<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('artists', function (Blueprint $table) {
            if (!Schema::hasColumn('artists', 'button_shadow_color')) {
                $table->string('button_shadow_color', 30)->nullable()->default('#000000')->after('button_shadow');
            }
            if (!Schema::hasColumn('artists', 'circular_shadow_enabled')) {
                $table->boolean('circular_shadow_enabled')->default(true)->after('button_shadow_color');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('artists', function (Blueprint $table) {
            if (Schema::hasColumn('artists', 'circular_shadow_enabled')) {
                $table->dropColumn('circular_shadow_enabled');
            }
            if (Schema::hasColumn('artists', 'button_shadow_color')) {
                $table->dropColumn('button_shadow_color');
            }
        });
    }
};
