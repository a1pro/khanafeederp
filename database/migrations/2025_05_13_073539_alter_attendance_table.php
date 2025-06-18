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
            $table->string('clock_out_meter_image')->nullable();
            $table->decimal('clock_out_meter_reading', 10, 2)->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances',function(Blueprint $table){
            $table->dropColumn('clock_out_meter_image');
            $table->dropColumn('clock_out_meter_reading');
        });
    }
};
