<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('demo_projects', function (Blueprint $table) {
            $table->unsignedTinyInteger('progress_percent')->default(0)->after('download_token');
        });
    }

    public function down(): void
    {
        Schema::table('demo_projects', function (Blueprint $table) {
            $table->dropColumn('progress_percent');
        });
    }
};
