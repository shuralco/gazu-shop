<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Symfony\Component\Process\Process;

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
        $cfg = config('database.connections.'.config('database.default'));
        $host = $cfg['host'] ?? '127.0.0.1';
        $port = (string) ($cfg['port'] ?? '3306');
        $user = $cfg['username'] ?? 'root';
        $pass = (string) ($cfg['password'] ?? '');
        $db = $cfg['database'] ?? '';

        $file = static::dir().'/db-'.date('Ymd-His').'.sql.gz';

        // MYSQL_PWD у env — щоб пароль не світився у списку процесів.
        $cmd = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --single-transaction --quick --no-tablespaces --default-character-set=utf8mb4 %s | gzip > %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($user),
            escapeshellarg($db),
            escapeshellarg($file),
        );

        $process = Process::fromShellCommandline($cmd, null, ['MYSQL_PWD' => $pass], null, 600);

        try {
            $process->run();
            if (! $process->isSuccessful() || ! is_file($file) || filesize($file) < 100) {
                @unlink($file);
                throw new \RuntimeException($process->getErrorOutput() ?: 'mysqldump не створив файл');
            }
            Notification::make()
                ->title('Бекап створено')
                ->body(basename($file).' · '.$this->humanSize(filesize($file)))
                ->success()
                ->send();
        } catch (\Throwable $e) {
            report($e);
            Notification::make()
                ->title('Помилка бекапу')
                ->body(mb_substr($e->getMessage(), 0, 300))
                ->danger()
                ->send();
        }
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
