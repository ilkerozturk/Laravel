<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("UPDATE leads SET status = 'demo_preparing' WHERE status IN ('new', 'postponed')");
        DB::statement("UPDATE leads SET status = 'completed' WHERE status = 'demo_ready'");
        DB::statement("UPDATE leads SET status = 'called' WHERE status = 'call_due'");
        DB::statement("UPDATE leads SET status = 'completed' WHERE status = 'lost'");

        DB::statement("ALTER TABLE leads MODIFY status ENUM('email_sent','called','demo_preparing','completed','won') NOT NULL DEFAULT 'demo_preparing'");
    }

    public function down(): void
    {
        DB::statement("UPDATE leads SET status = 'new' WHERE status = 'demo_preparing'");
        DB::statement("UPDATE leads SET status = 'demo_ready' WHERE status = 'completed'");
        DB::statement("UPDATE leads SET status = 'call_due' WHERE status = 'called'");

        DB::statement("ALTER TABLE leads MODIFY status ENUM('new','demo_ready','email_sent','call_due','won','lost','postponed') NOT NULL DEFAULT 'new'");
    }
};
