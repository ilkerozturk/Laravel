<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE leads MODIFY status ENUM('called','demo_preparing','demo_ready','won','lost','postponed') NOT NULL DEFAULT 'demo_ready'");
    }

    public function down(): void
    {
        DB::statement("UPDATE leads SET status = 'demo_ready' WHERE status = 'demo_preparing'");
        DB::statement("ALTER TABLE leads MODIFY status ENUM('called','demo_ready','won','lost','postponed') NOT NULL DEFAULT 'demo_ready'");
    }
};
