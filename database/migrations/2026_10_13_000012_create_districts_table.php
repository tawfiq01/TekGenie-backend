<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('districts', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('division_id')->index();
            $table->foreign('division_id')->references('id')->on('divisions')->restrictOnDelete();
            $table->string('name', 50)->unique();
            $table->string('name_bn', 50)->nullable();
            $table->foreignId('delivery_zone_id')->nullable()->constrained('delivery_zones')->nullOnDelete();
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('districts');
    }
};
