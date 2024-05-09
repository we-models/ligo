<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfigTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('data_type')->insert([
            [ 'name' => 'String' ],
            [ 'name' => 'Text' ],
            [ 'name' => 'Html' ],
            [ 'name' => 'Email' ],
            [ 'name' => 'Phone' ],
            [ 'name' => 'Color' ],
            [ 'name' => 'Integer' ],
            [ 'name' => 'Decimal' ],
            [ 'name' => 'Double' ],
            [ 'name' => 'Date' ],
            [ 'name' => 'Time' ],
            [ 'name' => 'DateTime' ],
            [ 'name' => 'Boolean' ],
            [ 'name' => 'Image' ],
            [ 'name' => 'File' ],
            [ 'name' => 'Array' ],
            [ 'name' => 'Json' ],
            [ 'name' => 'Link' ],
            [ 'name' => 'Select' ],
        ]);
    }
}
