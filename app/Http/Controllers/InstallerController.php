<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InstallerController extends Controller
{
    private function guard(Request $request): string
    {
        if (!filter_var(env('INSTALLER_ENABLED', false), FILTER_VALIDATE_BOOLEAN)) {
            abort(404);
        }
        $token = env('INSTALLER_TOKEN', '');
        $given = $request->query('token', $request->input('token', ''));
        if (!$token || !hash_equals($token, (string) $given)) {
            abort(403, 'Token instalasi salah.');
        }
        return $token;
    }

    private function checks(): array
    {
        $dbDriver = env('DB_CONNECTION', 'sqlite');
        $needPdo = $dbDriver === 'mysql' ? 'pdo_mysql' : 'pdo_sqlite';
        $ext = ['pdo', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json', 'bcmath', 'fileinfo', 'zip'];
        $checks = [];
        $checks[] = ['item' => 'PHP >= 8.2 (saat ini '.PHP_VERSION.')', 'ok' => version_compare(PHP_VERSION, '8.2.0', '>=')];
        foreach ($ext as $e) {
            $checks[] = ['item' => 'Ekstensi PHP: '.$e, 'ok' => extension_loaded($e)];
        }
        $checks[] = ['item' => 'Ekstensi PHP: gd (opsional, untuk export PDF)', 'ok' => extension_loaded('gd'), 'optional' => true];
        $checks[] = ['item' => 'Driver DB: '.$needPdo.' (DB_CONNECTION='.$dbDriver.')', 'ok' => extension_loaded($needPdo)];
        try {
            DB::connection()->getPdo();
            $checks[] = ['item' => 'Koneksi database ('.$dbDriver.')', 'ok' => true];
        } catch (\Throwable $e) {
            $checks[] = ['item' => 'Koneksi database ('.$dbDriver.'): '.$e->getMessage(), 'ok' => false];
        }
        $checks[] = ['item' => 'File .env ada & dapat ditulis', 'ok' => file_exists(base_path('.env')) && is_writable(base_path('.env'))];
        $checks[] = ['item' => 'Folder storage/ dapat ditulis', 'ok' => is_writable(storage_path())];
        $checks[] = ['item' => 'Folder bootstrap/cache dapat ditulis', 'ok' => is_writable(base_path('bootstrap/cache'))];
        $checks[] = ['item' => 'File seed Data Kegiatan (58 baris)', 'ok' => file_exists(database_path('seeders/activities_seed.json'))];
        return $checks;
    }

    private function serverPaths(): array
    {
        return [
            'base' => base_path(),
            'vendor' => base_path('vendor/autoload.php'),
            'bootstrap' => base_path('bootstrap/app.php'),
        ];
    }

    private function installed(): array
    {
        try {
            if (!Schema::hasTable('activities')) return ['done' => false];
            return [
                'done' => true,
                'sections' => \App\Models\Section::count(),
                'activities' => \App\Models\Activity::count(),
                'users' => \App\Models\User::count(),
            ];
        } catch (\Throwable $e) {
            return ['done' => false];
        }
    }

    public function index(Request $request)
    {
        $token = $this->guard($request);
        $checks = $this->checks();
        $allOk = collect($checks)->where('optional', '!=', true)->every(fn($c) => $c['ok']);
        $installed = $this->installed();
        $paths = $this->serverPaths();
        return view('install.index', compact('checks', 'allOk', 'installed', 'token', 'paths'));
    }

    public function run(Request $request)
    {
        $token = $this->guard($request);
        $request->validate(['confirm' => 'accepted']);
        $paths = $this->serverPaths();

        $log = [];
        try {
            Artisan::call('migrate:fresh', ['--force' => true]);
            $log[] = Artisan::output();
            Artisan::call('db:seed', ['--force' => true]);
            $log[] = Artisan::output();
            try {
                Artisan::call('storage:link');
                $log[] = Artisan::output();
            } catch (\Throwable $e) {
                $log[] = 'storage:link dilewati: '.$e->getMessage();
            }
            try {
                Artisan::call('optimize:clear');
                $log[] = 'Cache dibersihkan.';
            } catch (\Throwable $e) {
                $log[] = 'optimize:clear dilewati: '.$e->getMessage();
            }
            $installed = $this->installed();
            $success = $installed['done'] && $installed['activities'] > 0;
            if ($success) {
                $this->setEnv('INSTALLER_ENABLED', 'false');
                $log[] = 'Installer otomatis dinonaktifkan (INSTALLER_ENABLED=false).';
            }
        } catch (\Throwable $e) {
            return view('install.index', [
                'checks' => $this->checks(), 'allOk' => true,
                'installed' => $this->installed(), 'token' => $token, 'paths' => $paths,
                'runError' => 'Instalasi gagal: '.$e->getMessage(), 'runLog' => implode("\n", $log),
            ]);
        }

        return view('install.index', [
            'checks' => $this->checks(), 'allOk' => true,
            'installed' => $installed, 'token' => $token, 'paths' => $paths,
            'runSuccess' => $success, 'runLog' => implode("\n", $log),
        ]);
    }

    private function setEnv(string $key, string $value): void
    {
        $path = base_path('.env');
        $content = file_get_contents($path);
        if (preg_match('/^'.$key.'=.*/m', $content)) {
            $content = preg_replace('/^'.$key.'=.*/m', $key.'='.$value, $content);
        } else {
            $content .= "\n".$key.'='.$value."\n";
        }
        file_put_contents($path, $content);
    }
}
