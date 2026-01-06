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
        Schema::create('contact_us_responses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('contact_us_id');
            $table->unsignedBigInteger('responded_by')->nullable();
            $table->text('message');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_us_responses');
    }
};
