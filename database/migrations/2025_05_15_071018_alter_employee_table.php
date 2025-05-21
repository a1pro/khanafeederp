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
            $table->string('gm_name')->nullable()->after('phone');
            $table->string('agm_name')->nullable()->after('gm_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees',function(Blueprint $table){
            $table->dropColumn('gm_name');
            $table->dropColumn('agm_name');
        });
    }
};
