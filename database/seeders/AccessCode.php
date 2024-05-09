<?php

namespace Database\Seeders;

use App\Models\ObjectType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AccessCode extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $object_types = ObjectType::query()->get();
        foreach ($object_types as $ot){
            $ot->access_code= generateRandomString(32);
            $ot->save();
        }
    }
}
