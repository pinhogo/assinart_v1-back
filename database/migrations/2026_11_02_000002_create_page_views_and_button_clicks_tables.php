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
        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artist_id')->constrained('artists')->cascadeOnDelete();
            $table->string('visitor_id', 64);
            $table->string('referrer_source', 50)->default('direct');
            $table->string('device_type', 20)->default('desktop');
            $table->string('utm_source', 100)->nullable();
            $table->string('referrer', 2048)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('viewed_at')->useCurrent();
            $table->timestamps();

            $table->index(['artist_id', 'viewed_at']);
            $table->index(['artist_id', 'visitor_id']);
        });

        Schema::create('button_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('button_id')->constrained('buttons')->cascadeOnDelete();
            $table->string('visitor_id', 64)->nullable();
            $table->timestamp('clicked_at')->useCurrent();
            $table->timestamps();

            $table->index(['button_id', 'clicked_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('button_clicks');
        Schema::dropIfExists('page_views');
    }
};
