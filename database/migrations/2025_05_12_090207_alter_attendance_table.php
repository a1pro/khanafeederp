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
             $table->string('meter_image')->nullable(); // Image path for meter at clock-in/clock-out
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Adding columns for break tracking
            $table->time('break_start_time')->nullable();
            $table->time('break_end_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances',function(Blueprint $table){
            $table->dropColumn('meter_image');
            $table->dropColumn('latitude');
            $table->dropColumn('longitude');
            $table->dropColumn('break_start_time');
            $table->dropColumn('break_end_time');
        });
    }
};
