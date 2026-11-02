<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FormatsPublicProfile;
use App\Models\Artist;
use Illuminate\Http\JsonResponse;

class PublicProfileController extends Controller
{
    use FormatsPublicProfile;

    protected const RESERVED_ROUTES = [
        'api', 'storage', 'r', 'admin', 'login', 'register', 'dashboard', 'editor', 'up',
    ];

    public function show(string $username): JsonResponse
    {
        $cleanUsername = strtolower(trim($username));
        $cleanUsername = ltrim($cleanUsername, '@');

        if ($cleanUsername === '' || in_array($cleanUsername, self::RESERVED_ROUTES, true)) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'Artista não encontrado.',
            ], 404);
        }

        $artist = Artist::where('username', $cleanUsername)->with(['activeButtons'])->first();

        if (!$artist) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'Artista não encontrado.',
            ], 404);
        }

        $theme = $this->buildThemeArray($artist);
        $artistSummary = array_merge([
            'id' => (int) $artist->id,
            'name' => $artist->name,
            'username' => $artist->username,
            'bio' => $artist->bio,
            'avatar_url' => $artist->avatar_url,
            'cover_url' => $artist->cover_url,
        ], $theme);

        $allButtons = $artist->activeButtons;

        if ($allButtons->isEmpty()) {
            return response()->json([
                'status' => 'no_links',
                'artist' => $artistSummary,
                'circular_links' => [],
                'button_links' => [],
                'data' => [
                    'id' => (int) $artist->id,
                    'name' => $artist->name,
                    'username' => $artist->username,
                    'bio' => $artist->bio,
                    'avatar_url' => $artist->avatar_url,
                    'cover_url' => $artist->cover_url,
                    'theme' => $theme,
                    'buttons' => [],
                    'circular_links' => [],
                    'button_links' => [],
                ],
            ], 200);
        }

        $circularLinks = $this->formatCircularLinks($allButtons, $artist);
        $buttonLinks = $this->formatButtonLinks($allButtons, $artist);
        $allFormattedButtons = array_merge($circularLinks, $buttonLinks);

        return response()->json([
            'status' => 'found',
            'artist' => $artistSummary,
            'circular_links' => $circularLinks,
            'button_links' => $buttonLinks,
            'data' => [
                'id' => (int) $artist->id,
                'name' => $artist->name,
                'username' => $artist->username,
                'bio' => $artist->bio,
                'avatar_url' => $artist->avatar_url,
                'cover_url' => $artist->cover_url,
                'theme' => $theme,
                'buttons' => $allFormattedButtons,
                'circular_links' => $circularLinks,
                'button_links' => $buttonLinks,
            ],
        ], 200);
    }
}
