<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variations', function (Blueprint $table) {
            $table->unsignedInteger('diamond_quality_id')->nullable()->after('diamond_weight');

            $table->foreign('diamond_quality_id')
                  ->references('dqg_id')
                  ->on('diamond_quality_group')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('product_variations', function (Blueprint $table) {
            $table->dropForeign(['diamond_quality_id']);
            $table->dropColumn('diamond_quality_id');
        });
    }
};
