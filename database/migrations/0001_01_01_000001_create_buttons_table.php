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
        Schema::create('buttons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artist_id')
                ->constrained('artists')
                ->cascadeOnDelete();

            $table->string('title', 100);
            $table->string('url', 2048);
            $table->string('platform', 50)->nullable()->default('custom');
            $table->enum('type', ['standard', 'icon_pill', 'card_featured'])->default('standard');

            // Customizações individuais (se null, herda de artists)
            $table->string('button_color', 20)->nullable();
            $table->string('text_color', 20)->nullable();
            $table->string('font_family', 50)->nullable();
            $table->string('image_url', 2048)->nullable();

            // Ordenação, cliques e visibilidade
            $table->unsignedInteger('order_index')->default(0);
            $table->unsignedInteger('clicks_count')->default(0);
            $table->boolean('is_active')->default(true)->index();

            $table->timestamps();

            // Índice composto para buscar botões ordenados com máxima performance
            $table->index(['artist_id', 'order_index']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buttons');
    }
};
