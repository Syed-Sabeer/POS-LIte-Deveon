<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pos_order_items', function (Blueprint $table) {
            $table->decimal('cost_price', 12, 2)->default(0)->after('unit_price');
        });

        // Backfill historical rows with current product cost where possible.
        DB::statement('UPDATE pos_order_items poi LEFT JOIN products p ON p.id = poi.product_id SET poi.cost_price = COALESCE(p.cost_price, 0) WHERE poi.cost_price = 0');
    }

    public function down(): void
    {
        Schema::table('pos_order_items', function (Blueprint $table) {
            $table->dropColumn('cost_price');
        });
    }
};
