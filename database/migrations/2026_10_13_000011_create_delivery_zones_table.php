<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60)->unique();
            $table->decimal('base_charge', 12, 2)->default(0);
            $table->decimal('free_delivery_threshold', 12, 2)->nullable();
            $table->string('estimate_text', 60)->nullable();
            $table->boolean('is_active')->default(false)->index();
            $table->smallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_zones');
    }
};
