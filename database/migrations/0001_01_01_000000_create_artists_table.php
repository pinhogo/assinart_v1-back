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
        Schema::create('artists', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('username', 50)->unique();
            $table->string('email', 255)->unique();
            $table->string('password', 255);
            $table->string('bio', 500)->nullable();
            $table->string('avatar_url', 2048)->nullable();
            $table->string('cover_url', 2048)->nullable();

            // Estilos visuais Neo-Brutalistas da página
            $table->string('background_color', 20)->default('#FFFFFF');
            $table->string('background_id', 50)->nullable();
            $table->enum('button_shape', ['square', 'rounded', 'pill'])->default('square');
            $table->enum('avatar_shape', ['circle', 'square', 'rounded'])->default('circle');
            $table->string('font_family', 50)->default('Space Grotesk');
            $table->string('font_color', 20)->default('#000000');
            $table->string('border_style', 30)->default('solid-3px');
            $table->enum('icon_variant', ['default', 'black_outline', 'white_outline', 'none'])->default('default');

            // Autenticação & Auditoria
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('artists');
    }
};
