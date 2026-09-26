<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();

            // Pricing
            $table->decimal('quantity', 15, 4);
            $table->string('unit', 50)->nullable();
            $table->decimal('unit_price', 15, 4)->nullable();
            $table->string('currency', 10)->default('USD');
            $table->decimal('total_price', 20, 4)->nullable();

            // Trade terms
            $table->string('shipping_term', 100)->nullable();   // Incoterms: FOB, CIF …
            $table->string('delivery_point', 100)->nullable();
            $table->string('delivery_address', 500)->nullable();

            // Status
            $table->string('status', 50)->default('pending');   // pending/confirmed/shipped/completed/cancelled
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
