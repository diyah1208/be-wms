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
        Schema::create('tb_purchase_request', function (Blueprint $table) {
            $table->bigIncrements('pr_id');

            $table->string('pr_kode')->unique();
            $table->string('pr_lokasi');
            $table->unsignedBigInteger('pr_pic_id');
            $table->date('pr_tanggal');
            $table->string('pr_status')->default('open');

            $table->timestamps();

            $table->foreign('pr_pic_id')->references('id')->on('users');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pr');
    }
};
