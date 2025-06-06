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
        Schema::create('workcenter_structures', function (Blueprint $table) {
            $table->id();
            $table->string('structure_code');
            $table->string('structure_name');
            $table->string('structure_type', 10);
            $table->string('structure_contract', 50);
            $table->bigInteger('structure_parent_id')->nullable();
            $table->bigInteger('user_create_id')->nullable();
            $table->bigInteger('user_update_id')->nullable();
            $table->varchar('multibatch', 5)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workcenter_structures');
    }
};
