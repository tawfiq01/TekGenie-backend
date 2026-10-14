<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 24)->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('pending');
            $table->string('payment_status', 20)->default('unpaid')->index();
            $table->string('payment_method', 30);
            $table->string('customer_name', 100);
            $table->string('phone', 11)->index();
            $table->string('email', 191)->nullable();
            $table->string('division_name', 50);
            $table->string('district_name', 50);
            $table->string('area_name', 100)->nullable();
            $table->unsignedSmallInteger('district_id')->nullable();
            $table->foreign('district_id')->references('id')->on('districts')->nullOnDelete();
            $table->string('address_line', 255);
            $table->char('postcode', 4)->nullable();
            $table->string('delivery_instructions', 500)->nullable();
            $table->foreignId('delivery_zone_id')->nullable()->constrained('delivery_zones')->nullOnDelete();
            $table->string('delivery_zone_name', 60);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('delivery_charge', 12, 2);
            $table->decimal('grand_total', 12, 2);
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
            $table->string('coupon_code', 32)->nullable();
            $table->string('courier_name', 60)->nullable();
            $table->string('tracking_reference', 100)->nullable();
            $table->string('customer_note', 500)->nullable();
            $table->string('cancel_reason', 255)->nullable();
            $table->timestamp('stock_restored_at')->nullable();
            $table->timestamp('coupon_released_at')->nullable();
            $table->timestamp('placed_at')->index();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();

            $table->index(['status', 'placed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
