<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tllincoln_empty_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('tllincoln_hotel_id');
            $table->integer('tllincoln_roomtype_code');
            $table->string('tllincoln_sell_date');
            $table->integer('tllincoln_room_empty')->nullable();
            $table->tinyInteger('tllincoln_flag');
            $table->bigInteger('tllincoln_updated_at');
            $table->tinyInteger('tllincoln_sell_status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tllincoln_empty_rooms');
    }
};
