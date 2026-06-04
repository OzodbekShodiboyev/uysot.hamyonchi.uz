<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blocks', function (Blueprint $table) {
            $table->id();
            $table->string('name');                // "1-blok", "A blok"
            $table->string('code', 10)->unique();  // "B1","B2","B3" — shartnoma raqamida
            $table->string('address')->nullable();
            $table->unsignedSmallInteger('total_floors')->default(1);
            $table->string('color', 7)->default('#1D9E75');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blocks');
    }
};
