<?php

namespace Tests\Feature\Modules;

use App\Models\Module;
use App\Support\ModuleDiscovery;
use App\Support\ModuleManager;
use App\Support\Modules\ModuleInstaller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\TestCase;
use ZipArchive;

/**
 * Coverage for App\Support\Modules\ModuleInstaller — the admin ZIP
 * install/export/uninstall flow that complements the drop-in git workflow.
 *
 * Isolation contract:
 *   - sqlite :memory: (forced by tests/bootstrap.php + phpunit.xml force="true").
 *     The dev MySQL instance is NEVER touched.
 *   - Filesystem side effects (modules/<testname>/, storage backups, tmp ZIPs)
 *     are created under throwaway names and torn down in tearDown().
 *
 * Fixture strategy: an already-installed module (related_products) is exported
 * to a real ZIP via exportToZip(), then repacked under a unique throwaway name
 * so install round-trips land in modules/<testname>/ and never clobber the
 * real module dir.
 *
 * Areas exercised:
 *   - previewFromZip()      — manifest + migrations (CREATE TABLE) + routes parse
 *   - compatibilityErrors() — engine / php / laravel constraint gate
 *   - exportToZip()→installFromZip() round-trip (incl. force reinstall)
 *   - uninstall()           — refuses to remove an enabled module
 */
class ModuleInstallerTest extends TestCase
{
    use RefreshDatabase;

    /** Throwaway module dirs created during a test, removed in tearDown. */
    private array $createdModuleDirs = [];

    /** Throwaway ZIP files created during a test, removed in tearDown. */
    private array $createdZips = [];

    /** Source module exported as the fixture. Must exist on disk + in config. */
    private const FIXTURE_SOURCE = 'related_products';

    protected function setUp(): void
    {
        parent::setUp();
        ModuleManager::clearCache();
        ModuleDiscovery::clearCache();
    }

