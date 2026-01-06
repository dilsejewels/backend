<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('product_variations', function (Blueprint $table) {
            $table->decimal('diamond_weight', 8, 2)->nullable()->after('shape_id');
        });
    }

    public function down()
    {
        Schema::table('product_variations', function (Blueprint $table) {
            $table->dropColumn('diamond_weight');
        });
    }
};