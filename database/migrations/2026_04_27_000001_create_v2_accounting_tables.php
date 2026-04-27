<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('v2_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('v2_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('v2_brands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('v2_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_type', 30)->index();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->date('opening_date')->nullable()->index();
            $table->decimal('opening_amount', 15, 2)->default(0);
            $table->decimal('currency_rate', 12, 4)->default(1);
            $table->boolean('is_system')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['account_type', 'is_active']);
        });

        Schema::create('v2_account_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('v2_accounts')->cascadeOnDelete();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('phone')->nullable();
            $table->string('fax')->nullable();
            $table->unsignedInteger('credit_days')->default(0);
            $table->string('contact')->nullable();
            $table->text('remarks')->nullable();
            $table->decimal('invoice_limit', 15, 2)->default(0);
            $table->decimal('ledger_limit', 15, 2)->default(0);
            $table->json('purchase_sale_sms_contacts')->nullable();
            $table->json('payment_receipt_sms_contacts')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('v2_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('v2_categories')->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('v2_brands')->nullOnDelete();
            $table->string('code', 20)->unique();
            $table->string('nick')->nullable();
            $table->string('description');
            $table->decimal('bf_qty', 15, 3)->default(0);
            $table->decimal('minimum_qty', 15, 3)->default(0);
            $table->decimal('maximum_qty', 15, 3)->default(0);
            $table->string('packing')->nullable();
            $table->decimal('packet_qty', 15, 3)->default(0);
            $table->decimal('opening_cost', 15, 2)->default(0);
            $table->decimal('cost', 15, 2)->default(0);
            $table->decimal('retail_rate', 15, 2)->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['category_id', 'brand_id', 'is_active']);
        });

        Schema::create('v2_invoices', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['purchase', 'sale'])->index();
            $table->foreignId('account_id')->constrained('v2_accounts')->restrictOnDelete();
            $table->string('party_name');
            $table->string('voucher_no')->index();
            $table->date('invoice_date')->index();
            $table->decimal('currency_rate', 12, 4)->default(1);
            $table->text('memo')->nullable();
            $table->decimal('gross_amount', 15, 2)->default(0);
            $table->decimal('charges', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2)->default(0);
            $table->decimal('received_amount', 15, 2)->default(0);
            $table->enum('status', ['posted', 'cancelled'])->default('posted')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['type', 'voucher_no']);
        });

        Schema::create('v2_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('v2_invoices')->cascadeOnDelete();
            $table->foreignId('item_id')->nullable()->constrained('v2_items')->nullOnDelete();
            $table->string('item_code')->nullable();
            $table->string('item_name');
            $table->string('item_detail')->nullable();
            $table->decimal('qty', 15, 3)->default(0);
            $table->decimal('packet', 15, 3)->default(0);
            $table->decimal('rate', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('discounted_rate', 15, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('v2_vouchers', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['receipt', 'payment', 'journal'])->index();
            $table->string('voucher_no')->index();
            $table->date('voucher_date')->index();
            $table->date('post_date')->nullable()->index();
            $table->foreignId('account_id')->nullable()->constrained('v2_accounts')->restrictOnDelete();
            $table->foreignId('contra_account_id')->nullable()->constrained('v2_accounts')->restrictOnDelete();
            $table->text('particulars')->nullable();
            $table->decimal('currency_rate', 12, 4)->default(1);
            $table->decimal('amount', 15, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->enum('status', ['posted', 'cancelled'])->default('posted')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['type', 'voucher_no']);
        });

        Schema::create('v2_voucher_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained('v2_vouchers')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('v2_accounts')->restrictOnDelete();
            $table->string('account_code')->nullable();
            $table->string('account_name')->nullable();
            $table->text('particulars')->nullable();
            $table->date('post_date')->nullable();
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('v2_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('v2_items')->restrictOnDelete();
            $table->string('source_type', 50)->index();
            $table->unsignedBigInteger('source_id')->index();
            $table->date('movement_date')->index();
            $table->string('voucher_no')->nullable()->index();
            $table->foreignId('account_id')->nullable()->constrained('v2_accounts')->nullOnDelete();
            $table->decimal('qty_in', 15, 3)->default(0);
            $table->decimal('qty_out', 15, 3)->default(0);
            $table->decimal('rate', 15, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('packing')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['item_id', 'movement_date']);
        });

        Schema::create('v2_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('v2_accounts')->restrictOnDelete();
            $table->string('source_type', 50)->index();
            $table->unsignedBigInteger('source_id')->index();
            $table->date('entry_date')->index();
            $table->string('voucher_no')->nullable()->index();
            $table->text('particulars')->nullable();
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->decimal('running_balance', 15, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['account_id', 'entry_date']);
        });

        Schema::create('v2_user_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('permission_key')->index();
            $table->boolean('can_view')->default(false);
            $table->boolean('can_insert')->default(false);
            $table->boolean('can_edit')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->timestamps();
            $table->unique(['user_id', 'permission_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('v2_user_permissions');
        Schema::dropIfExists('v2_ledger_entries');
        Schema::dropIfExists('v2_stock_movements');
        Schema::dropIfExists('v2_voucher_lines');
        Schema::dropIfExists('v2_vouchers');
        Schema::dropIfExists('v2_invoice_items');
        Schema::dropIfExists('v2_invoices');
        Schema::dropIfExists('v2_items');
        Schema::dropIfExists('v2_account_details');
        Schema::dropIfExists('v2_accounts');
        Schema::dropIfExists('v2_brands');
        Schema::dropIfExists('v2_categories');
        Schema::dropIfExists('v2_settings');
    }
};
