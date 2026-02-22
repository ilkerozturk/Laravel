<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('outreach_emails')) {
            Schema::create('outreach_emails', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
                $table->string('to_email', 190);
                $table->string('subject', 255);
                $table->longText('body_html');
                $table->enum('status', ['draft', 'sent', 'failed'])->default('draft');
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('follow_ups')) {
            Schema::create('follow_ups', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
                $table->timestamp('due_at');
                $table->enum('status', ['open', 'done', 'canceled'])->default('open');
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('outreach_emails');
        Schema::dropIfExists('follow_ups');
    }
};
