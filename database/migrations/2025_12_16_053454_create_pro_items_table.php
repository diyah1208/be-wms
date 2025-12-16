<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dtl_purchase_order', function (Blueprint $table) {
            $table->bigIncrements('dtl_po_id');

            $table->unsignedBigInteger('po_id');
            $table->unsignedBigInteger('mr_id');

            $table->string('dtl_po_part_number');
            $table->string('dtl_po_part_name');
            $table->string('dtl_po_satuan');
            $table->integer('dtl_po_qty');

            $table->timestamps();

            $table->foreign('po_id')->references('po_id')->on('tb_purchase_order')->onDelete('cascade');
            $table->foreign('mr_id')->references('mr_id')->on('tb_material_request');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pro_items');
    }
};
