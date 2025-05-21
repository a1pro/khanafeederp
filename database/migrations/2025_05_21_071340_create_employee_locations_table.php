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
        Schema::create('employee_locations', function (Blueprint $table) {
            $table->id();
            $table->string('emp_id');
            $table->string('check_in_lat')->nullable();
            $table->string('check_in_long')->nullable();
            $table->string('break_in_lat')->nullable();
            $table->string('break_in_long')->nullable();
            $table->string('break_out_lat')->nullable();
            $table->string('break_out_long')->nullable();
            $table->string('check_out_lat')->nullable();
            $table->string('check_out_long')->nullable();
            $table->string('attendance_date')->nullable();
            $table->string('attendance_time')->nullable();
            $table->string('meeting_lat')->nullable();
            $table->string('meeting_long')->nullable();
            $table->string('meeting_date')->nullable();
            $table->string('meeting_time')->nullable();
            $table->string('locationcode')->nullable();
            $table->enum('status',['Active','Inactive'])->default('Active');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_locations');
    }
};
