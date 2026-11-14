<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FormatsPublicProfile;
use App\Models\Artist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicProfileController extends Controller
{
    use FormatsPublicProfile;

    protected const RESERVED_ROUTES = [
        'api', 'storage', 'r', 'admin', 'login', 'register', 'dashboard', 'editor', 'up',
    ];

    public function show(Request $request, string $username): JsonResponse
    {
        $cleanUsername = strtolower(trim($username));
        $cleanUsername = ltrim($cleanUsername, '@');

        if ($cleanUsername === '' || in_array($cleanUsername, self::RESERVED_ROUTES, true)) {
            return $this->notFoundResponse();
        }

        $artist = Artist::where('username', $cleanUsername)->with(['activeButtons'])->first();

        if (!$artist) {
            return $this->notFoundResponse();
        }

        if (!$artist->is_published) {
            $viewer = $request->user('sanctum');
            if (!$viewer || (int) $viewer->id !== (int) $artist->id) {
                return $this->notFoundResponse();
            }
        }

        $theme = $this->buildThemeArray($artist);
        $isPublished = (bool) $artist->is_published;
        $publicationStatus = $artist->publication_status;
        $artistSummary = array_merge([
            'id' => (int) $artist->id,
            'name' => $artist->name,
            'username' => $artist->username,
            'bio' => $artist->bio,
            'avatar_url' => $artist->avatar_url,
            'cover_url' => $artist->cover_url,
            'is_published' => $isPublished,
            'publication_status' => $publicationStatus,
        ], $theme);

        $allButtons = $artist->activeButtons;
        $hasButtons = !$allButtons->isEmpty();
        $circularLinks = $hasButtons ? $this->formatCircularLinks($allButtons, $artist) : [];
        $buttonLinks = $hasButtons ? $this->formatButtonLinks($allButtons, $artist) : [];
        $allFormattedButtons = $hasButtons ? array_merge($circularLinks, $buttonLinks) : [];

        return response()->json([
            'status' => $hasButtons ? 'found' : 'no_links',
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
                'is_published' => $isPublished,
                'publication_status' => $publicationStatus,
                'theme' => $theme,
                'buttons' => $allFormattedButtons,
                'circular_links' => $circularLinks,
                'button_links' => $buttonLinks,
            ],
        ], 200);
    }

    private function notFoundResponse(): JsonResponse
    {
        return response()->json([
            'status' => 'not_found',
            'message' => 'Artista não encontrado.',
        ], 404);
    }
}
