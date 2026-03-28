<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->restrictOnDelete();
            $table->date('entry_date')->index();
            $table->string('source_type')->nullable()->index();
            $table->unsignedBigInteger('source_id')->nullable()->index();
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->string('reference_no')->nullable()->index();
            $table->string('description')->nullable();
            $table->decimal('debit', 14, 2)->default(0);
            $table->decimal('credit', 14, 2)->default(0);
            $table->decimal('running_balance', 14, 2)->default(0);
            $table->timestamps();

            $table->index(['account_id', 'entry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_ledger_entries');
    }
};
