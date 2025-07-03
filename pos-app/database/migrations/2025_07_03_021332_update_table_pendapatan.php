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
        //
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('pendapatans');
        Schema::enableForeignKeyConstraints();
        Schema::create('pendapatans', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->index();
            $table->enum('tipe', ['penjualan', 'lainnya']);
            $table->decimal('total', 12, 2);
            $table->integer('diskon')->default(0);
            $table->integer('pajak')->default(0);
            $table->decimal('grand_total', 12, 2);
            $table->text('keterangan')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });   
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
