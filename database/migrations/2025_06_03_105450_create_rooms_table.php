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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id('room_id');
            $table->string('room_number');
            $table->unsignedBigInteger('room_type_id');
            $table->enum('status', ['available', 'occupied', 'maintenance', 'reserved'])->default('available');
            $table->integer('floor');
            $table->timestamps();

            $table->foreign('room_type_id')->references('room_type_id')->on('room_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
