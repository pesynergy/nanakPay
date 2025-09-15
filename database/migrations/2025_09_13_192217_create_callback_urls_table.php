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
        Schema::create('callback_urls', function (Blueprint $table) {
            $table->id();
            $table->string('payin_callback', 255);
            $table->string('payout_callback', 255);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('callback_urls');
    }
};
