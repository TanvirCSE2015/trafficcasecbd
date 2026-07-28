<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('case_invoices', function (Blueprint $table) {
            $table->string('status')
                ->after('created_by')
                ->charset('utf8mb4')
                ->collation('utf8mb4_unicode_ci');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('case_invoices', function (Blueprint $table) {
            //
        });
    }
};
