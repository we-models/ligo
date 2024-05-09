<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfigTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('data_type')->insert([
            ['id'=>1, 'name' => 'String' ],
            ['id'=>2, 'name' => 'Text' ],
            ['id'=>4, 'name' => 'Email' ],
            ['id'=>5, 'name' => 'Phone' ],
            ['id'=>6, 'name' => 'Color' ],
            ['id'=>7, 'name' => 'Integer' ],
            ['id'=>8, 'name' => 'Decimal' ],
            ['id'=>9, 'name' => 'Double' ],
            ['id'=>10, 'name' => 'Date' ],
            ['id'=>11, 'name' => 'Time' ],
            ['id'=>12, 'name' => 'DateTime' ],
            ['id'=>13, 'name' => 'Boolean' ],
            ['id'=>14, 'name' => 'Image' ],
            ['id'=>15, 'name' => 'File' ],
            ['id'=>18, 'name' => 'Link' ],
            ['id'=>19, 'name' => 'Select' ],
            ['id'=>20, 'name' => 'Map' ]
        ]);
    }
}
