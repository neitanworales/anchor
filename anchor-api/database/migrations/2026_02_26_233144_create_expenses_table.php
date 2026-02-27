<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('cost_center_id')->nullable()->constrained()->nullOnDelete();

            $table->date('expense_date');
            $table->string('vendor')->nullable();
            $table->string('currency', 3)->default('MXN');
            $table->decimal('amount', 12, 2);
            $table->decimal('tax_iva', 12, 2)->nullable();
            $table->string('payment_method', 30)->nullable(); // CASH|CARD|COMPANY_CARD
            $table->string('receipt_type', 10)->default('TICKET'); // TICKET|CFDI

            // CFDI data (si aplica)
            $table->string('cfdi_uuid', 36)->nullable()->index();
            $table->string('cfdi_emitter_rfc', 13)->nullable();
            $table->string('cfdi_emitter_name')->nullable();
            $table->dateTime('cfdi_issue_datetime')->nullable();
            $table->string('status', 20)->default('DRAFT'); // DRAFT|IN_REPORT|LOCKED
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
