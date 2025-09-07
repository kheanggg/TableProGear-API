<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            ['name' => 'New', 'description' => 'New Product'],
            ['name' => 'Hot', 'description' => 'Hot Prodcut'],
            ['name' => 'Collection', 'description' => 'Collection Product'],
        ];

        DB::table('tags')->insert($tags);
    }
}
