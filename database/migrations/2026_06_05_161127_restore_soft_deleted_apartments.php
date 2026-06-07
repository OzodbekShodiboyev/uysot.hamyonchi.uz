<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tasodifan o'chirilgan (soft-deleted) xonadonlarni tiklaydi.
 * Faqat shartnomasi bo'lmagan, status='free' bo'lgan o'chirilgan xonadonlar tiklanadi.
 */
return new class extends Migration
{
    public function up(): void
    {
        $restored = DB::table('apartments')
            ->whereNotNull('deleted_at')
            ->whereNotExists(function ($q) {
                $q->from('contracts')->whereColumn('contracts.apartment_id', 'apartments.id');
            })
            ->update(['deleted_at' => null, 'status' => 'free']);

        if ($restored > 0) {
            \Illuminate\Support\Facades\Log::info("Restore migration: {$restored} ta soft-deleted xonadon tiklandi.");
        }
    }

    public function down(): void
    {
        // Teskari yo'nalish kerak emas
    }
};
