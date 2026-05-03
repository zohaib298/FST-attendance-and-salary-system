<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_summaries', function (Blueprint $table) {
            if (!Schema::hasColumn('attendance_summaries', 'deductions')) {
                $table->decimal('deductions', 10, 2)->default(0)->after('advance');
            }
            if (!Schema::hasColumn('attendance_summaries', 'late_absents')) {
                $table->integer('late_absents')->default(0)->after('late_count');
            }
        });

        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'deductions')) {
                $table->decimal('deductions', 10, 2)->default(0)->after('advance');
            }
        });

        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::table('attendance_summaries', function (Blueprint $table) {
            $table->dropColumn(['deductions', 'late_absents']);
        });
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('deductions');
        });
    }
};