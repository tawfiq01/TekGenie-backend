<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('label', 30)->nullable();
            $table->string('recipient_name', 100);
            $table->string('phone', 11);
            $table->unsignedSmallInteger('division_id');
            $table->foreign('division_id')->references('id')->on('divisions')->restrictOnDelete();
            $table->unsignedSmallInteger('district_id');
            $table->foreign('district_id')->references('id')->on('districts')->restrictOnDelete();
            $table->unsignedInteger('area_id')->nullable();
            $table->foreign('area_id')->references('id')->on('areas')->nullOnDelete();
            $table->string('area_text', 100)->nullable();
            $table->string('address_line', 255);
            $table->char('postcode', 4)->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
