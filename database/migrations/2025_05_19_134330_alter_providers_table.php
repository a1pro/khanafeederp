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
        Schema::table('purchases',function(Blueprint $table){
            $table->string('item_id')->nullable()->after('warehouse_id');
            $table->string('quantity')->nullable()->after('item_id');
            $table->string('price')->nullable()->after('quantity');
            $table->string('note')->nullable()->after('price');
            $table->string('expected_deliver')->after('note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
                Schema::table('purchases',function(Blueprint $table){
                $table->dropColumn('item_id');
                $table->dropColumn('quantity');
                $table->dropColumn('price');
                $table->dropColumn('note');
                $table->dropColumn('expected_deliver');


                });
    }
};
