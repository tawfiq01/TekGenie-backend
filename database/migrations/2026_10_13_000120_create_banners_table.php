<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('placement', 30)->index();
            $table->string('title', 120)->nullable();
            $table->string('subtitle', 255)->nullable();
            $table->string('button_label', 40)->nullable();
            $table->string('link_url', 255)->nullable();
            $table->string('image_desktop_path');
            $table->string('image_mobile_path')->nullable();
            $table->string('alt_text', 200);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
