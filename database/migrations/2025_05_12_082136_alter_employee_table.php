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
        Schema::table('employees',function(Blueprint $table){
    $table->time('start_time');
    $table->time('end_time')->nullable();
    $table->decimal('latitude', 10, 7)->nullable();
    $table->decimal('longitude', 10, 7)->nullable();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees',function(Blueprint $table){
            $table->dropColumn('start_time');
            $table->dropColumn('end_time');
            $table->dropColumn('latitude');
            $table->dropColumn('longitude');
        });
    }
};
