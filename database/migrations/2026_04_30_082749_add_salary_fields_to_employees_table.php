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
        Schema::table('employees', function (Blueprint $table) {
            
        $table->decimal('bike_allowance', 10, 2)->default(0);
        $table->decimal('mobile_allowance', 10, 2)->default(0);
        $table->decimal('overtime_rate', 10, 2)->default(0);
        $table->decimal('commission', 10, 2)->default(0);
        $table->decimal('other_allowance', 10, 2)->default(0);

        $table->decimal('late_deduction', 10, 2)->default(0);
        $table->decimal('absent_deduction', 10, 2)->default(0);
        $table->integer('allowed_leaves')->default(2);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
             $table->dropColumn([
            'bike_allowance',
            'mobile_allowance',
            'overtime_rate',
            'commission',
            'other_allowance',
            'late_deduction',
            'absent_deduction',
            'allowed_leaves'
        ]);
        });
    }
};
