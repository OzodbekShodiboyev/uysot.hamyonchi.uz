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
        Schema::table('apartments', function (Blueprint $table) {
            // price_podklyuch: tayyor ta'mir narxi (ixtiyoriy)
            // total_price: karobka (ta'mirsiz) narxi bo'lib qoladi
            $table->decimal('price_podklyuch', 15, 2)->nullable()->after('total_price');
        });
    }

    public function down(): void
    {
        Schema::table('apartments', function (Blueprint $table) {
            $table->dropColumn('price_podklyuch');
        });
    }
};
