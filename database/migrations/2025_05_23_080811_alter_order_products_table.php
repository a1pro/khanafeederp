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
        schema::table('order_products',function(Blueprint $table){
            $table->enum('status',['placed','inprogress','dispatch','delivered'])->defatul('placed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    { schema::table('order_products',function(Blueprint $table){
            $table->dropColumn('status');
        });
        
    }
};
