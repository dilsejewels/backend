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
        Schema::table('orders', function (Blueprint $table) {     
            //
            $table->json('billing_address')->after('address')->nullable();
            $table->String('transaction_id')->after('payment_mode')->nullable();
            $table->String('razorpay_payment_id')->after('transaction_id')->nullable();
            $table->String('razorpay_order_id')->after('razorpay_payment_id')->nullable();             

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['billing_address','transaction_id','razorpay_payment_id','razorpay_order_id']);
        });
    }
};
