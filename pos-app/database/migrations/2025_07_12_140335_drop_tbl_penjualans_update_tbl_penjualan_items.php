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
        Schema::table('penjualan_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('penjualan_id');
        });
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('penjualans');
        Schema::enableForeignKeyConstraints();   
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
