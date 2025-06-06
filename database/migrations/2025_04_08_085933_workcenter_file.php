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
        Schema::create('workcenter_files', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('workcenter_structure_id');
            $table->string('path');
            $table->bigInteger('user_create_id')->nullable();
            $table->bigInteger('user_update_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workcenter_files');
    }
};
