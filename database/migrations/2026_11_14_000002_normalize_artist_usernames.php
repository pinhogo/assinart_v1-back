<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Normalize existing artist usernames and log collisions without overwriting.
     */
    public function up(): void
    {
        $rows = DB::table('artists')->select(['id', 'username'])->orderBy('id')->get();
        $taken = [];

        foreach ($rows as $row) {
            $taken[$row->username] = (int) $row->id;
        }

        foreach ($rows as $row) {
            $original = (string) $row->username;
            $normalized = ltrim(strtolower(trim($original)), '@');

            if ($normalized === $original) {
                continue;
            }

            if ($normalized === '') {
                Log::warning('Artist username normalization skipped: empty target.', [
                    'artist_id' => (int) $row->id,
                    'username' => $original,
                ]);
                continue;
            }

            $existingOwnerId = $taken[$normalized] ?? null;
            if ($existingOwnerId !== null && $existingOwnerId !== (int) $row->id) {
                Log::warning('Artist username normalization skipped due to collision.', [
                    'artist_id' => (int) $row->id,
                    'username' => $original,
                    'target_username' => $normalized,
                    'conflicting_artist_id' => $existingOwnerId,
                ]);
                continue;
            }

            try {
                DB::table('artists')
                    ->where('id', $row->id)
                    ->update(['username' => $normalized]);
                unset($taken[$original]);
                $taken[$normalized] = (int) $row->id;
            } catch (\Throwable $exception) {
                Log::warning('Artist username normalization failed due to database constraint.', [
                    'artist_id' => (int) $row->id,
                    'username' => $original,
                    'target_username' => $normalized,
                    'error' => $exception->getMessage(),
                ]);
            }
        }
    }

    /**
     * Normalization cannot be losslessly reversed.
     */
    public function down(): void
    {
    }
};
