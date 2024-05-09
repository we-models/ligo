<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Group;
use App\Models\NewPermission;
use App\Models\NewRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FillChannelPermissions extends Seeder
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
    public int $current_user;

    public function addBusinessToObject($class, $object, $business){
        DB::table('model_has_business')->insert([
            'model_type' => $class,
            'model_id' => $object->id,
            'business' => $business->id
        ]);

        $this->fillUserManipulation($class, $object->id, 'created');
    }

    function fillUserManipulation($model, $model_id, $state){
        DB::table('user_manipulations')->insert([
            'model_type' => $model,
            'model_id' => $model_id,
            'type' => $state,
            'user' => $this->current_user,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
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
            ['name' => 'channel.index',            'identifier' => 'Canales', 'show' => true],
            ['name' => 'channel.all',              'identifier' => 'Obtener canales'],
            ['name' => 'channel.create',           'identifier' => 'Crear Canal'],
            ['name' => 'channel.store',            'identifier' => 'Guardar canal'],
            ['name' => 'channel.show',             'identifier' => 'Mostrar canal'],
            ['name' => 'channel.edit',             'identifier' => 'Editar canal'],
            ['name' => 'channel.update',           'identifier' => 'Actualizar canal'],
            ['name' => 'channel.destroy',          'identifier' => 'Eliminar canal'],
            ['name' => 'channel.details',          'identifier' => 'Detalle de canal'],
            ['name' => 'channel.logs',             'identifier' => 'Obtener log de canal'],
            ['name' => 'intermediary.assign_channel', 'identifier' => 'Asignar intermediario a canal', 'show' => true],
            ['name' => 'chat.index',            'identifier' => 'Chats', 'show' => true],
            ['name' => 'chat.all',              'identifier' => 'Obtener chats'],
            ['name' => 'chat.create',           'identifier' => 'Crear chat'],
            ['name' => 'chat.store',            'identifier' => 'Guardar chat'],
            ['name' => 'chat.show',             'identifier' => 'Mostrar chat'],
            ['name' => 'chat.edit',             'identifier' => 'Editar chat'],
            ['name' => 'chat.update',           'identifier' => 'Actualizar chat'],
            ['name' => 'chat.destroy',          'identifier' => 'Eliminar chat'],
            ['name' => 'chat.details',          'identifier' => 'Detalle de chat'],
            ['name' => 'chat.logs',             'identifier' => 'Obtener log de chat'],
        ], $group->id, $role);

    }
}
