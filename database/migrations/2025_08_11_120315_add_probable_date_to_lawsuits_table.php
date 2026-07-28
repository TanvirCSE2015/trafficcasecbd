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
        Schema::table('lawsuits', function (Blueprint $table) {
            $table->string('probable_date')->after('lawsuit_date')->nullable()->charset('utf8mb4')
            ->collation('utf8mb4_unicode_ci');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lawsuits', function (Blueprint $table) {
            //
        });
    }
};
