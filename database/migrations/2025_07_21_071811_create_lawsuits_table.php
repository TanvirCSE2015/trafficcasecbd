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
        Schema::create('lawsuits', function (Blueprint $table) {
            $table->id();
            $table->string('vechicle_number')->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('month_name')->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->integer('month')->unsigned();
            $table->string('year')->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->enum('status', ['Paid', 'Unpaid', 'Released'])
                ->default('Unpaid')
                ->charset('utf8mb4')
                ->collation('utf8mb4_unicode_ci');
            $table->string('lawsuit_date')->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('pay_date')->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->foreignId('entry_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('paid_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->float('total_amount',8,2)->nullable();
            $table->string('box_no')->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('invoice_no')->unsigned()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lawsuits');
    }
};
