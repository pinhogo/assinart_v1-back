<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Rules\ArtistUsername;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class DatabaseProductionConfigTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Recarrega config/database.php com variaveis de ambiente temporarias.
     *
     * @param array<string, string|null> $env
     * @return array<string, mixed>
     */
    private function loadDatabaseConfig(array $env): array
    {
        $previous = [];
        foreach ($env as $key => $value) {
            $previous[$key] = [
                'getenv' => getenv($key),
                'env_exists' => array_key_exists($key, $_ENV),
                'env' => $_ENV[$key] ?? null,
                'server_exists' => array_key_exists($key, $_SERVER),
                'server' => $_SERVER[$key] ?? null,
            ];

            if ($value === null) {
                putenv($key);
                unset($_ENV[$key], $_SERVER[$key]);
            } else {
                putenv("{$key}={$value}");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }

        try {
            return require config_path('database.php');
        } finally {
            foreach ($previous as $key => $orig) {
                $orig['getenv'] === false ? putenv($key) : putenv("{$key}={$orig['getenv']}");
                if ($orig['env_exists']) {
                    $_ENV[$key] = $orig['env'];
                } else {
                    unset($_ENV[$key]);
                }
                if ($orig['server_exists']) {
                    $_SERVER[$key] = $orig['server'];
                } else {
                    unset($_SERVER[$key]);
                }
            }
        }
    }

    public function test_default_connection_is_sqlite_for_development_and_tests(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
    }

    public function test_pgsql_ssl_options_follow_environment(): void
    {
        $config = $this->loadDatabaseConfig([
            'DB_SSLMODE' => 'require',
            'DB_SSLROOTCERT' => '/certs/root.crt',
        ]);

        $this->assertSame('require', $config['connections']['pgsql']['sslmode']);
        $this->assertSame('/certs/root.crt', $config['connections']['pgsql']['sslrootcert']);
    }

    public function test_pgsql_sslmode_defaults_to_prefer(): void
    {
        $config = $this->loadDatabaseConfig(['DB_SSLMODE' => null]);

        $this->assertSame('prefer', $config['connections']['pgsql']['sslmode']);
    }

    public function test_mysql_ssl_options_preserve_explicit_false(): void
    {
        if (!extension_loaded('pdo_mysql')) {
            $this->markTestSkipped('Extensao pdo_mysql nao carregada.');
        }

        $config = $this->loadDatabaseConfig([
            'MYSQL_ATTR_SSL_CA' => '/certs/ca.pem',
            'MYSQL_ATTR_SSL_VERIFY_SERVER_CERT' => 'false',
        ]);

        foreach (['mysql', 'mariadb'] as $name) {
            $options = $config['connections'][$name]['options'];
            $this->assertSame('/certs/ca.pem', $options[\PDO::MYSQL_ATTR_SSL_CA]);
            $this->assertArrayHasKey(\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT, $options);
            $this->assertFalse($options[\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT]);
        }
    }

    public function test_mysql_ssl_options_drop_empty_ca_and_verify_by_default(): void
    {
        if (!extension_loaded('pdo_mysql')) {
            $this->markTestSkipped('Extensao pdo_mysql nao carregada.');
        }

        $config = $this->loadDatabaseConfig([
            'MYSQL_ATTR_SSL_CA' => '',
            'DB_SSL_CA' => null,
            'MYSQL_ATTR_SSL_VERIFY_SERVER_CERT' => null,
            'DB_SSL_VERIFY' => null,
        ]);

        $options = $config['connections']['mysql']['options'];
        $this->assertArrayNotHasKey(\PDO::MYSQL_ATTR_SSL_CA, $options);
        $this->assertTrue($options[\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT]);
    }

    public function test_queue_infrastructure_tables_exist(): void
    {
        $this->assertTrue(Schema::hasTable('jobs'));
        $this->assertTrue(Schema::hasTable('job_batches'));
        $this->assertTrue(Schema::hasTable('failed_jobs'));
    }

    public function test_health_endpoint_returns_ok_with_active_database(): void
    {
        $this->get('/up')->assertStatus(200);
    }

    public function test_username_check_is_case_insensitive_against_stored_username(): void
    {
        Artist::factory()->create(['username' => 'MixedCase']);

        $this->getJson('/api/v1/usernames/check?username=MIXEDCASE')
            ->assertStatus(200)
            ->assertJson(['available' => false, 'reason' => 'taken']);
    }

    public function test_artist_username_rule_is_case_insensitive(): void
    {
        Artist::factory()->create(['username' => 'MixedCase']);

        $validator = Validator::make(
            ['username' => 'mixedcase'],
            ['username' => [new ArtistUsername()]]
        );

        $this->assertTrue($validator->fails());
    }
}
