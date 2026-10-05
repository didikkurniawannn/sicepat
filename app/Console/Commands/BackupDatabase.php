<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Backup database harian (plan.md NFR-09, RPO < 24 jam).
 * SQLite: salin file. pgsql: pg_dump bila tersedia.
 */
class BackupDatabase extends Command
{
    protected $signature = 'app:backup-db {--keep=7 : jumlah backup terbaru yang dipertahankan}';
    protected $description = 'Backup database ke storage/app/backups';

    public function handle(): int
    {
        $dir = storage_path('app/backups');
        @mkdir($dir, 0755, true);
        $stamp = now()->format('Ymd-His');
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $src = config('database.connections.sqlite.database');
            $dst = "{$dir}/backup-{$stamp}.sqlite";
            // Checkpoint WAL agar salinan konsisten
            try {
                DB::statement('PRAGMA wal_checkpoint(TRUNCATE)');
            } catch (\Throwable) {
            }
            copy($src, $dst);
            $this->info("Backup SQLite: {$dst}");
        } elseif ($driver === 'pgsql') {
            $c = config('database.connections.pgsql');
            $dst = "{$dir}/backup-{$stamp}.sql";
            $cmd = sprintf(
                'PGPASSWORD=%s pg_dump -h %s -p %s -U %s %s > %s 2>&1',
                escapeshellarg($c['password'] ?? ''), escapeshellarg($c['host']),
                escapeshellarg($c['port']), escapeshellarg($c['username']),
                escapeshellarg($c['database']), escapeshellarg($dst)
            );
            exec($cmd, $out, $code);
            if ($code !== 0) {
                $this->error('pg_dump gagal: '.implode("\n", $out));

                return self::FAILURE;
            }
            $this->info("Backup pgSQL: {$dst}");
        } else {
            $this->error("Driver {$driver} belum didukung.");

            return self::FAILURE;
        }

        // Rotasi: pertahankan N terbaru
        $files = glob("{$dir}/backup-*");
        rsort($files);
        foreach (array_slice($files, (int) $this->option('keep')) as $old) {
            @unlink($old);
        }

        return self::SUCCESS;
    }
}
