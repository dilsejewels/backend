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
        Schema::table('categories', function (Blueprint $table) {
            // Add product_type column right after category_id
            $table->json('product_type')
                  ->nullable()
                  ->after('category_id')
                  ->comment('0=jewelry, 1=engagement, 2=wedding, 3=gifts, 4=sale');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('product_type');
        });
    }
};
