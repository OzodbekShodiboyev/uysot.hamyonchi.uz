<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Mavjud 'booked' statusli xonadonlarni 'reserved' ga o'tkazish
        DB::table('apartments')
            ->where('status', 'booked')
            ->update([
                'status'         => 'reserved',
                'reserved_until' => null,
                'reserved_by'    => null,
            ]);

        // Enum dan 'booked' ni olib tashlash va 'price_podklyuch' ustun qo'shish
        DB::statement("
            ALTER TABLE apartments
            MODIFY COLUMN status ENUM('free','reserved','sold','unavailable') NOT NULL DEFAULT 'free'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE apartments
            MODIFY COLUMN status ENUM('free','reserved','booked','sold','unavailable') NOT NULL DEFAULT 'free'
        ");
    }
};
