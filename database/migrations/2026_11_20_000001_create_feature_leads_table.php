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
        Schema::create('feature_leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artist_id')->nullable()->constrained('artists')->nullOnDelete();
            $table->string('feature', 50)->default('vitrine');
            $table->string('source', 50);
            $table->string('contact_value', 255)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['feature', 'source']);
            $table->index(['artist_id', 'feature']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_leads');
    }
};
