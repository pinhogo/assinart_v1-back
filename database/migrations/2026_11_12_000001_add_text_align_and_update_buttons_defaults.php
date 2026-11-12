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
        Schema::table('buttons', function (Blueprint $table) {
            $table->string('text_align', 10)->default('left')->after('text_border');
            $table->boolean('show_icon_background')->default(false)->change();
        });

        Schema::table('artists', function (Blueprint $table) {
            $table->string('avatar_url', 4096)->nullable()->change();
            $table->string('cover_url', 4096)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('artists', function (Blueprint $table) {
            $table->string('avatar_url', 2048)->nullable()->change();
            $table->string('cover_url', 2048)->nullable()->change();
        });

        Schema::table('buttons', function (Blueprint $table) {
            $table->boolean('show_icon_background')->default(true)->change();
            $table->dropColumn('text_align');
        });
    }
};
