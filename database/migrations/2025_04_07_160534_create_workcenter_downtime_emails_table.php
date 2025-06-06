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
        Schema::create('workcenter_downtime_emails', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('workcenter_downtime_id');
            $table->string('email');
            $table->bigInteger('user_create_id')->nullable();
            $table->bigInteger('user_update_id')->nullable();
            $table->bigInteger('user_delete_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workcenter_downtime_emails');
    }
};
