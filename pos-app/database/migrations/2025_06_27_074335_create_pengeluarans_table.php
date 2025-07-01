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
        Schema::create('pengeluarans', function (Blueprint $table) {
            $table->id();
            $table->enum('tipe', ['beli_bahan_baku', 'operasional', 'lainnya']);
            $table->foreignId('bahan_baku_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('jumlah')->nullable();
            $table->decimal('harga', 12, 2);
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
        Schema::dropIfExists('pengeluarans');
    }
};
