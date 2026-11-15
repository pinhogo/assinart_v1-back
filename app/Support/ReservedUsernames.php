<?php

namespace App\Support;

/**
 * Single source of reserved @username slugs.
 *
 * Used by the availability check, registration, profile update, the public
 * profile controller, and the public route constraint in routes/web_public.php.
 */
final class ReservedUsernames
{
    /**
     * Lowercase slugs that no artist can use as @username.
     * Covers API prefixes, SPA routes, system pages, and static root files.
     *
     * @var list<string>
     */
    public const SLUGS = [
        'admin',
        'ajuda',
        'analytics',
        'api',
        'app',
        'assets',
        'assin.art',
        'assinart',
        'auth',
        'cadastro',
        'configuracoes',
        'confirmacao-email',
        'contato',
        'dashboard',
        'editor',
        'email',
        'entrar',
        'esqueci-senha',
        'favicon.ico',
        'health',
        'home',
        'login',
        'logout',
        'metrics',
        'null',
        'oauth',
        'perfil',
        'playground',
        'privacidade',
        'public',
        'r',
        'register',
        'robots.txt',
        'root',
        'sac',
        'settings',
        'signin',
        'signup',
        'sitemap.xml',
        'sobre',
        'static',
        'status',
        'storage',
        'suporte',
        'termos',
        'undefined',
        'up',
    ];

    /**
     * Return true when the value matches a reserved slug, ignoring case.
     */
    public static function contains(string $value): bool
    {
        return in_array(strtolower($value), self::SLUGS, true);
    }

    /**
     * Return a route regex that rejects reserved slugs as a whole path segment.
     *
     * The result is a negative lookahead followed by the allowed username characters,
     * so it can be passed directly to Route::where().
     */
    public static function routePattern(): string
    {
        $alternatives = implode('|', array_map(
            static fn (string $slug): string => preg_quote($slug, '/'),
            self::SLUGS,
        ));

        return '(?!(?:' . $alternatives . ')$)[@a-zA-Z0-9_.-]+';
    }
}
