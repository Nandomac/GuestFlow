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
        Schema::create('address_states', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('address_country_id');
            $table->string('state_country_code', 2);
            $table->string('state_country_name');
            $table->string('state_name');
            $table->string('state_code', 10)->nullable();
            $table->string('state_type', 100)->nullable();
            $table->decimal('state_latitude', 12, 8)->nullable();
            $table->decimal('state_longitude', 12, 8)->nullable();
            $table->bigInteger('user_create_id')->nullable();
            $table->bigInteger('user_update_id')->nullable();
            $table->bigInteger('user_delete_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

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
        Schema::dropIfExists('address_states');
    }
};
