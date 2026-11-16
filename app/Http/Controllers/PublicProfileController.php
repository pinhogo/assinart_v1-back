<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FormatsPublicProfile;
use App\Models\Artist;
use App\Support\ReservedUsernames;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicProfileController extends Controller
{
    use FormatsPublicProfile;

    public function show(Request $request, string $username): JsonResponse
    {
        $cleanUsername = strtolower(trim($username));
        $cleanUsername = ltrim($cleanUsername, '@');

        if ($cleanUsername === '' || ReservedUsernames::contains($cleanUsername)) {
            return $this->notFoundResponse();
        }

        $artist = Artist::where('username', $cleanUsername)->with(['activeButtons'])->first();

        if (!$artist) {
            return $this->notFoundResponse();
        }

        if (!$artist->is_published) {
            $viewer = $request->user('sanctum');
            if (!$viewer || (int) $viewer->id !== (int) $artist->id) {
                return $this->draftResponse();
            }
        }

        $theme = $this->buildThemeArray($artist);
        $isPublished = (bool) $artist->is_published;
        $publicationStatus = $artist->publication_status;
        $artistSummary = array_merge([
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

    /**
     * Return a minimal 404 payload for unpublished profiles without exposing artist data.
     */
    private function draftResponse(): JsonResponse
    {
        return response()->json([
            'status' => 'draft',
            'message' => 'Perfil ainda não publicado.',
        ], 404);
    }
}
