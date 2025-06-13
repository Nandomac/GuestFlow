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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->string('address_street');
            $table->string('address_number')->nullable();
            $table->string('address_complement')->nullable();
            $table->string('address_postal_code', 20)->nullable();
            $table->bigInteger('address_city_id')->nullable();
            $table->bigInteger('address_state_id')->nullable();
            $table->bigInteger('address_country_id')->nullable();
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
        Schema::dropIfExists('addresses');
    }
};
