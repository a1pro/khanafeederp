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
        Schema::table('clients',function(Blueprint $table){
            $table->string('state')->nullable()->after('country');
            $table->string('district')->nullable()->after('state');
            $table->string('tehsil')->nullable()->after('district');
            $table->string('village')->nullable()->after('tehsil');
            $table->string('document')->nullable()->after('adresse');
            $table->string('document_number')->nullable()->after('document');
            $table->string('document_image')->nullable()->after('document_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients',function(Blueprint $table){
            $table->dropColumn('state');
            $table->dropColumn('district');
            $table->dropColumn('tehsil');
            $table->dropColumn('village');
            $table->dropColumn('document');
            $table->dropColumn('document_number');
            $table->dropColumn('document_image');
        });
    }
};
