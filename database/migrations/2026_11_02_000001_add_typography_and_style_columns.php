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
            $table->string('cover_color', 20)->nullable()->after('cover_url');
            $table->string('wallpaper_url', 2048)->nullable()->after('background_id');
            $table->boolean('wallpaper_repeat')->default(false)->after('wallpaper_url');
            $table->string('wallpaper_preset_id', 50)->nullable()->after('wallpaper_repeat');

            // Tipografia do Perfil
            $table->string('profile_font_family', 50)->default('Space Grotesk')->after('font_color');
            $table->string('profile_font_size', 10)->default('md')->after('profile_font_family');
            $table->string('profile_text_color', 20)->default('#000000')->after('profile_font_size');
            $table->string('profile_font_weight', 10)->default('normal')->after('profile_text_color');
            $table->string('profile_font_style', 10)->default('normal')->after('profile_font_weight');
            $table->string('profile_text_border', 10)->default('none')->after('profile_font_style');

            // Tipografia Global dos Botões
            $table->string('button_font_family', 50)->nullable()->after('profile_text_border');
            $table->string('button_font_size', 10)->default('md')->after('button_font_family');
            $table->string('button_font_weight', 10)->default('normal')->after('button_text_color');
            $table->string('button_font_style', 10)->default('normal')->after('button_font_weight');
            $table->string('button_text_border', 10)->default('none')->after('button_font_style');
        });

        Schema::table('buttons', function (Blueprint $table) {
            $table->string('font_weight', 10)->nullable()->after('font_family');
            $table->string('font_style', 10)->nullable()->after('font_weight');
            $table->string('text_border', 10)->nullable()->after('font_style');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('buttons', function (Blueprint $table) {
            $table->dropColumn(['font_weight', 'font_style', 'text_border']);
        });

        Schema::table('artists', function (Blueprint $table) {
            $table->dropColumn([
                'cover_color',
                'wallpaper_url',
                'wallpaper_repeat',
                'wallpaper_preset_id',
                'profile_font_family',
                'profile_font_size',
                'profile_text_color',
                'profile_font_weight',
                'profile_font_style',
                'profile_text_border',
                'button_font_family',
                'button_font_size',
                'button_font_weight',
                'button_font_style',
                'button_text_border',
            ]);
        });
    }
};
