<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Additive performance optimization for published store catalog lookup.
 * Never changes SISPAGOS tm_* tables or existing data.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_catalog_items', function (Blueprint $table) {
            // PublishedCatalogQuery filters on BOTH office and publication status.
            // Existing unique keys begin with office but do not cover is_published.
            $table->index(
                ['legacy_office_id', 'is_published'],
                'idx_store_catalog_office_published'
            );
        });
    }

    public function down(): void
    {
        Schema::table('store_catalog_items', function (Blueprint $table) {
            $table->dropIndex('idx_store_catalog_office_published');
        });
    }
};
