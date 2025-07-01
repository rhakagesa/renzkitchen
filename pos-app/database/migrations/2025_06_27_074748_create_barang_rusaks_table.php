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
        Schema::create('barang_rusaks', function (Blueprint $table) {
            $table->id();
            $table->enum('tipe', ['produk', 'bahan_baku']);
            $table->foreignId('produk_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('bahan_baku_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('jumlah');
            $table->decimal('nilai_kerugian', 12, 2);
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
        Schema::dropIfExists('barang_rusaks');
    }
};
