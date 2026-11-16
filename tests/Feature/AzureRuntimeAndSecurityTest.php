<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\Button;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AzureRuntimeAndSecurityTest extends TestCase
{
    public function test_azure_runtime_and_ci_artifacts_exist_with_expected_directives(): void
    {
        $requiredFiles = [
            'azure/nginx.conf',
            'azure/startup.sh',
            'azure/provision.sh',
            'azure/backup.sh',
            'azure/appsettings.example.env',
            'public/.user.ini',
            '.gitattributes',
            '.github/workflows/deploy-backend.yml',
        ];

        foreach ($requiredFiles as $relativePath) {
            $this->assertFileExists(base_path($relativePath), "Arquivo ausente: {$relativePath}");
        }

        $nginx = file_get_contents(base_path('azure/nginx.conf'));
        $this->assertStringContainsString('root /home/site/wwwroot/public;', $nginx);
        $this->assertStringContainsString('client_max_body_size 16M;', $nginx);
        $this->assertStringContainsString('try_files $uri $uri/ /index.php?$query_string;', $nginx);
        $this->assertStringContainsString('location ~ /\.(?!well-known).*', $nginx);
        $this->assertStringContainsString('location ~* ^/storage/.*\.php$', $nginx);

        $startup = file_get_contents(base_path('azure/startup.sh'));
        $this->assertStringContainsString('/home/data/storage/app/public', $startup);
        $this->assertStringContainsString('avatars,covers,cards,wallpapers', $startup);
        $this->assertStringContainsString('php artisan migrate --force', $startup);
        $this->assertStringContainsString('nginx -s reload', $startup);

        $provision = file_get_contents(base_path('azure/provision.sh'));
        $this->assertStringContainsString('az account show', $provision);
        $this->assertStringContainsString('brazilsouth', $provision);
        $this->assertStringContainsString('eastus2', $provision);
        $this->assertStringContainsString('--sku "${SKU}"', $provision);
        $this->assertStringContainsString('--is-linux', $provision);
        $this->assertStringContainsString('PHP:8.3', $provision);
        $this->assertStringContainsString('--https-only true', $provision);
        $this->assertStringContainsString('--always-on true', $provision);
        $this->assertStringContainsString('--min-tls-version 1.2', $provision);
        $this->assertStringContainsString('--ftps-state Disabled', $provision);
        $this->assertStringContainsString('WEBSITES_CONTAINER_START_TIME_LIMIT="230"', $provision);
        $this->assertStringContainsString('az consumption budget create', $provision);

        $backup = file_get_contents(base_path('azure/backup.sh'));
        $this->assertStringContainsString('sqlite3', $backup);
        $this->assertStringContainsString('.backup', $backup);
        $this->assertStringContainsString('Restauração', $backup);

        $appSettings = file_get_contents(base_path('azure/appsettings.example.env'));
        $this->assertStringContainsString('APP_ENV=production', $appSettings);
        $this->assertStringContainsString('APP_DEBUG=false', $appSettings);
        $this->assertStringContainsString('FILESYSTEM_DISK=public', $appSettings);
        $this->assertStringContainsString('MEDIA_DISK=public', $appSettings);
        $this->assertStringContainsString('SESSION_DRIVER=file', $appSettings);
        $this->assertStringContainsString('CACHE_STORE=file', $appSettings);
        $this->assertStringContainsString('QUEUE_CONNECTION=sync', $appSettings);
        $this->assertStringContainsString('LOG_CHANNEL=stderr', $appSettings);
        $this->assertStringContainsString('CORS_ALLOWED_ORIGINS=https://assin.art,https://www.assin.art', $appSettings);
        $this->assertStringContainsString('WEBSITES_CONTAINER_START_TIME_LIMIT=230', $appSettings);
        $this->assertStringNotContainsString('SANCTUM_STATEFUL_DOMAINS', $appSettings);

        $userIni = file_get_contents(base_path('public/.user.ini'));
        $this->assertStringContainsString('upload_max_filesize=12M', $userIni);
        $this->assertStringContainsString('post_max_size=16M', $userIni);
        $this->assertStringContainsString('memory_limit=256M', $userIni);
        $this->assertStringContainsString('max_execution_time=60', $userIni);

        $gitattributes = file_get_contents(base_path('.gitattributes'));
        foreach (['context/', 'docs/', 'documentacao/', 'tests/', '*.md', 'run.sh', 'composer.phar', '.phpunit*', '.github/'] as $entry) {
            $this->assertStringContainsString("{$entry} export-ignore", $gitattributes);
        }

        $workflow = file_get_contents(base_path('.github/workflows/deploy-backend.yml'));
        $this->assertStringContainsString("PHP_VERSION: '8.3'", $workflow);
        $this->assertStringContainsString('shivammathur/setup-php@v2', $workflow);
        $this->assertStringContainsString('fileinfo, mbstring', $workflow);
        $this->assertStringContainsString('composer install --no-dev', $workflow);
        $this->assertStringContainsString('git archive', $workflow);
        $this->assertStringContainsString('actions/upload-artifact@v4', $workflow);
        $this->assertStringContainsString('azure/webapps-deploy@v3', $workflow);
    }

    public function test_azure_shell_scripts_pass_bash_syntax_check(): void
    {
        $scripts = glob(base_path('azure/*.sh'));
        $this->assertNotEmpty($scripts);

        foreach ($scripts as $script) {
            $output = [];
            $exitCode = 1;
            exec('bash -n ' . escapeshellarg($script) . ' 2>&1', $output, $exitCode);
            $this->assertSame(0, $exitCode, "Falha de sintaxe bash em {$script}: " . implode("\n", $output));
        }
    }

    public function test_database_sqlite_config_composer_and_env_example_hardening(): void
    {
        $this->assertSame('WAL', config('database.connections.sqlite.journal_mode'));
        $this->assertSame(5000, config('database.connections.sqlite.busy_timeout'));

        $composer = json_decode(file_get_contents(base_path('composer.json')), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('*', $composer['require']['ext-fileinfo'] ?? null);
        $this->assertSame('*', $composer['require']['ext-mbstring'] ?? null);

        $envExample = file_get_contents(base_path('.env.example'));
        $this->assertMatchesRegularExpression('/^APP_ENV=production$/m', $envExample);
        $this->assertMatchesRegularExpression('/^APP_DEBUG=false$/m', $envExample);
        $this->assertMatchesRegularExpression('/^SESSION_DRIVER=file$/m', $envExample);
        $this->assertMatchesRegularExpression('/^CACHE_STORE=file$/m', $envExample);
        $this->assertMatchesRegularExpression('/^QUEUE_CONNECTION=sync$/m', $envExample);
        $this->assertMatchesRegularExpression('/^CORS_ALLOWED_ORIGINS=https:\/\/assin\.art,https:\/\/www\.assin\.art$/m', $envExample);
    }

    public function test_sqlite_wal_load_on_track_and_redirect_does_not_hit_database_is_locked(): void
    {
        $tempDb = sys_get_temp_dir() . '/assinart_wal_load_' . uniqid('', true) . '.sqlite';
        touch($tempDb);

        $originalDatabase = config('database.connections.sqlite.database');
        config(['database.connections.sqlite.database' => $tempDb]);
        DB::purge('sqlite');

        try {
            Artisan::call('migrate', ['--force' => true]);

            $journalMode = DB::connection('sqlite')->selectOne('PRAGMA journal_mode')->journal_mode ?? null;
            $busyTimeout = DB::connection('sqlite')->selectOne('PRAGMA busy_timeout')->timeout ?? null;

            $this->assertSame('wal', strtolower((string) $journalMode));
            $this->assertSame(5000, (int) $busyTimeout);

            $artist = Artist::factory()->create([
                'username' => 'wal_load_artist',
                'is_published' => true,
            ]);

            $button = Button::factory()->create([
                'artist_id' => $artist->id,
                'url' => 'https://assin.art',
                'is_active' => true,
                'clicks_count' => 0,
            ]);

            for ($i = 0; $i < 25; $i++) {
                $trackResponse = $this->postJson('/api/v1/track', [
                    'type' => 'pageview',
                    'username' => 'wal_load_artist',
                    'visitor_id' => 'load-visitor-' . $i,
                ]);
                $trackResponse->assertStatus(204);

                $redirectResponse = $this->get('/r/' . $button->id);
                $redirectResponse->assertStatus(302);
            }

            $button->refresh();
            $this->assertSame(25, $button->clicks_count);
            $this->assertSame(25, DB::table('page_views')->where('artist_id', $artist->id)->count());
        } finally {
            DB::disconnect('sqlite');
            config(['database.connections.sqlite.database' => $originalDatabase]);
            DB::purge('sqlite');

            @unlink($tempDb);
            @unlink($tempDb . '-wal');
            @unlink($tempDb . '-shm');
        }
    }
}
