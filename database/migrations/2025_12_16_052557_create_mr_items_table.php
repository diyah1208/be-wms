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
        Schema::create('dtl_material_request', function (Blueprint $table) {
            $table->bigIncrements('dtl_mr_id');

            $table->unsignedBigInteger('mr_id');

            $table->string('dtl_mr_part_number');
            $table->string('dtl_mr_part_name');
            $table->string('dtl_mr_satuan');
            $table->string('dtl_mr_prioritas');           
            $table->integer('dtl_mr_qty_request');
            $table->integer('dtl_mr_qty_received')->default(0);

            $table->timestamps();

            $table->foreign('mr_id')->references('mr_id')->on('tb_material_request')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mr_items');
    }
};
