<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apartments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('block_id')->constrained()->restrictOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained()->nullOnDelete();
            $table->string('number', 20);           // "101", "215"
            $table->unsignedSmallInteger('floor');
            $table->unsignedSmallInteger('entrance')->default(1);
            $table->unsignedTinyInteger('rooms');
            $table->decimal('area_total', 8, 2);
            $table->decimal('area_living', 8, 2)->nullable();
            $table->decimal('area_kitchen', 8, 2)->nullable();
            $table->enum('renovation', ['none', 'rough', 'full'])->default('none');
            $table->decimal('price_per_m2', 12, 2)->nullable();
            $table->decimal('total_price', 15, 2);
            $table->enum('status', [
                'free',
                'reserved',     // vaqtinchalik band (24 soat)
                'booked',       // shartnoma loyiha holatida
                'sold',         // sotilgan / shartnoma faol
                'unavailable',  // sotilmaydi
            ])->default('free');
            $table->timestamp('reserved_until')->nullable();
            $table->foreignId('reserved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('floor_plan_image')->nullable();
            $table->json('floor_plan_data')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['block_id', 'number']);
            $table->index(['block_id', 'status']);
            $table->index(['status', 'floor']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apartments');
    }
};
