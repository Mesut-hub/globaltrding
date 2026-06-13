<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class IndustriesIndexPageSeeder extends Seeder
{
    public function run(): void
    {
        Page::firstOrCreate(
            ['slug' => 'industries'],
            [
                'title'        => ['en' => 'Industries', 'tr' => 'Endüstriler', 'ar' => 'الصناعات', 'fr' => 'Industries'],
                'content'      => ['en' => ''],
                'is_published' => true,
                'show_in_company'     => false,
                'show_in_products'    => false,
                'show_in_information' => false,
                'show_in_service'     => false,
                'blocks' => [
                    [
                        'type' => 'industries_grid',
                        'data' => [],
                    ],
                ],
            ]
        );
    }
}