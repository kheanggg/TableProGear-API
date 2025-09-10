<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Table', 'description' => 'It\'s a Table'],
            ['name' => 'Chair', 'description' => 'It\'s a Chair'],
            ['name' => 'Accessories', 'description' => 'Accessory items'],
            ['name' => 'Collections', 'description' => 'Collection items'],
        ];

        DB::table('categories')->insert($categories);
    }
}
