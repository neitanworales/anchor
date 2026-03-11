<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('cfdi_type', 1)->nullable()->after('receipt_type'); // I,E,T,N,P
            $table->string('cfdi_receiver_rfc', 13)->nullable()->after('cfdi_emitter_name');
            $table->string('cfdi_currency', 3)->nullable()->after('cfdi_receiver_rfc');
            $table->decimal('cfdi_subtotal', 12, 2)->nullable()->after('cfdi_currency');
            $table->decimal('cfdi_total', 12, 2)->nullable()->after('cfdi_subtotal');
            $table->boolean('xml_uploaded')->default(false)->after('cfdi_total');
            $table->string('xml_original_name')->nullable()->after('xml_uploaded');
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn([
                'cfdi_type',
                'cfdi_receiver_rfc',
                'cfdi_currency',
                'cfdi_subtotal',
                'cfdi_total',
                'xml_uploaded',
                'xml_original_name',
            ]);
        });
    }
};