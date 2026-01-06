<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('parent_id')->nullable(); // For replies
            $table->string('user_name')->nullable(); // For guest users
            $table->tinyInteger('rating')->nullable()->comment('1-5 star rating (optional)');
            $table->text('comment')->nullable();
            $table->unsignedInteger('likes_count')->default(0);
            $table->unsignedInteger('dislikes_count')->default(0);
            $table->unsignedInteger('replies_count')->default(0);
            $table->unsignedInteger('shares_count')->default(0);
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('reviews')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('reviews');
    }
};