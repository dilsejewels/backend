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
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            
            // Category with predefined options
            $table->enum('category', [
                'Engagement Rings',
                'Gemstone Insights',
                'Wedding Bands',
                'Metal',
                'Buying Guides',
                'Diamond',
                'Jewelry'
            ])->nullable();

            // Blog fields
            $table->string('title');
            $table->string('image')->nullable();
            $table->string('slug')->unique();
            $table->longText('paragraph')->nullable();
            $table->string('writer_name')->nullable();
            $table->string('read_time')->nullable(); // e.g. "5 min read"
            $table->date('publish_date')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};
