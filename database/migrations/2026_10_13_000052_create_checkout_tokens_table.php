<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checkout_tokens', function (Blueprint $table) {
            $table->id();
            $table->char('token', 40)->unique();
            $table->foreignId('cart_id')->nullable()->constrained('carts')->nullOnDelete();
            $table->string('session_id')->index();
            $table->timestamp('used_at')->nullable();
            // order_id references the orders table created later; the FK constraint
            // is added in 2026_10_13_000061_add_order_foreign_key_to_checkout_tokens_table.
            $table->unsignedBigInteger('order_id')->nullable()->unique();
            $table->timestamp('expires_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkout_tokens');
    }
};
