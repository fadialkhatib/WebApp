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
        Schema::create('active_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBiginteger('User_id');
            $table->foreign('User_id')->references('id')->on('Users')->onDelete('cascade');
            $table->String('token');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('active_tokens');
    }
};
