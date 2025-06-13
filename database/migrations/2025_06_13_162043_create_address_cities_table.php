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
        Schema::create('address_cities', function (Blueprint $table) {
            $table->id('city_id');
            $table->unsignedBigInteger('address_state_id');
            $table->string('city_state_code', 10)->nullable();
            $table->string('city_state_name')->nullable();
            $table->unsignedBigInteger('address_country_id');
            $table->string('city_country_code', 2)->nullable();
            $table->string('city_country_name')->nullable();
            $table->string('city_name');
            $table->decimal('city_latitude', 12, 8)->nullable();
            $table->decimal('city_longitude', 12, 8)->nullable();
            $table->string('city_wikiDataId', 50)->nullable();
            $table->bigInteger('user_create_id')->nullable();
            $table->bigInteger('user_update_id')->nullable();
            $table->bigInteger('user_delete_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('address_state_id')
                ->references('id')->on('address_states')
                ->onDelete('cascade');
            $table->foreign('address_country_id')
                ->references('id')->on('address_countries')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('address_cities');
    }
};
