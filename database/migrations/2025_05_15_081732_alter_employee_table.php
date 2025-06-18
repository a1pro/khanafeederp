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
            $table->string('state')->nullable()->after('country');
            $table->string('district')->nullable()->after('state');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees',function(Blueprint $table){
            $table->dropColumn('state');
            $table->dropColumn('district');
        });
    }
};
