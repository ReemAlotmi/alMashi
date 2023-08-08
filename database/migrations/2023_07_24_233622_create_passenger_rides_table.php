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
        Schema::create('passenger_rides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users');
            $table->double('cost');
            $table->foreignId('ride_id')->references('id')->on('rides');
            $table->string('departure');
            $table->string('destination');
            $table->timestamps();


            $table->double('cost')->nullable()->change();
            $table->unsignedBigInteger('ride_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('passenger_rides');
    }
};
