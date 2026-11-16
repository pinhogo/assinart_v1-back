<?php

namespace Tests\Feature;

use Tests\TestCase;

class AzureSimContainerConfigTest extends TestCase
{
    private function repoPath(string $relativePath): string
    {
        return dirname(__DIR__, 2) . '/' . ltrim($relativePath, '/');
    }

    public function test_azure_sim_container_files_exist_and_have_expected_directives(): void
    {
        $requiredFiles = [
            'docker/azure-sim/Dockerfile',
            'docker/azure-sim/entrypoint.sh',
            'docker/azure-sim/compose.yml',
            'scripts/load-test-azure-sim.sh',
            '.dockerignore',
        ];

        foreach ($requiredFiles as $relativePath) {
            $this->assertFileExists($this->repoPath($relativePath), "Arquivo ausente: {$relativePath}");
        }

        $dockerfile = file_get_contents($this->repoPath('docker/azure-sim/Dockerfile'));
        $this->assertStringContainsString('FROM php:8.3-fpm', $dockerfile);
        $this->assertStringContainsString('PHP_INI_SCAN_DIR=/usr/local/etc/php/conf.d', $dockerfile);
        $this->assertStringContainsString('nginx', $dockerfile);
        $this->assertStringContainsString('sqlite3', $dockerfile);
        $this->assertStringContainsString('libpq-dev', $dockerfile);
        foreach (['mbstring', 'fileinfo', 'pdo_sqlite', 'pdo_pgsql', 'gd', 'zip', 'opcache'] as $ext) {
            $this->assertStringContainsString($ext, $dockerfile);
        }
        $this->assertStringContainsString('cp azure/nginx.conf /etc/nginx/sites-available/default', $dockerfile);
        $this->assertStringContainsString('cp public/.user.ini /usr/local/etc/php/conf.d/', $dockerfile);
        $this->assertStringContainsString('listen = 127.0.0.1:9000', $dockerfile);
        $this->assertStringContainsString('EXPOSE 8080', $dockerfile);

        $entrypoint = file_get_contents($this->repoPath('docker/azure-sim/entrypoint.sh'));
        $this->assertStringContainsString('/home/data/storage/app/public/{avatars,covers,cards,wallpapers}', $entrypoint);
        $this->assertStringContainsString('/home/data/backups', $entrypoint);
        $this->assertStringContainsString('azure/startup.sh', $entrypoint);
        $this->assertStringContainsString('chown -R www-data:www-data', $entrypoint);
        $this->assertStringContainsString('php-fpm -D', $entrypoint);
        $this->assertStringContainsString('nginx -g "daemon off;"', $entrypoint);
    }

    public function test_azure_sim_compose_defines_b1_limits_env_and_postgres_profile(): void
    {
        $compose = file_get_contents($this->repoPath('docker/azure-sim/compose.yml'));

        $this->assertStringContainsString('cpus: 1.0', $compose);
        $this->assertStringContainsString('mem_limit: 1792m', $compose);
        $this->assertStringContainsString('memory: 1792M', $compose);
        $this->assertStringContainsString('restart: unless-stopped', $compose);
        $this->assertStringContainsString(':/home/data', $compose);
        $this->assertStringContainsString('APP_ENV: production', $compose);
        $this->assertStringContainsString('APP_DEBUG: "false"', $compose);
        $this->assertStringContainsString('DB_CONNECTION:', $compose);
        $this->assertStringContainsString('DB_DATABASE:', $compose);
        $this->assertStringContainsString('DB_SQLITE_JOURNAL_MODE:', $compose);
        $this->assertStringContainsString('DB_SQLITE_BUSY_TIMEOUT:', $compose);
        $this->assertStringContainsString('SESSION_DRIVER: file', $compose);
        $this->assertStringContainsString('CACHE_STORE: file', $compose);
        $this->assertStringContainsString('QUEUE_CONNECTION: sync', $compose);
        $this->assertStringContainsString('FILESYSTEM_DISK: public', $compose);

        $this->assertStringContainsString('postgres:16-alpine', $compose);
        $this->assertStringContainsString('- postgres', $compose);
        $this->assertStringContainsString('pg_isready', $compose);
    }

    public function test_azure_sim_scripts_are_executable_and_pass_bash_syntax_check(): void
    {
        $scripts = [
            $this->repoPath('docker/azure-sim/entrypoint.sh'),
            $this->repoPath('scripts/load-test-azure-sim.sh'),
        ];

        foreach ($scripts as $script) {
            $this->assertTrue(is_executable($script), "Script nao executavel: {$script}");
            $output = [];
            $exitCode = 1;
            exec('bash -n ' . escapeshellarg($script) . ' 2>&1', $output, $exitCode);
            $this->assertSame(0, $exitCode, "Falha bash -n em {$script}: " . implode("\n", $output));
        }

        $loadScript = file_get_contents($this->repoPath('scripts/load-test-azure-sim.sh'));
        $this->assertStringContainsString('--db', $loadScript);
        $this->assertStringContainsString('sqlite', $loadScript);
        $this->assertStringContainsString('pgsql', $loadScript);
        $this->assertStringContainsString('/api/v1/track', $loadScript);
        $this->assertStringContainsString('/r/', $loadScript);
        $this->assertStringContainsString('/up', $loadScript);
        $this->assertStringContainsString('grafana/k6', $loadScript);
        $this->assertStringContainsString('database is locked', $loadScript);
        $this->assertStringContainsString('P50_MS', $loadScript);
        $this->assertStringContainsString('P95_MS', $loadScript);
    }

    public function test_deploy_plan_documents_azure_sim_commands_and_cutover_criteria(): void
    {
        $deployPlan = file_get_contents($this->repoPath('context/DEPLOY_PLAN.md'));

        $this->assertStringContainsString('docker/azure-sim/compose.yml', $deployPlan);
        $this->assertStringContainsString('scripts/load-test-azure-sim.sh', $deployPlan);
        $this->assertStringContainsString('systemctl start docker', $deployPlan);
        $this->assertStringContainsString('--db sqlite', $deployPlan);
        $this->assertStringContainsString('--db pgsql', $deployPlan);
        $this->assertStringContainsString('API-12', $deployPlan);
    }
}
