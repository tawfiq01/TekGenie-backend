<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_option_type', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('option_type_id')->constrained('option_types')->cascadeOnDelete();
            $table->integer('sort_order')->default(0);

            $table->primary(['product_id', 'option_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_option_type');
    }
};
