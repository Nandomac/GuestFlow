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
        Schema::create('workcenter_template', function (Blueprint $table) {
            $table->id();
            $table->integer('workcenter_structure_id');
            $table->integer('characteristic_id');
            $table->integer('cols');
            $table->integer('order');
            $table->integer('characteristic_group_id')->nullable();
            $table->integer('characteristic_group_order')->nullable();
            $table->bigInteger('parent_id')->nullable();

            $table->integer('user_create_id')->nullable();
            $table->integer('user_update_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workcenter_template');
    }
};
