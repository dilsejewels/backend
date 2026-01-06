<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('product_collections', function (Blueprint $table) {
            $table->json('product_type')->nullable()->after('id')
                ->comment('Multiple values allowed: 0=jewelry, 1=engagement, 2=wedding, 3=gifts, 4=sale');

            $table->json('product_category_ids')->nullable()->after('product_type');
            $table->json('parent_category_ids')->nullable()->after('product_category_ids');

            $table->dropColumn(['product_category_id', 'parent_category_id']);
        });
    }

    public function down(): void
    {
        Schema::table('product_collections', function (Blueprint $table) {
            $table->dropColumn(['product_type', 'product_category_ids', 'parent_category_ids']);

            $table->unsignedInteger('product_category_id')->nullable();
            $table->unsignedInteger('parent_category_id')->nullable();
        });
    }
};
