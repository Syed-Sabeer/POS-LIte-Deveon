<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('account_type', 50)->nullable()->after('name');
            $table->string('account_subtype')->nullable()->after('account_type');
            $table->enum('normal_balance', ['debit', 'credit'])->nullable()->after('account_subtype');
            $table->text('notes')->nullable()->after('is_active');
        });

        DB::table('accounts')->whereNull('account_type')->update([
            'account_type' => DB::raw('type'),
        ]);

        DB::statement("UPDATE accounts SET normal_balance = CASE WHEN COALESCE(account_type, type) IN ('asset','expense') THEN 'debit' ELSE 'credit' END WHERE normal_balance IS NULL");

        Schema::table('journal_entries', function (Blueprint $table) {
            $table->string('source_type')->nullable()->after('reference_no')->index();
            $table->unsignedBigInteger('source_id')->nullable()->after('source_type')->index();
            $table->enum('status', ['draft', 'posted', 'cancelled'])->default('posted')->after('description')->index();
        });

        DB::statement("UPDATE journal_entries SET source_type = voucher_type WHERE source_type IS NULL");
        DB::statement("UPDATE journal_entries SET source_id = voucher_id WHERE source_id IS NULL");

        Schema::table('party_ledgers', function (Blueprint $table) {
            $table->string('source_type')->nullable()->after('entry_date')->index();
            $table->unsignedBigInteger('source_id')->nullable()->after('source_type')->index();
            $table->decimal('running_balance', 14, 2)->default(0)->after('credit');
        });

        DB::statement("UPDATE party_ledgers SET source_type = voucher_type WHERE source_type IS NULL");
        DB::statement("UPDATE party_ledgers SET source_id = voucher_id WHERE source_id IS NULL");
        DB::statement("UPDATE party_ledgers SET running_balance = balance WHERE running_balance = 0");

        Schema::table('customer_payments', function (Blueprint $table) {
            $table->string('invoice_type')->nullable()->after('customer_id')->index();
            $table->unsignedBigInteger('invoice_id')->nullable()->after('invoice_type')->index();
            $table->foreignId('deposit_account_id')->nullable()->after('account_id')->constrained('accounts')->nullOnDelete();
        });

        DB::statement("UPDATE customer_payments SET invoice_type = 'pos_order' WHERE invoice_type IS NULL AND pos_order_id IS NOT NULL");
        DB::statement("UPDATE customer_payments SET invoice_id = pos_order_id WHERE invoice_id IS NULL AND pos_order_id IS NOT NULL");
        DB::statement("UPDATE customer_payments SET deposit_account_id = account_id WHERE deposit_account_id IS NULL AND account_id IS NOT NULL");

        Schema::table('supplier_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('bill_id')->nullable()->after('purchase_invoice_id')->index();
            $table->foreignId('payment_account_id')->nullable()->after('account_id')->constrained('accounts')->nullOnDelete();
        });

        DB::statement("UPDATE supplier_payments SET bill_id = purchase_invoice_id WHERE bill_id IS NULL AND purchase_invoice_id IS NOT NULL");
        DB::statement("UPDATE supplier_payments SET payment_account_id = account_id WHERE payment_account_id IS NULL AND account_id IS NOT NULL");

        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->decimal('total_amount', 14, 2)->nullable()->after('total');
            $table->enum('posting_status', ['draft', 'posted', 'cancelled'])->nullable()->after('status')->index();
        });

        DB::statement("UPDATE purchase_invoices SET total_amount = total WHERE total_amount IS NULL");
        DB::statement("UPDATE purchase_invoices SET posting_status = status WHERE posting_status IS NULL");

        Schema::table('pos_orders', function (Blueprint $table) {
            $table->decimal('total_amount', 14, 2)->nullable()->after('total');
            $table->enum('posting_status', ['draft', 'posted', 'cancelled'])->nullable()->after('status')->index();
        });

        DB::statement("UPDATE pos_orders SET total_amount = total WHERE total_amount IS NULL");
        DB::statement("UPDATE pos_orders SET posting_status = CASE WHEN status = 'completed' THEN 'posted' ELSE status END WHERE posting_status IS NULL");
    }

    public function down(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            $table->dropColumn(['total_amount', 'posting_status']);
        });

        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->dropColumn(['total_amount', 'posting_status']);
        });

        Schema::table('supplier_payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_account_id');
            $table->dropColumn('bill_id');
        });

        Schema::table('customer_payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('deposit_account_id');
            $table->dropColumn(['invoice_type', 'invoice_id']);
        });

        Schema::table('party_ledgers', function (Blueprint $table) {
            $table->dropColumn(['source_type', 'source_id', 'running_balance']);
        });

        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropColumn(['source_type', 'source_id', 'status']);
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['account_type', 'account_subtype', 'normal_balance', 'notes']);
        });
    }
};
