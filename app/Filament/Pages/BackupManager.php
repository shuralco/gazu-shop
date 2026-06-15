<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Резервні копії БД — для технічного адміна. Повний дамп через mysqldump
 * (mysql-client є в образі) → gzip у storage/app/backups. Створення / список /
 * завантаження / видалення.
 *
 * Доступ: супер-адмін АБО пресет admin_full («Технічний адміністратор»).
 */
class BackupManager extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';

    protected static ?string $navigationGroup = 'Обслуговування';

    protected static ?string $navigationLabel = 'Резервні копії';

    protected static ?string $title = 'Резервні копії бази даних';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.backup-manager';

    /** Лише техадмін: супер-адмін або пресет admin_full. */
    public static function canAccess(): bool
    {
        $u = auth()->user();

        return $u && ($u->is_admin === true
            || optional($u->accessPreset)->key === 'admin_full');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    protected static function dir(): string
    {
        $d = storage_path('app/backups');
        if (! is_dir($d)) {
            @mkdir($d, 0775, true);
        }

        return $d;
    }

    /** Список бекапів для вью: name, size, date (новіші перші). */
    public function getBackups(): array
    {
        $files = glob(static::dir().'/*.sql.gz') ?: [];
        rsort($files);

        return array_map(fn ($f) => [
            'name' => basename($f),
            'size' => $this->humanSize(filesize($f) ?: 0),
            'date' => date('d.m.Y H:i', filemtime($f) ?: time()),
        ], $files);
    }

    protected function humanSize(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return round($bytes / 1073741824, 2).' ГБ';
        }
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1).' МБ';
        }

        return round($bytes / 1024).' КБ';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label('Створити бекап')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Створити повний бекап БД?')
                ->modalDescription('Повний дамп бази (mysqldump) у storage/app/backups. Може зайняти кілька секунд.')
                ->action(fn () => $this->createBackup()),
        ];
    }

    public function createBackup(): void
    {
        $file = static::dir().'/db-'.date('Ymd-His').'.sql.gz';

        try {
            $this->dumpDatabase($file);
            if (! is_file($file) || filesize($file) < 100) {
                @unlink($file);
                throw new \RuntimeException('Дамп порожній');
            }
            Notification::make()
                ->title('Бекап створено')
                ->body(basename($file).' · '.$this->humanSize(filesize($file)))
                ->success()
                ->send();
        } catch (\Throwable $e) {
            @unlink($file);
            report($e);
            Notification::make()
                ->title('Помилка бекапу')
                ->body(mb_substr($e->getMessage(), 0, 300))
                ->danger()
                ->send();
        }
    }

    /**
     * Потоковий дамп БД через PDO застосунку (mysqldump-бінарник на Alpine =
     * MariaDB-клієнт, який не авторизується до MySQL 8 з caching_sha2_password).
     * Пише структуру + дані всіх таблиць у gzip, рядок за рядком (без OOM).
     */
    protected function dumpDatabase(string $file): void
    {
        $conn = \Illuminate\Support\Facades\DB::connection();
        $pdo = $conn->getPdo();
        $dbName = $conn->getDatabaseName();

        $gz = gzopen($file, 'w6');
        if (! $gz) {
            throw new \RuntimeException('Не вдалось відкрити файл для запису');
        }

        gzwrite($gz, "-- GAZU DB dump {$dbName} ".date('Y-m-d H:i:s')."\n");
        gzwrite($gz, "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n");

        $tables = array_map(
            fn ($r) => array_values((array) $r)[0],
            $conn->select('SHOW TABLES')
        );

        foreach ($tables as $table) {
            $createRow = (array) $conn->select('SHOW CREATE TABLE `'.$table.'`')[0];
            $create = $createRow['Create Table'] ?? $createRow['Create View'] ?? null;
            if (! $create) {
                continue;
            }
            gzwrite($gz, "\n-- Table: {$table}\nDROP TABLE IF EXISTS `{$table}`;\n{$create};\n\n");

            // Дані — курсором, рядок за рядком.
            foreach ($conn->cursor('SELECT * FROM `'.$table.'`') as $row) {
                $row = (array) $row;
                $cols = '`'.implode('`,`', array_keys($row)).'`';
                $vals = implode(',', array_map(function ($v) use ($pdo) {
                    if ($v === null) {
                        return 'NULL';
                    }
                    if (is_int($v) || is_float($v)) {
                        return (string) $v;
                    }

                    return $pdo->quote((string) $v);
                }, array_values($row)));
                gzwrite($gz, "INSERT INTO `{$table}` ({$cols}) VALUES ({$vals});\n");
            }
        }

        gzwrite($gz, "\nSET FOREIGN_KEY_CHECKS=1;\n");
        gzclose($gz);
    }

    public function download(string $name)
    {
        $path = static::dir().'/'.basename($name);
        abort_unless(is_file($path), 404);

        return response()->download($path);
    }

    public function deleteBackup(string $name): void
    {
        $path = static::dir().'/'.basename($name);
        if (is_file($path)) {
            @unlink($path);
            Notification::make()->title('Бекап видалено')->success()->send();
        }
    }
}
