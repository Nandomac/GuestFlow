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
        Schema::create('workcenter_part_characteristics', function (Blueprint $table) {
            $table->id();
            $table->integer('workcenter_part_id');
            $table->integer('characteristic_group_id')->nullable();
            $table->integer('characteristic_group_order')->nullable();
            $table->integer('characteristic_id');
            $table->integer('cols');
            $table->integer('order');
            $table->string('nominal_value');
            $table->string('tolerance_value');
            $table->integer('user_create_id');
            $table->integer('user_update_id')->nullable();
            $table->integer('user_delete_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workcenter_part_characteristics');
    }
};
