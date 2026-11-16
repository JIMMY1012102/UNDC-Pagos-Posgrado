<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fail before DDL; never discard existing selections or order snapshots.
        if (DB::table('tienda_carritos')->where('status', 'active')->whereNotNull('user_id')
            ->groupBy('user_id')->havingRaw('COUNT(*) > 1')->exists()
            || DB::table('tienda_pedidos')->whereNotNull('cart_id')->groupBy('cart_id')->havingRaw('COUNT(*) > 1')->exists()) {
            throw new RuntimeException('Hay carritos activos o pedidos duplicados. Conciliar los registros antes de aplicar esta migración; no se eliminó ningún dato.');
        }
        // MariaDB forbids a stored expression over a SET NULL foreign key.
        // Keep ownership/history intact instead of silently anonymizing carts.
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            foreach (Schema::getForeignKeys('tienda_carritos') as $key) {
                if ($key['columns'] === ['user_id']) {
                    Schema::table('tienda_carritos', fn (Blueprint $table) => $table->dropForeign($key['name']));
                }
            }
            Schema::table('tienda_carritos', fn (Blueprint $table) => $table->foreign('user_id', 'fk_cart_owner_restrict')->references('id')->on('users')->restrictOnDelete());
        }
        Schema::table('tienda_carritos', function (Blueprint $table) {
            $table->unsignedBigInteger('active_user_id')->nullable()->storedAs("CASE WHEN status = 'active' THEN user_id ELSE NULL END");
            $table->unique('active_user_id', 'uq_cart_active_user');
        });
        Schema::table('tienda_pedidos', function (Blueprint $table) {
            $table->unique('cart_id', 'uq_order_cart');
            $table->index(['status', 'expires_at'], 'idx_order_expiry');
        });
    }

    public function down(): void
    {
        Schema::table('tienda_pedidos', function (Blueprint $table) {
            $table->dropIndex('idx_order_expiry');
            $table->dropUnique('uq_order_cart');
        });
        Schema::table('tienda_carritos', function (Blueprint $table) {
            $table->dropUnique('uq_cart_active_user');
            $table->dropColumn('active_user_id');
        });
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            Schema::table('tienda_carritos', function (Blueprint $table) {
                $table->dropForeign('fk_cart_owner_restrict');
                $table->foreign('user_id', 'store_carts_user_id_foreign')->references('id')->on('users')->nullOnDelete();
            });
        }
    }
};
