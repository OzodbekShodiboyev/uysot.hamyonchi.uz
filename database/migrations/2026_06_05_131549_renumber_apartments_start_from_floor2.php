<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $blockIds = DB::table('apartments')
            ->select('block_id')
            ->distinct()
            ->pluck('block_id');

        foreach ($blockIds as $blockId) {
            $minNum = (int) DB::table('apartments')
                ->where('block_id', $blockId)
                ->selectRaw('MIN(CAST(number AS UNSIGNED)) as min_num')
                ->value('min_num');

            if ($minNum <= 1) continue;

            $offset = $minNum - 1;

            // 1,000,000 buffer qo'shib unique constraint xatosidan saqlaymiz
            DB::statement(
                "UPDATE apartments SET number = CAST(CAST(number AS UNSIGNED) + 1000000 AS CHAR) WHERE block_id = ?",
                [$blockId]
            );

            // Keyin to'g'ri raqamga o'tkazamiz
            $total = 1000000 + $offset;
            DB::statement(
                "UPDATE apartments SET number = CAST(CAST(number AS UNSIGNED) - {$total} AS CHAR) WHERE block_id = ?",
                [$blockId]
            );
        }
    }

    public function down(): void
    {
        $blockIds = DB::table('apartments')
            ->select('block_id')
            ->distinct()
            ->pluck('block_id');

        foreach ($blockIds as $blockId) {
            $aptsOnFloor2 = DB::table('apartments')
                ->where('block_id', $blockId)
                ->where('floor', 2)
                ->count();

            if ($aptsOnFloor2 <= 0) continue;

            DB::statement(
                "UPDATE apartments SET number = CAST(CAST(number AS UNSIGNED) + 1000000 AS CHAR) WHERE block_id = ?",
                [$blockId]
            );

            $total = 1000000 - $aptsOnFloor2;
            DB::statement(
                "UPDATE apartments SET number = CAST(CAST(number AS UNSIGNED) - {$total} AS CHAR) WHERE block_id = ?",
                [$blockId]
            );
        }
    }
};
