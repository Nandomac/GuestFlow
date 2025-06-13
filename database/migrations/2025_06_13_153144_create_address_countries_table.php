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
        Schema::create('address_countries', function (Blueprint $table) {
            $table->id();
            $table->string('country_name')->unique();
            $table->string('country_iso3', 3)->nullable();
            $table->string('country_iso2', 2)->nullable();
            $table->string('country_numeric_code', 3)->nullable();
            $table->string('country_phonecode')->nullable();
            $table->string('country_capital')->nullable();
            $table->string('country_currency', 3)->nullable();
            $table->string('country_currency_name')->nullable();
            $table->string('country_currency_symbol', 10)->nullable();
            $table->string('country_tld', 10)->nullable();
            $table->string('country_native')->nullable();
            $table->string('address_region_name')->nullable();
            $table->unsignedBigInteger('address_region_id')->nullable();
            $table->foreign('address_region_id')
                ->references('id')->on('address_regions')
                ->onDelete('cascade');
            $table->string('address_region_sub_name')->nullable();
            $table->unsignedBigInteger('address_region_sub_id')->nullable();
            $table->foreign('address_region_sub_id')
                ->references('id')->on('address_region_subs')
                ->onDelete('cascade');
            $table->string('country_nationality')->nullable();
            $table->json('country_timezones')->nullable();
            $table->json('country_translations')->nullable();
            $table->decimal('country_latitude', 12, 8)->nullable();
            $table->decimal('country_longitude', 12, 8)->nullable();
            $table->string('country_emoji', 10)->nullable();
            $table->string('country_emojiU', 20)->nullable();
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
        Schema::dropIfExists('address_countries');
    }
};
