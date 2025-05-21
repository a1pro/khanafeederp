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
        Schema::table('providers',function(Blueprint $table){
        	$table->string('state')->nullable()->after('country');
        	$table->string('district')->nullable()->after('state');
        	$table->string('tehsil')->nullable()->after('district');
        	$table->string('zipcode')->nullable()->after('tehsil');
        	$table->enum('status',['Active','Inactive'])->default('Active')->after('adresse');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('providers',function(Blueprint $table){
        	$table->dropColumn('state');
        	$table->dropColumn('district');
        	$table->dropColumn('tehsil');
        	$table->dropColumn('zipcode');
        	$table->dropColumn('status');

        });
    }
};
