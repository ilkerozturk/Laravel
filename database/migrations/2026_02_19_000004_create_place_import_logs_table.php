<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('place_import_logs', function (Blueprint $table) {
            $table->id();
            $table->string('city', 120);
            $table->string('district', 120);
            $table->string('keyword', 190)->nullable();
            $table->unsignedTinyInteger('max_pages')->default(1);
            $table->unsignedTinyInteger('pages_processed')->default(0);
            $table->unsignedInteger('fetched_result_count')->default(0);
            $table->unsignedInteger('created_count')->default(0);
            $table->unsignedInteger('updated_count')->default(0);
            $table->unsignedInteger('skipped_count')->default(0);
            $table->unsignedInteger('new_lead_count')->default(0);
            $table->timestamp('executed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('place_import_logs');
    }
};
