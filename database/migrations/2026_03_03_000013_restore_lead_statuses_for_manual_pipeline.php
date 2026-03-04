<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("UPDATE leads SET status = 'called' WHERE status = 'email_sent'");
        DB::statement("UPDATE leads SET status = 'demo_ready' WHERE status IN ('demo_preparing', 'completed')");

        DB::statement("ALTER TABLE leads MODIFY status ENUM('called','demo_ready','won','lost','postponed') NOT NULL DEFAULT 'demo_ready'");
    }

    public function down(): void
    {
        DB::statement("UPDATE leads SET status = 'completed' WHERE status = 'demo_ready'");
        DB::statement("UPDATE leads SET status = 'called' WHERE status IN ('lost', 'postponed')");

        DB::statement("ALTER TABLE leads MODIFY status ENUM('email_sent','called','demo_preparing','completed','won') NOT NULL DEFAULT 'demo_preparing'");
    }
};
