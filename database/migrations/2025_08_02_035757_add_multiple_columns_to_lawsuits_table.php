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
            $table->enum('case_status', ['pending', 'approved',])->default('pending')->after('lawsuit_date');
            $table->string('vehicle_type')->charset('utf8mb4')->collation('utf8mb4_general_ci')->nullable()->after('case_status');
            $table->string('location')->charset('utf8mb4')->collation('utf8mb4_general_ci')->nullable()->after('vehicle_type');
            $table->foreignId('office_id')->nullable()->constrained()->cascadeOnDelete()->after('location');
            $table->integer('discount')->default(0)->after('total_amount');
            $table->float('discount_amount',8,2)->default(0)->after('discount');
            $table->float('pay_amount',8,2)->default(0)->after('discount_amount');
            $table->integer('mp_percentage')->default(25)->after('pay_amount');
            $table->float('mp_amount',8,2)->default(0)->after('mp_percentage');
            $table->float('board_amount',8,2)->default(0)->after('mp_amount');
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
