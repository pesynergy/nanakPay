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
        Schema::create('payin_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('txnid')->nullable()->index();
            $table->string('payin_ref')->nullable();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->string('customer_name')->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->string('device_info')->nullable();
            $table->json('udf')->nullable();            // udf1-udf5 as array
            $table->string('status')->default('pending')->index();
            $table->string('bank_ref_num')->nullable();
            $table->string('rrn')->nullable();
            $table->json('payload')->nullable();        // request
            $table->json('response')->nullable();       // last response / webhook payload
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payin_transactions');
    }
};
