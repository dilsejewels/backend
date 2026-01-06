<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('product_collections', function (Blueprint $table) {
            $table->dropForeign(['product_category_id']);
            $table->dropForeign(['parent_category_id']);
        });
    }

    public function down(): void
    {
        Schema::table('product_collections', function (Blueprint $table) {
            $table->foreign('product_category_id')
                  ->references('category_id')
                  ->on('categories')
                  ->onDelete('SET NULL');

            $table->foreign('parent_category_id')
                  ->references('category_id')
                  ->on('categories')
                  ->onDelete('SET NULL');
        });
    }
};
