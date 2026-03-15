<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('party_ledgers', function (Blueprint $table) {
            $table->id();
            $table->enum('party_type', ['customer', 'supplier'])->index();
            $table->unsignedBigInteger('party_id')->index();
            $table->date('entry_date')->index();
            $table->enum('voucher_type', [
                'sale',
                'sale_payment',
                'purchase',
                'purchase_payment',
                'opening_balance',
                'sale_return',
                'purchase_return',
                'adjustment',
            ])->index();
            $table->unsignedBigInteger('voucher_id')->nullable()->index();
            $table->string('reference_no')->nullable()->index();
            $table->string('description');
            $table->decimal('debit', 14, 2)->default(0);
            $table->decimal('credit', 14, 2)->default(0);
            $table->decimal('balance', 14, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['party_type', 'party_id', 'entry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('party_ledgers');
    }
};
