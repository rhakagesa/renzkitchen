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
        Schema::create('pengeluaran_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengeluaran_id')->constrained()->onDelete('cascade');
            $table->integer('bahan_baku_id');
            $table->integer('jumlah');
            $table->integer('satuan');
            $table->decimal('harga_satuan', 10, 2);
            $table->decimal('total_harga', 10, 2);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_pengeluaran_details');
    }
};
