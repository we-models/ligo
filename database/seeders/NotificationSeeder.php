<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Configuration;
use App\Models\Group;
use App\Models\NewPermission;
use App\Models\NewRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationSeeder extends Seeder
{

    public function generatePermission(array $data, $group, $role){
        foreach ($data as $permission){
            $show = isset($permission['show']) ?? false;
            $permission = NewPermission::query()->create([
                'name' => $permission['name'],
                'identifier' => $permission['identifier'],
                'guard_name' => 'web',
                'detail' => '',
                'show_in_menu' => $show
            ]);
            $permission->save();
            $role->givePermissionTo($permission->name);
            if($group != null){
                DB::table('model_has_group')->insert([
                    'model_id' => $permission->id,
                    'model_type' =>NewPermission::class,
                    'group' => $group
                ]);
            }
        }
    }
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $group = Group::query()->where('name' , 'Usuarios')->first();
        $role  = NewRole::query()->where('name' ,  'Developer')->first();

        $this->generatePermission([
            ['name' => 'notification_type.index',            'identifier' => 'Tipo de notificación', 'show' => true],
            ['name' => 'notification_type.all',              'identifier' => 'Obtener tipos de notificaciones'],
            ['name' => 'notification_type.create',           'identifier' => 'Crear tipo de notificaciones'],
            ['name' => 'notification_type.store',            'identifier' => 'Guardar tipo de notificaciones'],
            ['name' => 'notification_type.show',             'identifier' => 'Mostrar tipo de notificaciones'],
            ['name' => 'notification_type.edit',             'identifier' => 'Editar tipo de notificaciones'],
            ['name' => 'notification_type.update',           'identifier' => 'Actualizar tipo de notificaciones'],
            ['name' => 'notification_type.destroy',          'identifier' => 'Eliminar tipo de notificaciones'],
            ['name' => 'notification_type.details',          'identifier' => 'Detalle de tipo de notificaciones'],
            ['name' => 'notification_type.logs',             'identifier' => 'Obtener log de tipo de notificaciones'],
        ], $group->id, $role);

        $this->generatePermission([
            ['name' => 'notification.index',            'identifier' => 'Notificaciones masivas', 'show' => true],
            ['name' => 'notification.all',              'identifier' => 'Obtener notificaciones'],
            ['name' => 'notification.create',           'identifier' => 'Crear notificaciones masivas'],
            ['name' => 'notification.store',            'identifier' => 'Guardar notificaciones'],
            ['name' => 'notification.show',             'identifier' => 'Mostrar notificaciones'],
            ['name' => 'notification.details',          'identifier' => 'Detalle de notificaciones'],
            ['name' => 'notification.logs',             'identifier' => 'Obtener log de notificaciones'],
        ], $group->id, $role);

        $config = Configuration::create([
            'name' => 'GOOGLE_MAPS_API_KEY',
            'description' => 'El API Key que permita conectar con goggle maps',
            'default' => '',
            'type' => 1,
            'custom_by_user' => true
        ]);
        $config->save();

        $config = Configuration::create([
            'name' => 'GOOGLE_FIREBASE_API_KEY',
            'description' => 'El API Key que permita conectar con FCM',
            'default' => '',
            'type' => 1,
            'custom_by_user' => true
        ]);
        $config->save();

        $config = Configuration::create([
            'name' => 'GOOGLE_FIREBASE_AUTH_DOMAIN',
            'description' => 'El dominio creado por Firebase',
            'default' => '',
            'type' => 1,
            'custom_by_user' => true
        ]);
        $config->save();
        $config = Configuration::create([
            'name' => 'GOOGLE_FIREBASE_PROJECT_ID',
            'description' => 'El id del proyecto para Firebase',
            'default' => '',
            'type' => 1,
            'custom_by_user' => true
        ]);
        $config->save();
        $config = Configuration::create([
            'name' => 'GOOGLE_FIREBASE_STORAGE_BUCKET',
            'description' => 'El storgae de google Firebase',
            'default' => '',
            'type' => 1,
            'custom_by_user' => true
        ]);
        $config->save();

        $config = Configuration::create([
            'name' => 'GOOGLE_FIREBASE_MESSAGING_SENDER_ID',
            'description' => 'El ID del sender de google Firebase',
            'default' => '',
            'type' => 1,
            'custom_by_user' => true
        ]);
        $config->save();

        $config = Configuration::create([
            'name' => 'GOOGLE_FIREBASE_APP_ID',
            'description' => 'El APP ID del google Firebase',
            'default' => '',
            'type' => 1,
            'custom_by_user' => true
        ]);
        $config->save();

        $config = Configuration::create([
            'name' => 'GOOGLE_FIREBASE_MEASUREMENT_ID',
            'description' => 'El measurement ID del google Firebase',
            'default' => '',
            'type' => 1,
            'custom_by_user' => true
        ]);
        $config->save();

        $config = Configuration::create([
            'name' => 'GOOGLE_FIREBASE_ENABLE',
            'description' => 'Activar las notificaciones',
            'default' => '',
            'type' => 13,
            'custom_by_user' => true
        ]);
        $config->save();

        $config = Configuration::create([
            'name' => 'GOOGLE_FIREBASE_PUBLIC',
            'description' => 'Clave publica de firebase',
            'default' => '',
            'type' => 1,
            'custom_by_user' => true
        ]);
        $config->save();

        $config = Configuration::create([
            'name' => 'GOOGLE_FIREBASE_WEB',
            'description' => 'Clave Para notificaciones web',
            'default' => '',
            'type' => 1,
            'custom_by_user' => true
        ]);
        $config->save();
    }
}
