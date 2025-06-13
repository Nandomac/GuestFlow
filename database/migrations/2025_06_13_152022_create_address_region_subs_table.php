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
        Schema::create('address_region_subs', function (Blueprint $table) {
            $table->id();
            $table->string('region_sub_name')->unique();
            $table->string('region_sub_code')->unique()->nullable();
            $table->json('region_sub_data_translations')->nullable();
            $table->bigInteger('address_region_id')->unsigned();
            $table->foreign('address_region_id')
                ->references('id')->on('address_regions')
                ->onDelete('cascade');
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
        Schema::dropIfExists('address_region_subs');
    }
};
