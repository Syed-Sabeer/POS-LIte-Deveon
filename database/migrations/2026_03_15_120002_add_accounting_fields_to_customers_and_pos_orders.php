<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->decimal('opening_balance', 14, 2)->default(0)->after('address');
            $table->enum('balance_type', ['dr', 'cr'])->default('dr')->after('opening_balance');
            $table->boolean('is_active')->default(true)->after('balance_type');
        });

        Schema::table('pos_orders', function (Blueprint $table) {
            $table->date('invoice_date')->nullable()->after('payment_method');
            $table->decimal('discount_amount', 14, 2)->default(0)->after('subtotal');
            $table->decimal('tax_amount', 14, 2)->default(0)->after('discount_amount');
            $table->decimal('paid_amount', 14, 2)->default(0)->after('total');
            $table->decimal('due_amount', 14, 2)->default(0)->after('paid_amount')->index();
            $table->enum('payment_status', ['paid', 'partial', 'unpaid'])->default('paid')->after('due_amount')->index();
            $table->enum('status', ['draft', 'completed', 'cancelled'])->default('completed')->after('payment_status')->index();
            $table->text('notes')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            $table->dropColumn([
                'invoice_date',
                'discount_amount',
                'tax_amount',
                'paid_amount',
                'due_amount',
                'payment_status',
                'status',
                'notes',
            ]);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['opening_balance', 'balance_type', 'is_active']);
        });
    }
};
