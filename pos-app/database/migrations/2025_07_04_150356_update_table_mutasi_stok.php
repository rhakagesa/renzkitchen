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
        Schema::dropIfExists('mutasi_stoks');
        Schema::enableForeignKeyConstraints();
        Schema::create('mutasi_stoks', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->foreignId('produk_id')->constrained()->onDelete('cascade');
            $table->text('bahan_baku');
            $table->integer('jumlah_produk');
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
