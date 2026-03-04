<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('companies') && Schema::hasColumn('companies', 'demo_prompt')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->dropColumn('demo_prompt');
            });
        }

        if (Schema::hasTable('demo_projects')) {
            Schema::drop('demo_projects');
        }

        if (Schema::hasTable('demo_sites')) {
            Schema::drop('demo_sites');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('companies') && !Schema::hasColumn('companies', 'demo_prompt')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->longText('demo_prompt')->nullable()->after('activity_confidence');
            });
        }

        if (!Schema::hasTable('demo_projects')) {
            Schema::create('demo_projects', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->string('title', 190);
                $table->enum('status', ['pending', 'generated', 'failed'])->default('pending');
                $table->longText('prompt_text')->nullable();
                $table->string('folder_path', 255)->nullable();
                $table->string('zip_path', 255)->nullable();
                $table->string('download_token', 80)->unique();
                $table->unsignedTinyInteger('progress_percent')->default(0);
                $table->text('error_message')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('demo_sites')) {
            Schema::create('demo_sites', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
                $table->longText('prompt_text');
                $table->string('deploy_url', 255)->nullable();
                $table->enum('status', ['pending', 'generated', 'deployed', 'failed'])->default('pending');
                $table->timestamps();
            });
        }
    }
};
