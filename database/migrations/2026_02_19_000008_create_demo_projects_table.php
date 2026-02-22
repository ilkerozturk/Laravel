<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('demo_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('title', 190);
            $table->enum('status', ['pending', 'generated', 'failed'])->default('pending');
            $table->longText('prompt_text')->nullable();
            $table->string('folder_path', 255)->nullable();
            $table->string('zip_path', 255)->nullable();
            $table->string('download_token', 80)->unique();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demo_projects');
    }
};
