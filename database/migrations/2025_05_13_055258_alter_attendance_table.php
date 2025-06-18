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
        Schema::table('attendances',function(Blueprint $table){
             $table->decimal('clock_in_latitude', 10, 7)->nullable();
    $table->decimal('clock_in_longitude', 10, 7)->nullable();
    $table->decimal('break_start_latitude', 10, 7)->nullable();
    $table->decimal('break_start_longitude', 10, 7)->nullable();
    $table->decimal('break_end_latitude', 10, 7)->nullable();
    $table->decimal('break_end_longitude', 10, 7)->nullable();
    $table->decimal('clock_out_latitude', 10, 7)->nullable();
    $table->decimal('clock_out_longitude', 10, 7)->nullable();
    $table->string('meter_reading')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances',function(Blueprint $table){
            $table->dropColumn('clock_in_latitude');
            $table->dropColumn('clock_in_longitude');
            $table->dropColumn('break_start_latitude');
            $table->dropColumn('break_end_latitude');
            $table->dropColumn('break_end_longitude');
            $table->dropColumn('clock_out_latitude');
            $table->dropColumn('clock_out_longitude');
            $table->dropColumn('meter_reading');

        });
    }
};
