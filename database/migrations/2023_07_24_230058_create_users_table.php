
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
            $table->integer('mobile_no')->unique();
            $table->string('name');
            $table->double('rating')->nullable();
            $table->string('profile_img')->default("https://www.google.com/url?sa=i&url=https%3A%2F%2Fpixabay.com%2Fvectors%2Fblank-profile-picture-mystery-man-973460%2F&psig=AOvVaw3sqjRffihDuDWGUV2b1t_i&ust=1691052811044000&source=images&cd=vfe&opi=89978449&ved=0CBEQjRxqFwoTCIiUxeLMvYADFQAAAAAdAAAAABAE");
            $table->boolean('is_driver')->default(false);
            $table->string('current_location')->nullable();
            $table->time('mobile_no_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
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

