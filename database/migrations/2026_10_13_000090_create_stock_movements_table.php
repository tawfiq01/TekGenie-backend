<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained('product_variants')->restrictOnDelete();
            $table->string('type', 20)->index();
            $table->integer('quantity');
            $table->boolean('affects_stock')->default(true);
            $table->unsignedInteger('balance_after');
            $table->string('reason', 255)->nullable();
            $table->foreignId('order_id')->nullable()->constrained('orders')->restrictOnDelete();
            $table->foreignId('order_return_id')->nullable()->constrained('order_returns')->restrictOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->restrictOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['product_variant_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
