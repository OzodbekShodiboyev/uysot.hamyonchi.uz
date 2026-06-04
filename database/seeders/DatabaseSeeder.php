<?php

namespace Database\Seeders;

use App\Models\{Apartment, Block, Owner, User};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Foydalanuvchilar ─────────────────────────────
        User::create([
            'name'     => 'Admin',
            'email'    => 'admin@uysotish.uz',
            'phone'    => '+998 90 000 00 00',
            'password' => Hash::make('Admin1234!'),
            'role'     => 'admin',
        ]);

        User::create([
            'name'     => 'Sardor Nazarov',
            'email'    => 'sardor@uysotish.uz',
            'phone'    => '+998 90 111 11 11',
            'password' => Hash::make('Manager123!'),
            'role'     => 'manager',
        ]);

        User::create([
            'name'     => 'Dilnoza Yusupova',
            'email'    => 'dilnoza@uysotish.uz',
            'phone'    => '+998 91 222 22 22',
            'password' => Hash::make('Manager123!'),
            'role'     => 'manager',
        ]);

        User::create([
            'name'     => 'Zafar Toshmatov',
            'email'    => 'zafar@uysotish.uz',
            'phone'    => '+998 93 333 33 33',
            'password' => Hash::make('Accountant123!'),
            'role'     => 'accountant',
        ]);

        // ─── Bloklar ──────────────────────────────────────
        $blocksData = [
            [
                'name'         => '1-blok',
                'code'         => 'B1',
                'color'        => '#1D9E75',
                'total_floors' => 9,
                'sort_order'   => 1,
                'address'      => 'Chilonzor tumani, 19-mavze',
            ],
            [
                'name'         => '2-blok',
                'code'         => 'B2',
                'color'        => '#185FA5',
                'total_floors' => 12,
                'sort_order'   => 2,
                'address'      => 'Chilonzor tumani, 19-mavze',
            ],
            [
                'name'         => '3-blok',
                'code'         => 'B3',
                'color'        => '#BA7517',
                'total_floors' => 9,
                'sort_order'   => 3,
                'address'      => 'Chilonzor tumani, 19-mavze',
            ],
        ];

        foreach ($blocksData as $bData) {
            $block = Block::create($bData);

            // Xonadon raqami bazaviy (masalan B1: 1001, 1002...)
            $baseNum = ($block->sort_order - 1) * 1000;

            for ($floor = 1; $floor <= $block->total_floors; $floor++) {
                for ($i = 0; $i < 4; $i++) {
                    $rooms   = [1, 2, 2, 3][$i];
                    $area    = round($rooms * 20 + rand(4, 14) + ($i * 2.5), 2);
                    $aptNum  = $baseNum + ($floor * 10) + ($i + 1);

                    $owner = Owner::create([
                        'full_name'       => $this->fakeName(),
                        'phone'           => '+998 9' . rand(0, 4) . ' ' . rand(100, 999) . ' ' . rand(10, 99) . ' ' . rand(10, 99),
                        'passport_series' => strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 2)) . rand(1000000, 9999999),
                    ]);

                    Apartment::create([
                        'block_id'    => $block->id,
                        'owner_id'    => $owner->id,
                        'number'      => (string) $aptNum,
                        'floor'       => $floor,
                        'entrance'    => $i < 2 ? 1 : 2,
                        'rooms'       => $rooms,
                        'area_total'  => $area,
                        'area_living' => round($area * 0.65, 2),
                        'area_kitchen'=> round($area * 0.15, 2),
                        'renovation'  => ['none', 'none', 'rough', 'full'][rand(0, 3)],
                        'price_per_m2'=> 650,
                        'total_price' => round($area * 650, 2),
                        'status'      => $this->randomStatus(),
                    ]);
                }
            }
        }
    }

    private function randomStatus(): string
    {
        // Taxminan: 50% bo'sh, 15% band, 10% bron, 25% sotilgan
        $statuses = [
            'free', 'free', 'free', 'free', 'free',
            'reserved', 'reserved',
            'booked',
            'sold', 'sold', 'sold',
        ];
        return $statuses[array_rand($statuses)];
    }

    private function fakeName(): string
    {
        $last  = ['Karimov', 'Rahimov', 'Umarov', 'Toshmatov', 'Nazarov',
                  'Yusupov', 'Mirzayev', 'Ergashev', 'Holmatov', 'Qodirov'];
        $first = ['Jasur', 'Sherzod', 'Bobur', 'Rustam', 'Sardor',
                  'Dilnoza', 'Sarvinoz', 'Gulnora', 'Malika', 'Barno'];
        return $last[array_rand($last)] . ' ' . $first[array_rand($first)];
    }
}
