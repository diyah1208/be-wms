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
        Schema::create('dtl_receive_item', function (Blueprint $table) {
            $table->bigIncrements('dtl_ri_id');

            $table->unsignedBigInteger('ri_id');
            $table->unsignedBigInteger('po_id');
            $table->unsignedBigInteger('mr_id');
            $table->unsignedBigInteger('part_id');

            $table->string('dtl_ri_part_number');
            $table->string('dtl_ri_part_name');
            $table->string('dtl_ri_satuan');

            $table->integer('dtl_ri_qty');              

            $table->timestamps();

            $table->foreign('ri_id')->references('ri_id')->on('tb_receive_item')->onDelete('cascade');
            $table->foreign('po_id')->references('po_id')->on('tb_purchase_order');
            $table->foreign('mr_id')->references('mr_id')->on('tb_material_request');
            $table->foreign('part_id')->references('part_id')->on('tb_barang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receive_detail');
    }
};
