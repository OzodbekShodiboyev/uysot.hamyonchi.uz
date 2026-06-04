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
            // 75-100% boshlang'ich to'lov uchun narxlar (chegirmali)
            $table->decimal('price_karobka_full', 15, 2)->nullable()->after('price_podklyuch');
            $table->decimal('price_podklyuch_full', 15, 2)->nullable()->after('price_karobka_full');
        });
    }

    public function down(): void
    {
        Schema::table('apartments', function (Blueprint $table) {
            $table->dropColumn(['price_karobka_full', 'price_podklyuch_full']);
        });
    }
};