    protected function tearDown(): void
    {
        foreach ($this->createdModuleDirs as $dir) {
            if (is_dir($dir)) {
                File::deleteDirectory($dir);
            }
        }
        foreach ($this->createdZips as $zip) {
            if (is_file($zip)) {
                @unlink($zip);
            }
        }
        // Drop any backup ZIPs the installer auto-created for our throwaway keys.
        $backupDir = storage_path('app/backups/modules');
        if (is_dir($backupDir)) {
            foreach (glob($backupDir.'/testmod_*') ?: [] as $f) {
                @unlink($f);
            }
        }

        ModuleManager::clearCache();
        ModuleDiscovery::clearCache();

        parent::tearDown();
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    /**
     * Wrap an on-disk file as an UploadedFile in test mode so isValid(),
     * getSize() and getRealPath() behave like a real upload would.
     */
    private function asUpload(string $path, ?string $clientName = null): UploadedFile
    {
        return new UploadedFile(
            $path,
            $clientName ?? basename($path),
            'application/zip',
            null,
            true // $test = true → skip is_uploaded_file() check
        );
    }

    /**
     * Export the fixture module and rewrite its module.json `name` to a
     * unique throwaway key so install lands in a fresh dir. Returns the
     * path to the new ZIP and the chosen module name.
     *
     * @return array{0:string,1:string}
     */
    private function makeFixtureZipAs(string $newName): array
    {
        $sourceZip = ModuleInstaller::exportToZip(self::FIXTURE_SOURCE);
        $this->createdZips[] = $sourceZip;

        $rewritten = $this->repackWithManifestName($sourceZip, $newName);
        $this->createdZips[] = $rewritten;
        $this->createdModuleDirs[] = base_path('modules/'.$newName);

        return [$rewritten, $newName];
    }

    /**
     * Copy a module ZIP into a new archive whose module.json carries a
     * different `name`. Used so round-trip installs do not clobber the
     * real fixture module on disk.
     */
    private function repackWithManifestName(string $sourceZip, string $newName, array $manifestOverrides = []): string
    {
        $src = new ZipArchive();
        $this->assertTrue($src->open($sourceZip) === true, 'Could not open source fixture ZIP.');

        $manifestRaw = $src->getFromName('module.json');
        $this->assertIsString($manifestRaw, 'Fixture ZIP missing root module.json.');
        $manifest = json_decode($manifestRaw, true);
        $manifest['name'] = $newName;
        foreach ($manifestOverrides as $k => $v) {
            $manifest[$k] = $v;
        }

        $outPath = storage_path('app/tmp/modules/'.$newName.'-repack-'.uniqid().'.zip');
        $dir = dirname($outPath);
        if (! is_dir($dir)) {
            File::makeDirectory($dir, 0755, true, true);
        }

        $out = new ZipArchive();
        $this->assertTrue(
            $out->open($outPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true,
            'Could not create repacked ZIP.'
        );

        for ($i = 0; $i < $src->numFiles; $i++) {
            $name = $src->getNameIndex($i);
            if ($name === false) {
                continue;
            }
            if ($name === 'module.json') {
                continue; // replaced below
            }
            if (str_ends_with($name, '/')) {
                $out->addEmptyDir(rtrim($name, '/'));
                continue;
            }
            $content = $src->getFromName($name);
            if (is_string($content)) {
                $out->addFromString($name, $content);
            }
        }
        $out->addFromString('module.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $out->close();
        $src->close();

        return $outPath;
    }

    // ---------------------------------------------------------------------
    // previewFromZip
    // ---------------------------------------------------------------------

    public function test_preview_parses_manifest_fields(): void
    {
        $zipPath = ModuleInstaller::exportToZip(self::FIXTURE_SOURCE);
        $this->createdZips[] = $zipPath;

        $preview = ModuleInstaller::previewFromZip($this->asUpload($zipPath));

        $this->assertSame(self::FIXTURE_SOURCE, $preview['module_name']);
        $this->assertSame('1.1.0', $preview['version']);
        $this->assertNotEmpty($preview['label']);
        $this->assertSame([], $preview['requires_modules']);
        $this->assertContains(
            'App\\Filament\\Resources\\FilterLandingResource',
            $preview['filament_resources']
        );
        $this->assertContains(
            'Modules\\RelatedProducts\\RelatedProductsServiceProvider',
            $preview['providers']
        );

        // raw manifest must round-trip through so compatibilityErrors() can read it.
        $this->assertIsArray($preview['raw']);
        $this->assertSame('>=2.0', $preview['raw']['engine']);
        $this->assertSame('>=8.2', $preview['raw']['php']);
    }

    public function test_preview_extracts_create_table_migrations(): void
    {
        $zipPath = ModuleInstaller::exportToZip(self::FIXTURE_SOURCE);
        $this->createdZips[] = $zipPath;

        $preview = ModuleInstaller::previewFromZip($this->asUpload($zipPath));

        // related_products ships migrations creating these tables.
        $this->assertContains('related_products', $preview['will_create_tables']);
        $this->assertContains('product_options', $preview['will_create_tables']);
        $this->assertContains('product_option_values', $preview['will_create_tables']);
        $this->assertContains('product_variants', $preview['will_create_tables']);
        $this->assertContains('filter_landings', $preview['will_create_tables']);

        // Each table parsed once — no dupes from the regex scan.
        $this->assertSame(
            array_values(array_unique($preview['will_create_tables'])),
            $preview['will_create_tables']
        );
    }

    public function test_preview_parses_routes_from_routes_file(): void
    {
        $zipPath = ModuleInstaller::exportToZip(self::FIXTURE_SOURCE);
        $this->createdZips[] = $zipPath;

        $preview = ModuleInstaller::previewFromZip($this->asUpload($zipPath));

        // routes/web.php declares storefront routes; assert the parser found
        // at least one VERB /uri entry in the expected shape.
        $this->assertNotEmpty($preview['routes'], 'Expected route declarations to be parsed.');
        foreach ($preview['routes'] as $route) {
            $this->assertMatchesRegularExpression(
                '/^(GET|POST|PUT|PATCH|DELETE|ANY) \S/',
                $route,
                "Route entry not in 'VERB /uri' shape: {$route}"
            );
        }
    }

    public function test_preview_rejects_zip_without_manifest(): void
    {
        $bogus = storage_path('app/tmp/modules/no-manifest-'.uniqid().'.zip');
        if (! is_dir(dirname($bogus))) {
            File::makeDirectory(dirname($bogus), 0755, true, true);
        }
        $this->createdZips[] = $bogus;

        $zip = new ZipArchive();
        $zip->open($bogus, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('readme.txt', 'not a module');
        $zip->close();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('module.json');
        ModuleInstaller::previewFromZip($this->asUpload($bogus));
    }

    // ---------------------------------------------------------------------
    // compatibilityErrors
    // ---------------------------------------------------------------------

    public function test_compatibility_passes_for_satisfied_constraints(): void
    {
        // Engine 2.0.0, Laravel 12.x, PHP 8.x — all satisfied here.
        $preview = ['raw' => [
            'engine' => '>=2.0',
            'php' => '>=8.2',
            'laravel' => '>=12.0',
            'requires_modules' => [],
        ]];

        $this->assertSame([], ModuleInstaller::compatibilityErrors($preview));
    }

    public function test_compatibility_flags_engine_too_new(): void
    {
        $preview = ['raw' => ['engine' => '>=99.0']];

        $errors = ModuleInstaller::compatibilityErrors($preview);

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('Engine', $errors[0]);
        $this->assertStringContainsString('99.0', $errors[0]);
    }

    public function test_compatibility_flags_unsatisfiable_php(): void
    {
        $preview = ['raw' => ['php' => '>=99.0']];

        $errors = ModuleInstaller::compatibilityErrors($preview);

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('PHP', $errors[0]);
    }

    public function test_compatibility_flags_unsatisfiable_laravel(): void
    {
        $preview = ['raw' => ['laravel' => '>=99.0']];

        $errors = ModuleInstaller::compatibilityErrors($preview);

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('Laravel', $errors[0]);
    }

    public function test_compatibility_flags_missing_required_module(): void
    {
        $preview = ['raw' => ['requires_modules' => ['definitely_not_installed_xyz']]];

        $errors = ModuleInstaller::compatibilityErrors($preview);

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('definitely_not_installed_xyz', $errors[0]);
    }

    public function test_compatibility_accepts_installed_required_module(): void
    {
        // related_products exists on disk → requires_modules dependency is met.
        $preview = ['raw' => ['requires_modules' => [self::FIXTURE_SOURCE]]];

        $this->assertSame([], ModuleInstaller::compatibilityErrors($preview));
    }

    public function test_compatibility_noop_without_raw_manifest(): void
    {
        // Backward-compat: a preview lacking 'raw' must not throw / errors.
        $this->assertSame([], ModuleInstaller::compatibilityErrors([]));
    }

    // ---------------------------------------------------------------------
    // exportToZip → installFromZip round-trip
    // ---------------------------------------------------------------------

    public function test_export_then_install_round_trip(): void
    {
        $name = 'testmod_roundtrip';
        [$zipPath] = $this->makeFixtureZipAs($name);

        $target = base_path('modules/'.$name);
        $this->assertDirectoryDoesNotExist($target, 'Precondition: throwaway module must not exist yet.');

        $result = ModuleInstaller::installFromZip($this->asUpload($zipPath));

        $this->assertSame($name, $result['key']);
        $this->assertSame('installed', $result['action']);
        $this->assertSame('1.1.0', $result['version']);

        // Files landed: manifest + a known migration must be present.
        $this->assertDirectoryExists($target);
        $this->assertFileExists($target.'/module.json');
        $this->assertFileExists(
            $target.'/database/migrations/2026_03_28_400001_create_related_products_table.php'
        );

        // Manifest name was correctly rewritten to the throwaway key.
        $installedManifest = json_decode(File::get($target.'/module.json'), true);
        $this->assertSame($name, $installedManifest['name']);

        // Discovery picks the freshly installed module up.
        ModuleDiscovery::clearCache();
        $this->assertArrayHasKey($name, ModuleDiscovery::manifests());
    }

    public function test_install_refuses_existing_without_force(): void
    {
        $name = 'testmod_noforce';
        [$zipPath] = $this->makeFixtureZipAs($name);

        ModuleInstaller::installFromZip($this->asUpload($zipPath));
        $this->assertDirectoryExists(base_path('modules/'.$name));

        // Second install without force must be refused.
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('вже встановлено');
        ModuleInstaller::installFromZip($this->asUpload($zipPath));
    }

    public function test_install_reinstalls_with_force(): void
    {
        $name = 'testmod_force';
        [$zipPath] = $this->makeFixtureZipAs($name);

        ModuleInstaller::installFromZip($this->asUpload($zipPath));

        $result = ModuleInstaller::installFromZip($this->asUpload($zipPath), force: true);

        $this->assertSame('reinstalled', $result['action']);
        $this->assertSame($name, $result['key']);
        // A backup of the previous version is taken on force reinstall.
        $this->assertNotNull($result['backup_path']);
        $this->assertFileExists($result['backup_path']);
    }

    public function test_install_blocked_by_incompatible_engine(): void
    {
        $name = 'testmod_incompat';
        $sourceZip = ModuleInstaller::exportToZip(self::FIXTURE_SOURCE);
        $this->createdZips[] = $sourceZip;

        // Repack demanding an impossible engine version.
        $zipPath = $this->repackWithManifestName($sourceZip, $name, ['engine' => '>=99.0']);
        $this->createdZips[] = $zipPath;
        $this->createdModuleDirs[] = base_path('modules/'.$name);

        try {
            ModuleInstaller::installFromZip($this->asUpload($zipPath));
            $this->fail('Expected install to be blocked by compatibility gate.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('не сумісний', $e->getMessage());
        }

        // Nothing was extracted — the gate runs before any filesystem writes.
        $this->assertDirectoryDoesNotExist(base_path('modules/'.$name));
    }

    // ---------------------------------------------------------------------
    // uninstall guard
    // ---------------------------------------------------------------------

    public function test_uninstall_refuses_enabled_module(): void
    {
        $name = 'testmod_enabled';
        [$zipPath] = $this->makeFixtureZipAs($name);
        ModuleInstaller::installFromZip($this->asUpload($zipPath));

        // Make ModuleManager treat the module as existing + enabled:
        //   - register a config entry so ->exists() is true
        //   - DB row enabled=true wins the DB→ENV→config waterfall
        config()->set('modules.'.$name, ['name' => $name, 'enabled' => false, 'requires' => []]);
        Module::create(['key' => $name, 'enabled' => true, 'enabled_at' => now()]);
        ModuleManager::clearCache();

        $this->assertTrue(ModuleManager::for($name)->enabled(), 'Precondition: module must read as enabled.');

        try {
            ModuleInstaller::uninstall($name);
            $this->fail('Expected uninstall of an enabled module to be refused.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('вимкніть', $e->getMessage());
        }

        // Guard fired before deletion — files are still on disk.
        $this->assertDirectoryExists(base_path('modules/'.$name));
    }

    public function test_uninstall_succeeds_when_disabled(): void
    {
        $name = 'testmod_disabled';
        [$zipPath] = $this->makeFixtureZipAs($name);
        ModuleInstaller::installFromZip($this->asUpload($zipPath));

        // Disabled via DB row → guard does not fire.
        config()->set('modules.'.$name, ['name' => $name, 'enabled' => false, 'requires' => []]);
        Module::create(['key' => $name, 'enabled' => false]);
        ModuleManager::clearCache();

        $this->assertFalse(ModuleManager::for($name)->enabled());

        $result = ModuleInstaller::uninstall($name);

        $this->assertSame('soft', $result['mode']);
        $this->assertGreaterThan(0, $result['files_removed']);
        $this->assertDirectoryDoesNotExist(base_path('modules/'.$name));
    }

    public function test_uninstall_rejects_missing_module(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('не знайдено');
        ModuleInstaller::uninstall('testmod_does_not_exist');
    }
}
