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
        Schema::table('attendances', function (Blueprint $table) {
                        if (!Schema::hasColumn('attendances', 'branch')) {
                $table->string('branch')->nullable()->after('employee_id');
            }
            if (!Schema::hasColumn('attendances', 'status')) {
                $table->string('status')->default('present')->after('date');
            }
            if (!Schema::hasColumn('attendances', 'check_in')) {
                $table->string('check_in')->nullable()->after('status');
            }
            if (!Schema::hasColumn('attendances', 'check_out')) {
                $table->string('check_out')->nullable()->after('check_in');
            }
            if (!Schema::hasColumn('attendances', 'late')) {
                $table->tinyInteger('late')->default(0)->after('check_out');
            }
            if (!Schema::hasColumn('attendances', 'overtime')) {
                $table->decimal('overtime', 5, 2)->default(0)->after('late');
            }
            if (!Schema::hasColumn('attendances', 'night')) {
                $table->tinyInteger('night')->default(0)->after('overtime');
            }
            if (!Schema::hasColumn('attendances', 'bonus')) {
                $table->decimal('bonus', 10, 2)->default(0)->after('night');
            }
            if (!Schema::hasColumn('attendances', 'advance')) {
                $table->decimal('advance', 10, 2)->default(0)->after('bonus');
            }
            if (!Schema::hasColumn('attendances', 'notes')) {
                $table->text('notes')->nullable()->after('advance');
            }
            if (!Schema::hasColumn('attendances', 'month')) {
                $table->tinyInteger('month')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('attendances', 'year')) {
                $table->smallInteger('year')->nullable()->after('month');
            }
            if (!Schema::hasColumn('attendances', 'basic_salary_override')) {
                $table->decimal('basic_salary_override', 10, 2)->nullable()->after('year');
            }

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
             $table->dropColumn([
                'branch', 'status', 'check_in', 'check_out',
                'late', 'overtime', 'night', 'bonus', 'advance',
                'notes', 'month', 'year', 'basic_salary_override',
            ]);
        });
    }
};
