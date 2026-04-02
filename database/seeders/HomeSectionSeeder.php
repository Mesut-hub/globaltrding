<?php

namespace Database\Seeders;

use App\Models\HomeSection;
use Illuminate\Database\Seeder;

class HomeSectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            [
                'key' => 'hero',
                'sort_order' => 10,
                'is_active' => true,
                'blocks' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'media_type' => 'video',
                            'media_path' => null, // upload in Filament
                            'title' => [
                                'en' => 'Global Industrial Equipment & Raw Materials Supplier',
                                'tr' => 'Küresel Endüstriyel Ekipman ve Hammadde Tedarikçisi',
                                'ar' => 'مورد عالمي للمعدات الصناعية والمواد الخام',
                                'fr' => 'Fournisseur mondial d’équipements industriels et de matières premières',
                            ],
                            'subtitle' => [
                                'en' => 'Supporting Oil & Gas, Petrochemical, Refinery, and Chemical industries with trusted brands and fast sourcing.',
                            ],
                            'cta1_label' => ['en' => 'Product Finder'],
                            'cta1_url' => '/{locale}/products',
                            'cta2_label' => ['en' => 'Collaboration Form'],
                            'cta2_url' => '/{locale}/collaboration',
                            'min_h' => '90vh',
                            'text_offset_px' => 290,
                            'overlay_top' => 0.45,
                            'overlay_mid' => 0.15,
                            'overlay_bottom' => 0.55,
                        ],
                    ],
                ],
            ],
            [
                'key' => 'market_belt',
                'sort_order' => 20,
                'is_active' => true,
                'blocks' => [
                    [
                        'type' => 'market_belt',
                        'data' => [
                            'enabled' => true,
                        ],
                    ],
                ],
            ],
            [
                'key' => 'industries',
                'sort_order' => 30,
                'is_active' => true,
                'blocks' => [
                    [
                        'type' => 'industries_slider',
                        'data' => [
                            'title' => ['en' => 'Industries'],
                            'view_all_url' => '/{locale}/industries',
                        ],
                    ],
                ],
            ],
            [
                'key' => 'insights',
                'sort_order' => 40,
                'is_active' => true,
                'blocks' => [
                    [
                        'type' => 'cards',
                        'data' => [
                            'title' => ['en' => 'Company insights'],
                            'items' => [
                                [
                                    'image_path' => null,
                                    'title' => ['en' => 'Supply chain insights'],
                                    'text' => ['en' => 'Updates and perspectives from our sourcing network.'],
                                    'url' => '/{locale}/news',
                                ],
                                [
                                    'image_path' => null,
                                    'title' => ['en' => 'Quality & compliance'],
                                    'text' => ['en' => 'How we validate brands, documentation, and delivery.'],
                                    'url' => '/{locale}/pages/who-we-are',
                                ],
                                [
                                    'image_path' => null,
                                    'title' => ['en' => 'Project procurement'],
                                    'text' => ['en' => 'Support for urgent spare parts and planned shutdowns.'],
                                    'url' => '/{locale}/products',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'key' => 'trending',
                'sort_order' => 50,
                'is_active' => true,
                'blocks' => [
                    [
                        'type' => 'trending_topics',
                        'data' => [
                            'title' => ['en' => 'Trending topics'],
                            'topics' => [
                                ['label' => ['en' => 'Valves'], 'url' => '/{locale}/products'],
                                ['label' => ['en' => 'Actuators'], 'url' => '/{locale}/products'],
                                ['label' => ['en' => 'Instrumentation'], 'url' => '/{locale}/products'],
                                ['label' => ['en' => 'Spare parts'], 'url' => '/{locale}/products'],
                                ['label' => ['en' => 'Shutdowns'], 'url' => '/{locale}/news'],
                                ['label' => ['en' => 'Petrochemical'], 'url' => '/{locale}/industries'],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'key' => 'sustainability',
                'sort_order' => 60,
                'is_active' => true,
                'blocks' => [
                    [
                        'type' => 'cta',
                        'data' => [
                            'title' => ['en' => 'Sustainability and responsible sourcing'],
                            'text' => ['en' => 'We support transparent documentation, compliance, and safe logistics across your procurement pipeline.'],
                            'button_label' => ['en' => 'Find out more'],
                            'button_url' => '/{locale}/pages/who-we-are',
                        ],
                    ],
                ],
            ],
            [
                'key' => 'people',
                'sort_order' => 70,
                'is_active' => true,
                'blocks' => [
                    [
                        'type' => 'cards',
                        'data' => [
                            'title' => ['en' => 'People'],
                            'items' => [
                                [
                                    'image_path' => null,
                                    'title' => ['en' => 'Fast sourcing team'],
                                    'text' => ['en' => 'We respond quickly to urgent RFQs and planned procurement.'],
                                    'url' => '/{locale}/collaboration',
                                ],
                                [
                                    'image_path' => null,
                                    'title' => ['en' => 'Technical support'],
                                    'text' => ['en' => 'We help match specifications and equivalents.'],
                                    'url' => '/{locale}/products',
                                ],
                                [
                                    'image_path' => null,
                                    'title' => ['en' => 'Logistics coordination'],
                                    'text' => ['en' => 'Clear communication from order to delivery.'],
                                    'url' => '/{locale}/pages/who-we-are',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($sections as $s) {
            HomeSection::updateOrCreate(
                ['key' => $s['key']],
                [
                    'sort_order' => $s['sort_order'],
                    'is_active' => $s['is_active'],
                    'blocks' => $s['blocks'],
                ]
            );
        }
    }
}