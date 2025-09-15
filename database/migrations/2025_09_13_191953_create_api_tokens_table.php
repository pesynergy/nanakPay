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
        Schema::create('api_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('ip', 45);
            $table->string('token', 255);
            $table->enum('status', ['Active', 'In-Active'])->default('In-Active');
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('api_tokens');
    }
};
