<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('location_id');
            $table->timestamp('start')->nullable(false);
            $table->timestamp('end')->nullable();
            $table->integer('duration'); // segundos
            $table->decimal('price', 10, 2);
            $table->tinyInteger('status'); // @see EnumRentalStatus
            $table->timestamps();

            $table->foreign('location_id')->references('id')->on('locations');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
