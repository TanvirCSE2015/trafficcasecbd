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
        Schema::create('case_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lawsuit_id')->constrained()->onDelete('cascade');
            $table->string('invoice_number')->unique();
            $table->string('car_no')->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('invoice_date');
            $table->integer('month');
            $table->string('month_name');
            $table->string('year');
            $table->float('total_amount', 8, 2);
            $table->float('discount', 8, 2)->default(0);
            $table->float('discount_amount', 8, 2)->default(0);
            $table->float('pay_amount', 8, 2)->default(0);
            $table->integer('mp_percentage')->default(25);
            $table->float('mp_amount', 8, 2)->default(0);
            $table->float('board_amount', 8, 2)->default(0);
            $table->foreignId('office_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_invoices');
    }
};
