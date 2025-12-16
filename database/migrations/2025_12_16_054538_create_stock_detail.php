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
        Schema::create('tb_stock', function (Blueprint $table) {
            $table->bigIncrements('stk_id');

            $table->unsignedBigInteger('part_id');
            $table->string('stk_lokasi');          

            $table->integer('stk_qty')->default(0);
            $table->integer('stk_min')->default(0);
            $table->integer('stk_max')->default(0);

            $table->timestamps();

            $table->foreign('part_id')->references('part_id')->on('tb_barang');

            $table->unique(['part_id', 'stk_lokasi']);  
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_detail');
    }
};
