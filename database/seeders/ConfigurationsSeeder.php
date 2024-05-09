<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Configuration;
use App\Models\SystemConfiguration;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ConfigurationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $bs = Business::query()->first();
        $config = Configuration::create([
            'name' => 'MAIL_MAILER',
            'description' => 'El protocolo de Correo electrónico',
            'default' => 'smtp',
            'type' => 1,
            'custom_by_user' => true
        ]);
        $config->save();

        $sys_config = SystemConfiguration::create([
            'configuration' => $config->id,
            'value' => 'smtp',
            'business' => $bs->id,
        ]);
        $sys_config->save();

        $config = Configuration::create([
            'name' => 'MAIL_HOST',
            'description' => 'El servidor host de correo electrónico',
            'default' => 'smtp.gmail.com',
            'type' => 1,
            'custom_by_user' => true
        ]);
        $config->save();

        $sys_config = SystemConfiguration::create([
            'configuration' => $config->id,
            'value' => 'smtp.gmail.com',
            'business' => $bs->id,
        ]);
        $sys_config->save();

        $config = Configuration::create([
            'name' => 'MAIL_PORT',
            'description' => 'El puerto de correo electrónico',
            'default' => '465',
            'type' => 7,
            'custom_by_user' => true
        ]);
        $config->save();

        $sys_config = SystemConfiguration::create([
            'configuration' => $config->id,
            'value' => 465,
            'business' => $bs->id,
        ]);
        $sys_config->save();

        $config = Configuration::create([
            'name' => 'MAIL_USERNAME',
            'description' => 'Usuario de correo',
            'default' => null,
            'type' => 4,
            'custom_by_user' => true
        ]);
        $config->save();

        $sys_config = SystemConfiguration::create([
            'configuration' => $config->id,
            'value' => 'developer.ecuador@gmail.com',
            'business' => $bs->id,
        ]);
        $sys_config->save();

        $config = Configuration::create([
            'name' => 'MAIL_PASSWORD',
            'description' => 'Contraseña del correo',
            'default' => null,
            'type' => 1,
            'custom_by_user' => true
        ]);
        $config->save();

        $sys_config = SystemConfiguration::create([
            'configuration' => $config->id,
            'value' => 'qpurcuhlmvnwwqtf',
            'business' => $bs->id,
        ]);
        $sys_config->save();

        $config = Configuration::create([
            'name' => 'MAIL_ENCRYPTION',
            'description' => 'Formato de encriptación de datos',
            'default' => null,
            'type' => 1,
            'custom_by_user' => true
        ]);
        $config->save();


        $config = Configuration::create([
            'name' => 'WP_LINK',
            'description' => 'Conexión con WordPress',
            'default' => null,
            'type' => 18,
            'custom_by_user' => true
        ]);
        $config->save();

        $sys_config = SystemConfiguration::create([
            'configuration' => $config->id,
            'value' => 'ssl',
            'business' => $bs->id,
        ]);
        $sys_config->save();

        $system_configs = [
            ['configuration' => 1, 'value' => 'smtp'],
            ['configuration' => 2, 'value' => 'smtp.gmail.com'],
            ['configuration' => 3, 'value' => '465'],
            ['configuration' => 4, 'value' => 'developer.ecuador@gmail.com'],
            ['configuration' => 5, 'value' => 'qpurcuhlmvnwwqtf'],
            ['configuration' => 6, 'value' => 'ssl'],
        ];

        foreach ($system_configs as $config){
            $config[BUSINESS_IDENTIFY] = $bs->id;
            SystemConfiguration::query()->create($config);
        }
    }
}
