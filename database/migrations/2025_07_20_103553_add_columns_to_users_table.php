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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->foreignId('office_id')->nullable()->constrained('offices')->nullOnDelete()->after('phone');
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete()->after('office_id');
            $table->foreignId('designation_id')->nullable()->constrained('designations')->nullOnDelete()->after('department_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
