<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * firstFloor >= 2 bo'lgan bloklardagi (noturar 1-etajli binolar) xonadon
 * raqam bo'shliqlarini to'g'irlaydi. Joriy tartibni saqlab qayta raqamlaydi.
 *
 * Formula: (qavat - birinchi_qavat) × max_qavatdagi_soni + pozitsiya
 *
 * Misol: 7-BLOK (firstFloor=2, 5 apt/floor)
 *   floor 2 joriy [3,4,5,6,7] → yangi [1,2,3,4,5]
 *   floor 3 joriy [8,9,10,11,12] → yangi [6,7,8,9,10]
 */
return new class extends Migration
{
    public function up(): void
    {
        $blockIds = DB::table('blocks')->pluck('id');

        foreach ($blockIds as $blockId) {
            $floors = DB::table('apartments')
                ->where('block_id', $blockId)
                ->whereNull('deleted_at')
                ->select('floor')
                ->distinct()
                ->orderByRaw('CAST(floor AS UNSIGNED)')
                ->pluck('floor');

            if ($floors->isEmpty()) continue;

            $firstFloor = (int) $floors->first();

            // Faqat 2-qavatdan boshlanadigan (noturar 1-etajli) binolar
            if ($firstFloor < 2) continue;

            // Eng ko'p xonadoni bo'lgan qavatdagi sonni asos qilamiz
            // Note: ->value('cnt') ishlatmaymiz — u floor qiymatini qaytarishi mumkin
            $aptsPerFloor = (int) DB::table('apartments')
                ->where('block_id', $blockId)
                ->whereNull('deleted_at')
                ->select('floor', DB::raw('COUNT(*) as cnt'))
                ->groupBy('floor')
                ->get()
                ->max('cnt');

            if ($aptsPerFloor === 0) continue;

            // Faqat shu blokni normalizatsiya qilamiz (boshqa bloklarga tegmaymiz)
            // 9_000_000 + id — globally unique, conflict imkonsiz
            DB::statement(
                "UPDATE apartments SET number = CAST(9000000 + id AS CHAR) WHERE block_id = ? AND deleted_at IS NULL",
                [$blockId]
            );
            DB::statement(
                "UPDATE apartments SET number = CAST(8000000 + id AS CHAR) WHERE block_id = ? AND deleted_at IS NOT NULL",
                [$blockId]
            );

            // Har qavatda joriy tartibni (raqamlar o'sish tartibida) saqlab to'g'ri raqam beramiz
            foreach ($floors as $floor) {
                $aptIds = DB::table('apartments')
                    ->where('block_id', $blockId)
                    ->where('floor', $floor)
                    ->whereNull('deleted_at')
                    ->orderByRaw('CAST(number AS UNSIGNED)')
                    ->pluck('id');

                foreach ($aptIds as $pos => $aptId) {
                    $newNum = ($floor - $firstFloor) * $aptsPerFloor + ($pos + 1);
                    DB::table('apartments')
                        ->where('id', $aptId)
                        ->update(['number' => (string) $newNum]);
                }
            }
        }
    }

    public function down(): void
    {
        // Bu migratsiya qaytarib bo'lmaydi.
        // Orqaga qaytarish uchun ma'lumotlar bazasi backupidan foydalaning.
    }
};
