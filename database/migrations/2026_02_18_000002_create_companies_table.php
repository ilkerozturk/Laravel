<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('place_id')->unique();
            $table->string('name', 190);
            $table->string('phone', 64)->nullable();
            $table->string('email', 190)->nullable();
            $table->string('address')->nullable();
            $table->string('city', 120)->nullable();
            $table->string('district', 120)->nullable();
            $table->string('website')->nullable();
            $table->string('google_category', 190)->nullable();
            $table->string('activity_area')->nullable();
            $table->decimal('activity_confidence', 4, 3)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
