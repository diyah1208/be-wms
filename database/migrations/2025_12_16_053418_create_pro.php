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
        Schema::create('tb_purchase_order', function (Blueprint $table) {
            $table->bigIncrements('po_id');

            $table->string('po_kode')->unique();
            $table->unsignedBigInteger('pr_id');
            $table->date('po_tanggal');
            $table->date('po_estimasi')->nullable();
            $table->text('po_keterangan')->nullable();
            $table->string('po_status')->default('open');

            $table->timestamps();

            $table->foreign('pr_id')->references('pr_id')->on('tb_purchase_request');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pro');
    }
};
