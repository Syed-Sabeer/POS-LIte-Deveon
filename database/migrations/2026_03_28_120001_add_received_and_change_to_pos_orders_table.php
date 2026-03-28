<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            $table->decimal('received_amount', 14, 2)->default(0)->after('paid_amount');
            $table->decimal('change_amount', 14, 2)->default(0)->after('received_amount');
        });
    }

    public function down(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            $table->dropColumn(['received_amount', 'change_amount']);
        });
    }
};
