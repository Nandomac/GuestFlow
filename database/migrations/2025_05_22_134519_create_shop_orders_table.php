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
        Schema::create('shop_orders', function (Blueprint $table) {
            $table->id(); 
            $table->unsignedBigInteger('op_id');
            $table->unsignedBigInteger('workcenter_id');
            $table->string('order_no');
            $table->string('release_no');
            $table->string('sequence_no');
            $table->string('state');

            $table->timestamps();
            $table->integer('user_create_id')->nullable();
            $table->integer('user_update_id')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_orders');
    }
};
