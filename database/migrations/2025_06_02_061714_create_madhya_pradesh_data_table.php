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
        Schema::create('madhya_pradesh_data', function (Blueprint $table) {
            $table->id();
             $table->string('state_id');
            $table->string('state_name');
            $table->string('district_id');
            $table->string('district_name');
            $table->string('subdistrict_id');
            $table->string('subdistrict_name');
            $table->string('area_id');
            $table->string('area_name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('madhya_pradesh_data');
    }
};
