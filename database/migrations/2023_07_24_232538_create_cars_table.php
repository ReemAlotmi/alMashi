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
        Schema::create('cars', function (Blueprint $table) {
            $table->id(); 
            $table->foreignId('user_id')->references('id')->on('users');     
            $table->string('type');
            $table->foreignId('classification_id')->references('id')->on('car_classifications'); 
            $table->integer('capacity');
            $table->string('plate');
            $table->string('color');
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
