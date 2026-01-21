<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('dtl_purchase_order', function (Blueprint $table) {
            $table->decimal('dtl_po_harga', 18, 2)
                  ->nullable()
                  ->after('dtl_po_qty');

            $table->unsignedBigInteger('vendor_id')
                  ->nullable()
                  ->after('dtl_po_harga');
$table->foreign('vendor_id')
      ->references('id')     // 🔥 PK SEBENARNYA
      ->on('vendors')
      ->nullOnDelete();

        });
    }

    public function down(): void
    {
        Schema::table('dtl_purchase_order', function (Blueprint $table) {
            $table->dropForeign(['vendor_id']);
            $table->dropColumn(['dtl_po_harga', 'vendor_id']);
        });
    }
};
