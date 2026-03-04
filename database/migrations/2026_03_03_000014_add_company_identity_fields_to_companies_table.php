<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('company_title', 255)->nullable()->after('name');
            $table->string('tax_office', 190)->nullable()->after('company_title');
            $table->string('tax_number', 64)->nullable()->after('tax_office');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['company_title', 'tax_office', 'tax_number']);
        });
    }
};
