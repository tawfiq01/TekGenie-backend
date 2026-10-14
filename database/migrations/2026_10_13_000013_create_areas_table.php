<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('district_id')->index();
            $table->foreign('district_id')->references('id')->on('districts')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('name_bn', 100)->nullable();
            $table->foreignId('delivery_zone_id')->nullable()->constrained('delivery_zones')->nullOnDelete();
            $table->boolean('is_active')->default(true);

            $table->unique(['district_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('areas');
    }
};
