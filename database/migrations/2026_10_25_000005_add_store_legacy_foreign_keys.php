<?php

use App\Support\LegacyTreasurySchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * These foreign keys link the store's tables to EXISTING SISPAGOS masters.
     * Never creates, alters or removes a tm_* table.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        // Lightweight SQLite unit tests don't import the institutional schema.
        // The MariaDB integration CI must prove these real constraints separately.
        if ($driver === 'sqlite') {
            return;
        }

        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            throw new RuntimeException("Unsupported shared treasury database driver: {$driver}");
        }

        $missing = LegacyTreasurySchema::missing();
        if ($missing !== []) {
            throw new RuntimeException('Cannot attach SISPAGOS foreign keys: '.implode('; ', $missing));
        }

        // ID types in tesoreria.sql: signed INT(11), not unsigned BIGINT.
        Schema::table('store_office_settings', function (Blueprint $table) {
            $table->foreign('legacy_office_id', 'fk_store_office_tm')
                ->references('oficina_id')->on('tm_oficina')
                ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::table('store_catalog_items', function (Blueprint $table) {
            $table->foreign('legacy_office_id', 'fk_store_catalog_office_tm')
                ->references('oficina_id')->on('tm_oficina')
                ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('legacy_concept_id', 'fk_store_catalog_concept_tm')
                ->references('id_concepto')->on('tm_conceptos')
                ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::table('store_order_items', function (Blueprint $table) {
            $table->foreign('legacy_office_id', 'fk_store_order_item_office_tm')
                ->references('oficina_id')->on('tm_oficina')
                ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('legacy_concept_id', 'fk_store_order_item_concept_tm')
                ->references('id_concepto')->on('tm_conceptos')
                ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::table('store_reconciliation_batches', function (Blueprint $table) {
            $table->foreign('legacy_importer_id', 'fk_store_recon_importer_tm')
                ->references('usu_id')->on('tm_usuario')
                ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::table('store_reconciliation_entries', function (Blueprint $table) {
            $table->foreign('legacy_result_id', 'fk_store_recon_result_tm')
                ->references('i_resultado')->on('tm_resultado')
                ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('legacy_recaudacion_id', 'fk_store_recon_charge_tm')
                ->references('id_recaudacion')->on('tm_recaudacion')
                ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::table('store_payment_validations', function (Blueprint $table) {
            $table->foreign('legacy_reviewer_id', 'fk_store_validation_reviewer_tm')
                ->references('usu_id')->on('tm_usuario')
                ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::table('store_validation_certificates', function (Blueprint $table) {
            $table->foreign('legacy_issuer_id', 'fk_store_cert_issuer_tm')
                ->references('usu_id')->on('tm_usuario')
                ->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('legacy_revoker_id', 'fk_store_cert_revoker_tm')
                ->references('usu_id')->on('tm_usuario')
                ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::table('store_audit_events', function (Blueprint $table) {
            $table->foreign('legacy_staff_id', 'fk_store_audit_staff_tm')
                ->references('usu_id')->on('tm_usuario')
                ->restrictOnDelete()->restrictOnUpdate();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        foreach ([
            'store_audit_events' => ['fk_store_audit_staff_tm'],
            'store_validation_certificates' => ['fk_store_cert_revoker_tm', 'fk_store_cert_issuer_tm'],
            'store_payment_validations' => ['fk_store_validation_reviewer_tm'],
            'store_reconciliation_entries' => ['fk_store_recon_charge_tm', 'fk_store_recon_result_tm'],
            'store_reconciliation_batches' => ['fk_store_recon_importer_tm'],
            'store_order_items' => ['fk_store_order_item_concept_tm', 'fk_store_order_item_office_tm'],
            'store_catalog_items' => ['fk_store_catalog_concept_tm', 'fk_store_catalog_office_tm'],
            'store_office_settings' => ['fk_store_office_tm'],
        ] as $tableName => $keys) {
            Schema::table($tableName, function (Blueprint $table) use ($keys) {
                foreach ($keys as $key) {
                    $table->dropForeign($key);
                }
            });
        }
    }
};
