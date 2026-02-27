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
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('company_id')->after('id')->constrained()->cascadeOnDelete();
            $table->unique(['company_id', 'name']);
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('company_id')->after('id')->constrained()->cascadeOnDelete();
            $table->index(['company_id', 'user_id']);
            $table->unique(['company_id', 'cfdi_uuid']); // evita duplicar UUID dentro de la empresa (si aplica)
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->foreignId('company_id')->after('id')->constrained()->cascadeOnDelete();
            $table->index(['company_id', 'user_id']);
        });

        Schema::table('approvals', function (Blueprint $table) {
            $table->foreignId('company_id')->after('id')->constrained()->cascadeOnDelete();
            $table->index(['company_id', 'report_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropUnique(['company_id', 'name']);
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropIndex(['company_id', 'user_id']);
            $table->dropUnique(['company_id', 'cfdi_uuid']);
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropIndex(['company_id', 'user_id']);
        });

        Schema::table('approvals', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropIndex(['company_id', 'report_id']);
        });
    }
};
