<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('review_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained('reviews')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('guest_identifier')->nullable(); // For guest users
            $table->string('platform')->nullable();
            $table->timestamps();

            $table->unique(['review_id', 'user_id']); // one share per user
            $table->unique(['review_id', 'guest_identifier']); // one share per guest
        });
    }

    public function down(): void {
        Schema::dropIfExists('review_shares');
    }
};