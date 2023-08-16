
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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('mobile_no')->unique();
            $table->string('name');
            $table->double('rating')->nullable();
            $table->string('profile_img')->default("https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_1280.png");
            $table->boolean('is_driver')->default(false);
            $table->string('current_location')->nullable();
            $table->datetime('mobile_no_verified_at')->nullable();
            $table->timestamps();
            //https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_1280.png
            // Schema::table('users', function (Blueprint $table) {
            //     $table->string('profile_img')->default("https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_1280.png")->change();
            // });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

