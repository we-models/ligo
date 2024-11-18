<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\Icon;
use App\Models\NewPermission;
use App\Models\NewRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class FirstInstallationSeeder extends Seeder
{

    public int $current_user;
    /**
     * Run the database seeds.
     *
     * @return void
     */

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

    public function fillUserManipulation($model, $model_id){
       // $this->fillUserManipulation($class, $object->id, 'created');
        if(!is_numeric($model_id)){
            $model_id = $model_id->id;
        }
       DB::table('user_manipulations')->insert([
        'model_type' => $model,
        'model_id' => $model_id,
        'type' => 'created',
        'user' => $this->current_user,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Create de general Role for development
        $role  = NewRole::query()->create([
            'name' =>  'Developer',
            'guard_name' => 'web',
            'description' => 'Is the system developer',
        ]);
        $role->save();

        $role_general  = NewRole::query()->create([
            'name' =>  'General',
            'guard_name' => 'web',
            'description' => 'User general',
        ]);
        $role_general->save();

        //Create the first user for administration with password Abc123.....
        $user = User::query()->create([
            'name' => 'Admin', 'email' => 'admin@ligo.com', 'password' => Hash::make('2024WMAxlM'), 'code' => generateUserCode()
        ]);
        $user->save();

        echo "Current user ". $user->getAuthIdentifier() ." \n" ;
        $this->current_user = $user->getAuthIdentifier();


        $this->fillUserManipulation(User::class, $user->getAuthIdentifier());

        //Assign role to user
        $user->assignRole($role->name);
        $user->assignRole($role_general->name);

        $this->fillUserManipulation(NewRole::class, $role);


        $icon = Icon::query()->where('name','fas fa-home' )->first();
        $group = Group::query()->create(['name' => 'Home',  'icon' => $icon->id]);
        $group->save();
        $this->fillUserManipulation(Group::class, $group);
        $this->generatePermission([
            ['name' => 'home', 'identifier' => 'Home', 'show' => true],
            ['name' => 'icon.index',            'identifier' => 'Iconos', 'show' => true],
            ['name' => 'icon.all',              'identifier' => 'Obtener iconos'],
            ['name' => 'icon.create',           'identifier' => 'Crear icono'],
            ['name' => 'icon.store',            'identifier' => 'Guardar icono'],
            ['name' => 'icon.show',             'identifier' => 'Mostrar icono'],
            ['name' => 'icon.edit',             'identifier' => 'Editar icono'],
            ['name' => 'icon.update',           'identifier' => 'Actualizar icono'],
            ['name' => 'icon.destroy',          'identifier' => 'Eliminar icono'],
            ['name' => 'icon.details',          'identifier' => 'Detalle de icono'],
            ['name' => 'icon.logs',             'identifier' => 'Obtener log de icono'],
        ], $group->id, $role);


        $icon = Icon::query()->where('name','fab fa-buromobelexperte' )->first();
        $group = Group::query()->create(['name' => 'Roles', 'icon' => $icon->id]);
        $group->save();
        $this->fillUserManipulation(Group::class, $group);
        $this->generatePermission([
            ['name' => 'role.index',            'identifier' => 'Roles', 'show' => true],
            ['name' => 'role.all',              'identifier' => 'Obtener roles'],
            ['name' => 'role.create',           'identifier' => 'Crear rol'],
            ['name' => 'role.store',            'identifier' => 'Guardar roles'],
            ['name' => 'role.show',             'identifier' => 'Mostrar roles'],
            ['name' => 'role.edit',             'identifier' => 'Editar roles'],
            ['name' => 'role.update',           'identifier' => 'Actualizar roles'],
            ['name' => 'role.destroy',          'identifier' => 'Eliminar roles'],
            ['name' => 'role.details',          'identifier' => 'Detalles de roles'],
            ['name' => 'role.logs',             'identifier' => 'Obtener log de roles']
        ], $group->id, $role );


        $icon = Icon::query()->where('name','fas fa-newspaper' )->first();
        $group = Group::query()->create(['name' => 'Permisos', 'icon' => $icon->id]);
        $group->save();
        $this->fillUserManipulation(Group::class, $group);
        $this->generatePermission([
            ['name' => 'permission.index',        'identifier' => 'Permisos', 'show' => true],
            ['name' => 'permission.all',          'identifier' => 'Obtener permisos'],
            ['name' => 'permission.create',       'identifier' => 'Crear permisos'],
            ['name' => 'permission.store',        'identifier' => 'Guardar permisos'],
            ['name' => 'permission.show',         'identifier' => 'Mostrar permisos'],
            ['name' => 'permission.edit',         'identifier' => 'Editar permisos'],
            ['name' => 'permission.update',       'identifier' => 'Actualizar permisos'],
            ['name' => 'permission.destroy',      'identifier' => 'Eliminar permisos'],
            ['name' => 'permission.details',      'identifier' => 'Detalle de permiso'],
            ['name' => 'permission.logs',         'identifier' => 'Obtener log de permisos'],
            ['name' => 'permission.assign_role',  'identifier' => 'Asignar permisos a roles', 'show' => true],
            ['name' => 'permission.assign_group', 'identifier' => 'Asignar permisos a grupos', 'show' => true]
        ], $group->id, $role );

        $this->generatePermission([
            ['name' => 'assign.objects',  'identifier' => 'Asignar objetos'],
            ['name' => 'assign.save',     'identifier' => 'Guardar asignaciones'],
        ], null, $role);


        $icon = Icon::query()->where('name','fas fa-object-group' )->first();
        $group = Group::query()->create(['name' => 'Grupos', 'icon' => $icon->id]);
        $group->save();
        $this->fillUserManipulation(Group::class, $group);
        $this->generatePermission([
            ['name' => 'group.index',           'identifier' => 'Grupos', 'show' => true],
            ['name' => 'group.all',             'identifier' => 'Obtener grupos'],
            ['name' => 'group.create',          'identifier' => 'Crear grupos'],
            ['name' => 'group.store',           'identifier' => 'Guardar grupos'],
            ['name' => 'group.show',            'identifier' => 'Mostrar grupos'],
            ['name' => 'group.edit',            'identifier' => 'Editar grupos'],
            ['name' => 'group.update',          'identifier' => 'Actualizar grupos'],
            ['name' => 'group.destroy',         'identifier' => 'Eliminar grupos'],
            ['name' => 'group.details', 'identifier' => 'Detalle de grupo'],
            ['name' => 'group.logs',            'identifier' => 'Obtener log de grupos']
        ], $group->id, $role );


        $icon = Icon::query()->where('name','fas fa-user-plus' )->first();
        $group = Group::query()->create(['name' => 'Usuarios',  'icon' => $icon->id]);
        $group->save();
        $this->fillUserManipulation(Group::class, $group);
        $this->generatePermission([
            ['name' => 'user.index',       'identifier' => 'Usuarios', 'show' => true],
            ['name' => 'user.all',         'identifier' => 'Obtener usuarios'],
            ['name' => 'user.create',      'identifier' => 'Crear usuarios'],
            ['name' => 'user.store',       'identifier' => 'Guardar usuarios'],
            ['name' => 'user.show',        'identifier' => 'Mostrar usuarios'],
            ['name' => 'user.edit',        'identifier' => 'Editar usuarios'],
            ['name' => 'user.update',      'identifier' => 'Actualizar usuarios'],
            ['name' => 'user.destroy',     'identifier' => 'Eliminar usuarios'],
            ['name' => 'user.details',     'identifier' => 'Detalle de usuario'],
            ['name' => 'user.logs',        'identifier' => 'Obtener log de usuarios'],
            ['name' => 'user.assign_role', 'identifier' => 'Asignar roles a usuarios', 'show' => true],
        ], $group->id, $role );


        $icon = Icon::query()->where('name','far fa-sun' )->first();
        $group = Group::query()->create(['name' => 'Configuration', 'icon' => $icon->id]);
        $group->save();
        $this->fillUserManipulation(Group::class, $group);
        $this->generatePermission([
            ['name' => 'configuration.index',   'identifier' => 'Variables de sistema', 'show' => true],
            ['name' => 'configuration.all',  'identifier' => 'Obtener variables de sistema'],
            ['name' => 'configuration.create',  'identifier' => 'Crear variables de sistema'],
            ['name' => 'configuration.store',  'identifier' => 'Guardar variables de sistema'],
            ['name' => 'configuration.show',  'identifier' => 'Mostrar variables de sistema'],
            ['name' => 'configuration.edit',  'identifier' => 'Editar variables de sistema'],
            ['name' => 'configuration.update',  'identifier' => 'Actualizar variables de sistema'],
            ['name' => 'configuration.destroy',  'identifier' => 'Eliminar variables de sistema'],
            ['name' => 'configuration.details', 'identifier' => 'Detalle de Configuración'],
            ['name' => 'configuration.logs',  'identifier' => 'Obtener log de variables de sistema'],

            ['name' => 'system.index', 'identifier' => 'Configuraciones', 'show' => true],
            ['name' => 'system.all',  'identifier' => 'Obtener configuraciones'],
            ['name' => 'system.store',  'identifier' => 'Guardar configuraciones'],
            ['name' => 'system.details', 'identifier' => 'Detalle de Sistema'],
            ['name' => 'system.show',  'identifier' => 'Ver configuraciones'],

            ['name' => 'datatype.all',  'identifier' => 'Listas tipos de datos'],

            ['name' => 'user.profile',  'identifier' => 'Editar mi perfil', 'show' => true],


        ], $group->id, $role );


        $icon = Icon::query()->where('name','fa-solid fa-link' )->first();
        $group = Group::query()->create(['name' => 'Logistica', 'icon' => $icon->id ]);
        $group->save();
        $this->fillUserManipulation(Group::class, $group);


        $icon = Icon::query()->where('name','fas fa-file-image' )->first();
        $group = Group::query()->create(['name' => 'Multimedia', 'icon' => $icon->id ]);
        $group->save();
        $this->fillUserManipulation(Group::class, $group);
        $this->generatePermission([
            ['name' => 'image.index', 'identifier' => 'Imágenes', 'show' => true],
            ['name' => 'image.all' , 'identifier' => 'Obtener imágenes'],
            ['name' => 'image.store', 'identifier' => 'Guardar imágenes'],
            ['name' => 'file.index',  'identifier' => 'Archivos', 'show' => true],
            ['name' => 'file.all',  'identifier' => 'Obtener archivos'],
            ['name' => 'file.store',  'identifier' => 'Guardar archivos'],
        ], $group->id, $role );


        $icon = Icon::query()->where('name','fas fa-file-image' )->first();
        $group = Group::query()->create(['name' => 'Posts', 'icon' => $icon->id ]);
        $group->save();
        $this->fillUserManipulation(Group::class, $group);
        $this->generatePermission([
            ['name' => 'object_type.index',   'identifier' => 'Tipos de publicaciones', 'show' => true],
            ['name' => 'object_type.all',     'identifier' => 'Obtener tipos de publicación'],
            ['name' => 'object_type.create',  'identifier' => 'Crear tipos de publicación'],
            ['name' => 'object_type.store',   'identifier' => 'Guardar tipos de publicación'],
            ['name' => 'object_type.show',    'identifier' => 'Mostrar tipos de publicación'],
            ['name' => 'object_type.edit',    'identifier' => 'Editar tipos de publicación'],
            ['name' => 'object_type.update',  'identifier' => 'Actualizar tipos de publicación'],
            ['name' => 'object_type.destroy', 'identifier' => 'Eliminar tipos de publicación'],
            ['name' => 'object_type.assign_role',  'identifier' => 'Asignar tipo de objetos a roles', 'show' => true],
            ['name' => 'object_type.details', 'identifier' => 'Detalle de Tipo de objeto'],
            ['name' => 'object_type.logs',    'identifier' => 'Obtener log de tipos de publicación'],

            ['name' => 'object_type_relation.index',   'identifier' => 'Relación de objetos', 'show' => true],
            ['name' => 'object_type_relation.all',     'identifier' => 'Obtener relaciones de objetos'],
            ['name' => 'object_type_relation.create',  'identifier' => 'Crear relación'],
            ['name' => 'object_type_relation.store',   'identifier' => 'Guardar relación'],
            ['name' => 'object_type_relation.show',    'identifier' => 'Mostrar relación'],
            ['name' => 'object_type_relation.edit',    'identifier' => 'Editar relación'],
            ['name' => 'object_type_relation.update',  'identifier' => 'Actualizar relación'],
            ['name' => 'object_type_relation.destroy', 'identifier' => 'Eliminar relación'],
            ['name' => 'object_type_relation.details', 'identifier' => 'Detalle de Relación'],
            ['name' => 'object_type_relation.logs',    'identifier' => 'Obtener log de relaciones'],


            ['name' => 'object.index',   'identifier' => 'Objetos', 'show' => true],
            ['name' => 'object.all',     'identifier' => 'Obtener objetos'],
            ['name' => 'object.create',  'identifier' => 'Crear objeto'],
            ['name' => 'object.store',   'identifier' => 'Guardar objeto'],
            ['name' => 'object.show',    'identifier' => 'Mostrar objeto'],
            ['name' => 'object.edit',    'identifier' => 'Editar objeto'],
            ['name' => 'object.update',  'identifier' => 'Actualizar objeto'],
            ['name' => 'object.destroy', 'identifier' => 'Eliminar objeto'],
            ['name' => 'object.details', 'identifier' => 'Detalle de Objeto'],
            ['name' => 'object.logs',    'identifier' => 'Obtener log de objetos'],

            ['name' => 'field.index',   'identifier' => 'Campos personalizados', 'show' => true],
            ['name' => 'field.all',     'identifier' => 'Obtener Campos personalizados'],
            ['name' => 'field.create',  'identifier' => 'Crear Campos personalizados'],
            ['name' => 'field.store',   'identifier' => 'Guardar Campos personalizados'],
            ['name' => 'field.show',    'identifier' => 'Mostrar Campos personalizados'],
            ['name' => 'field.edit',    'identifier' => 'Editar Campos personalizados'],
            ['name' => 'field.update',  'identifier' => 'Actualizar Campos personalizados'],
            ['name' => 'field.destroy', 'identifier' => 'Eliminar Campos personalizados'],
            ['name' => 'field.details', 'identifier' => 'Detalle de Campo personalizado'],
            ['name' => 'field.logs',    'identifier' => 'Obtener log de Campos personalizados']

        ], $group->id, $role );

        $icon = Icon::query()->where('name','fa-solid fa-link' )->first();
        $group = Group::query()->create(['name' => 'Enlaces', 'icon' => $icon->id ]);
        $group->save();
        $this->fillUserManipulation(Group::class, $group);
        $this->generatePermission([
            ['name' => 'link.index',   'identifier' => 'Enlaces', 'show' => true],
            ['name' => 'link.all',     'identifier' => 'Obtener enlaces'],
            ['name' => 'link.create',  'identifier' => 'Crear enlaces'],
            ['name' => 'link.store',   'identifier' => 'Guardar enlaces'],
            ['name' => 'link.show',    'identifier' => 'Mostrar enlaces'],
            ['name' => 'link.edit',    'identifier' => 'Editar enlaces'],
            ['name' => 'link.update',  'identifier' => 'Actualizar enlaces'],
            ['name' => 'link.destroy', 'identifier' => 'Eliminar enlaces'],
            ['name' => 'link.details', 'identifier' => 'Detalle de enlace'],
            ['name' => 'link.logs',    'identifier' => 'Obtener log de enlaces'],

        ], $group->id, $role );
    }
}
