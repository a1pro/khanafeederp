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
        Schema::table('meetings',function(Blueprint $table){
            $table->string('dealer_name')->after('date')->nullable();
            $table->string('firm_name')->after('dealer_name')->nullable();
            $table->string('sale')->after('firm_name')->nullable();
            $table->string('bags')->after('sale')->nullable();
            $table->string('farmer')->after('sale')->nullable();
            $table->string('farmer_name')->after('farmer')->nullable();
            $table->string('dob')->after('farmer_name')->nullable();
            $table->string('phone')->after('dob')->nullable();
            $table->string('address')->after('phone')->nullable();
            $table->string('record')->after('address')->nullable();
            $table->string('cattle_buff')->after('record')->nullable();
            $table->string('cattle_cock')->after('cattle_buff')->nullable();
            $table->string('cattle_calf')->after('cattle_cock')->nullable();
            $table->string('trail_bag')->after('cattle_calf')->nullable();
            $table->string('schedule')->after('trail_bag')->nullable();
            $table->string('meeting_note')->after('schedule')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meetings',function(Blueprint $table){
 $table->string('dealer_name')->after('date')->nullable();
            $table->dropColumn('firm_name');
            $table->dropColumn('sale');
            $table->dropColumn('bags');
            $table->dropColumn('farmer');
            $table->dropColumn('farmer_name');
            $table->dropColumn('dob');
            $table->dropColumn('phone');
            $table->dropColumn('address');
            $table->dropColumn('record');
            $table->dropColumn('cattle_buff');
            $table->dropColumn('cattle_cock');
            $table->dropColumn('cattle_calf');
            $table->dropColumn('trail_bag');
            $table->dropColumn('schedule');
            $table->dropColumn('meeting_note');
        });
    }
};
