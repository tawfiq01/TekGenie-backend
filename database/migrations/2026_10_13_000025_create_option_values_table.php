<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('option_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('option_type_id')->constrained('option_types')->cascadeOnDelete();
            $table->string('value', 60);
            $table->char('swatch_hex', 7)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['option_type_id', 'value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('option_values');
    }
};
