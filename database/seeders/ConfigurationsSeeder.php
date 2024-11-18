<?php

namespace Database\Seeders;

use App\Models\Configuration;
use App\Models\SystemConfiguration;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ConfigurationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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
            'value' => 'smtp'
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
            'value' => 'smtp.gmail.com'
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
            'value' => 465
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
            'value' => 'developer.ecuador@gmail.com'
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
            'value' => 'qpurcuhlmvnwwqtf'
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
            'name' => 'GRUPO_DEFECTO',
            'description' => 'Grupo por defecto para objeto',
            'default' => 9,
            'type' => 7,
            'custom_by_user' => false
        ]);
        $config->save();

        $config = Configuration::create([
            'name' => 'DEBUG',
            'description' => 'Permite mostrar elementos del sistema ocultos para depurar el app.',
            'default' => false,
            'type' => 13,
            'custom_by_user' => false
        ]);
        $config->save();

        $system_configs = [
            ['configuration' => 1, 'value' => 'smtp'],
            ['configuration' => 2, 'value' => 'smtp.gmail.com'],
            ['configuration' => 3, 'value' => '465'],
            ['configuration' => 4, 'value' => 'developer.ecuador@gmail.com'],
            ['configuration' => 5, 'value' => 'qpurcuhlmvnwwqtf'],
            ['configuration' => 6, 'value' => 'ssl'],
        ];

        foreach ($system_configs as $config){
            SystemConfiguration::query()->create($config);
        }
    }
}
