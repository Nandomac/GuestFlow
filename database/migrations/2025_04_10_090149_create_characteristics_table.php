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
        Schema::create('characteristics', function (Blueprint $table) {
            $table->id(); 
            $table->string('code'); 
            $table->string('description');
            $table->string('type');
            $table->string('uom')->nullable();
            $table->string('datetype')->nullable();
            $table->unsignedBigInteger('id_bdlab')->nullable();

            $table->integer('user_create_id')->nullable();
            $table->integer('user_update_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('characteristics');
    }
};
