<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('mcq_users')->insert([
            ['username' => 'admin', 'password' => Hash::make('admin123'), 'name' => 'Oscar Admin', 'role' => 'ADMIN', 'created_at' => now(), 'updated_at' => now()],
            ['username' => 'usuario', 'password' => Hash::make('user123'), 'name' => 'Analista de Empaque', 'role' => 'USER', 'created_at' => now(), 'updated_at' => now()],
        ]);

        foreach (['SP26', 'FA26', 'SP27', 'FA27'] as $season) {
            DB::table('seasons')->insert(['code' => $season, 'created_at' => now(), 'updated_at' => now()]);
        }

        DB::table('brands')->insert([
            ['id' => 'patagonia', 'name' => 'Patagonia', 'color' => '#00539f', 'badge' => 'PTG', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 'the-north-face', 'name' => 'The North Face (TNF)', 'color' => '#d71920', 'badge' => 'TNF', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 'kuhl', 'name' => 'Kühl', 'color' => '#b45309', 'badge' => 'KÜHL', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 'engelbert-strauss', 'name' => 'Engelbert Strauss', 'color' => '#e31b23', 'badge' => 'e.s.', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('master_boxes')->insert([
            ['id' => 'box-ptg-1', 'brand_id' => 'patagonia', 'name' => 'Standard Patagonia Carton (23.0 x 15.0 x 17.5)', 'l' => 23.0, 'w' => 15.0, 'h' => 17.5, 'tare' => 800, 'max_kg' => 15.0, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 'box-tnf-1', 'brand_id' => 'the-north-face', 'name' => 'Master TNF Jacket Carton (26.0 x 18.0 x 22.0)', 'l' => 26.0, 'w' => 18.0, 'h' => 22.0, 'tare' => 1200, 'max_kg' => 16.0, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 'box-kuhl-1', 'brand_id' => 'kuhl', 'name' => 'Kühl Master Carton #K2 (24.0 x 16.0 x 16.5)', 'l' => 24.0, 'w' => 16.0, 'h' => 16.5, 'tare' => 880, 'max_kg' => 16.0, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 'box-es-1', 'brand_id' => 'engelbert-strauss', 'name' => 'Caja Standard e.s. (23.5 x 15.5 x 18.0)', 'l' => 23.5, 'w' => 15.5, 'h' => 18.0, 'tare' => 850, 'max_kg' => 16.0, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('mcq_tests')->insert([
            [
                'id' => 'MCQ-2026-101', 'season' => 'SP26', 'brand_id' => 'patagonia',
                'style_code' => "Men's Baggies Shorts 5\" (#57022)", 'category' => 'Pantalones / Trousers',
                'garment_l' => 11.0, 'garment_w' => 7.5, 'garment_h' => 0.9, 'compression_pct' => 0, 'lock_flat' => false,
                'unit_weight_grams' => 220, 'order_qty' => 4000, 'box_name' => 'Standard Patagonia Carton (23.0 x 15.0 x 17.5)',
                'box_l' => 23.0, 'box_w' => 15.0, 'box_h' => 17.5, 'box_tare_grams' => 800, 'max_weight_limit_kg' => 15.0,
                'created_by' => 'Oscar Admin', 'created_at' => now()->subHours(20), 'updated_at' => now()
            ],
            [
                'id' => 'MCQ-2026-102', 'season' => 'FA26', 'brand_id' => 'the-north-face',
                'style_code' => '1996 Retro Nuptse Jacket (#NF0A3C8D)', 'category' => 'Chaquetas / Jackets',
                'garment_l' => 18.5, 'garment_w' => 14.0, 'garment_h' => 2.8, 'compression_pct' => -10, 'lock_flat' => true,
                'unit_weight_grams' => 1150, 'order_qty' => 1500, 'box_name' => 'Master TNF Jacket Carton (26.0 x 18.0 x 22.0)',
                'box_l' => 26.0, 'box_w' => 18.0, 'box_h' => 22.0, 'box_tare_grams' => 1200, 'max_weight_limit_kg' => 16.0,
                'created_by' => 'Oscar Admin', 'created_at' => now()->subHours(12), 'updated_at' => now()
            ],
            [
                'id' => 'MCQ-2027-103', 'season' => 'SP27', 'brand_id' => 'kuhl',
                'style_code' => 'Kühl Rydr Pant (#5109)', 'category' => 'Pantalones / Trousers',
                'garment_l' => 15.5, 'garment_w' => 11.5, 'garment_h' => 1.4, 'compression_pct' => 0, 'lock_flat' => true,
                'unit_weight_grams' => 820, 'order_qty' => 2800, 'box_name' => 'Kühl Master Carton #K2 (24.0 x 16.0 x 16.5)',
                'box_l' => 24.0, 'box_w' => 16.0, 'box_h' => 16.5, 'box_tare_grams' => 880, 'max_weight_limit_kg' => 16.0,
                'created_by' => 'Analista de Empaque', 'created_at' => now()->subHours(5), 'updated_at' => now()
            ],
            [
                'id' => 'MCQ-2027-104', 'season' => 'FA27', 'brand_id' => 'engelbert-strauss',
                'style_code' => 'e.s. motion 2020 Work Trouser (#65321)', 'category' => 'Pantalones / Trousers',
                'garment_l' => 15.0, 'garment_w' => 11.0, 'garment_h' => 1.2, 'compression_pct' => 0, 'lock_flat' => false,
                'unit_weight_grams' => 780, 'order_qty' => 3000, 'box_name' => 'Caja Standard e.s. (23.5 x 15.5 x 18.0)',
                'box_l' => 23.5, 'box_w' => 15.5, 'box_h' => 18.0, 'box_tare_grams' => 850, 'max_weight_limit_kg' => 16.0,
                'created_by' => 'Oscar Admin', 'created_at' => now()->subHour(), 'updated_at' => now()
            ],
        ]);
    }
}