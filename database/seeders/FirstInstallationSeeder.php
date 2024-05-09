<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Group;
use App\Models\NewPermission;
use App\Models\NewRole;
use App\Models\User;
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

    public function run()  {

        //Create the first Business for the system
        $business = Business::query()->create([
            'code' => 'f9c83aa9-d5d9-4232-8c72-8d284418a180',
            'name' => 'OPC SYSTEM',
            'description' => 'Is the general system'
        ]);
        $business->save();

        //Create de general Role for development
        $role  = NewRole::query()->create([
            'name' =>  'Developer',
            'guard_name' => 'web',
            'public' => false,
            'icon' => 'fab fa-readme',
            'description' => '<p>Is the system developer </p>',
            'is_admin' => true
        ]);
        $role->save();

        //Create the first user for administration with password Abc123.....
        $user = User::query()->create([
            'name' => 'Admin', 'email' => 'admin@opc.com', 'password' => Hash::make('2022WMAxlM'), 'code' => generateUserCode()
        ]);
        $user->save();

        $this->current_user = $user->getAuthIdentifier();

        DB::table('users')->where('id', $user->getAuthIdentifier())->update(['email_verified_at' => date('Y-m-d H:i:s')]);

        //Assign user to business
        $this->addBusinessToObject(User::class, $user, $business);

        //Assign role to user
        $user->assignRole($role->name);

        //Assign role to business
        $this->addBusinessToObject(NewRole::class, $role, $business);


        $group = Group::query()->create(['name' => 'Home',  'icon' => 'fas fa-home']);
        $group->save();
        $this->addBusinessToObject(Group::class, $group, $business);
        $this->generatePermission([
            ['name' => 'home', 'identifier' => 'Home', 'show' => true],
            ['name' => 'slider.index',            'identifier' => 'Sliders', 'show' => true],
            ['name' => 'slider.all',              'identifier' => 'Obtener Sliders'],
            ['name' => 'slider.create',           'identifier' => 'Crear Slider'],
            ['name' => 'slider.store',            'identifier' => 'Guardar Slider'],
            ['name' => 'slider.show',             'identifier' => 'Mostrar Slider'],
            ['name' => 'slider.edit',             'identifier' => 'Editar Slider'],
            ['name' => 'slider.update',           'identifier' => 'Actualizar Slider'],
            ['name' => 'slider.destroy',          'identifier' => 'Eliminar Slider'],
            ['name' => 'slider.details',          'identifier' => 'Detalle de Slider'],
            ['name' => 'slider.logs',             'identifier' => 'Obtener log de Slider'],
        ], $group->id, $role);


        $group = Group::query()->create(['name' => 'Roles', 'icon' => 'fab fa-buromobelexperte']);
        $group->save();
        $this->addBusinessToObject(Group::class, $group, $business);
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
            ['name' => 'role.logs',             'identifier' => 'Obtener log de roles'],
            ['name' => 'role.assign_business',  'identifier' => 'Asignar roles a empresas', 'show' => true]
        ], $group->id, $role );


        $group = Group::query()->create(['name' => 'Permisos', 'icon' => 'fas fa-newspaper']);
        $group->save();
        $this->addBusinessToObject(Group::class, $group, $business);
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


        $group = Group::query()->create(['name' => 'Empresas',  'icon' => 'fas fa-building']);
        $group->save();
        $this->addBusinessToObject(Group::class, $group, $business);
        $this->generatePermission([
            ['name' => 'business.index',   'identifier' => 'Empresas', 'show' => true],
            ['name' => 'business.all',     'identifier' => 'Obtener empresas'],
            ['name' => 'business.create',  'identifier' => 'Crear empresas'],
            ['name' => 'business.store',   'identifier' => 'Guardar empresas'],
            ['name' => 'business.show',    'identifier' => 'Mostrar empresas'],
            ['name' => 'business.edit',    'identifier' => 'Editar empresas'],
            ['name' => 'business.update',  'identifier' => 'Actualizar empresas'],
            ['name' => 'business.destroy', 'identifier' => 'Eliminar empresas'],
            ['name' => 'business.details', 'identifier' => 'Detalle de Empresa'],
            ['name' => 'business.logs',    'identifier' => 'Obtener log de empresas'],

        ], $group->id, $role );


        $group = Group::query()->create(['name' => 'Grupos', 'icon' => 'fas fa-object-group']);
        $group->save();
        $this->addBusinessToObject(Group::class, $group, $business);
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
            ['name' => 'group.logs',            'identifier' => 'Obtener log de grupos'],
            ['name' => 'group.assign_business', 'identifier' => 'Asignar grupos a empresas', 'show' => true]
        ], $group->id, $role );


        $group = Group::query()->create(['name' => 'Usuarios',  'icon' => 'fas fa-user-plus']);
        $group->save();
        $this->addBusinessToObject(Group::class, $group, $business);
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


        $group = Group::query()->create(['name' => 'Configuration', 'icon' => 'far fa-sun']);
        $group->save();
        $this->addBusinessToObject(Group::class, $group, $business);
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

            ['name' => 'font.index',   'identifier' => 'Fuentes', 'show' => true],
            ['name' => 'font.all',  'identifier' => 'Obtener fuentes'],
            ['name' => 'font.create',  'identifier' => 'Crear fuentes'],
            ['name' => 'font.store',  'identifier' => 'Guardar fuentes'],
            ['name' => 'font.show',  'identifier' => 'Mostrar fuentes'],
            ['name' => 'font.edit',  'identifier' => 'Editar fuentes'],
            ['name' => 'font.update',  'identifier' => 'Actualizar fuentes'],
            ['name' => 'font.destroy',  'identifier' => 'Eliminar fuentes'],
            ['name' => 'font.details', 'identifier' => 'Detalle de fuentes'],
            ['name' => 'font.logs',  'identifier' => 'Obtener log de fuentes'],

            ['name' => 'system.index', 'identifier' => 'Configuraciones', 'show' => true],
            ['name' => 'system.all',  'identifier' => 'Obtener configuraciones'],
            ['name' => 'system.store',  'identifier' => 'Guardar configuraciones'],
            ['name' => 'system.details', 'identifier' => 'Detalle de Sistema'],
            ['name' => 'system.show',  'identifier' => 'Ver configuraciones'],

            ['name' => 'datatype.all',  'identifier' => 'Listas tipos de datos'],

        ], $group->id, $role );


        $group = Group::query()->create(['name' => 'Media files', 'icon' => 'fas fa-file-image' ]);
        $group->save();
        $this->addBusinessToObject(Group::class, $group, $business);
        $this->generatePermission([
            ['name' => 'image.index', 'identifier' => 'Imágenes', 'show' => true],
            ['name' => 'image.all' , 'identifier' => 'Obtener imágenes'],
            ['name' => 'image.store', 'identifier' => 'Guardar imágenes'],
            ['name' => 'file.index',  'identifier' => 'Archivos', 'show' => true],
            ['name' => 'file.all',  'identifier' => 'Obtener archivos'],
            ['name' => 'file.store',  'identifier' => 'Guardar archivos'],
        ], $group->id, $role );


        $group = Group::query()->create(['name' => 'Posts', 'icon' => 'fas fa-file-image' ]);
        $group->save();
        $this->addBusinessToObject(Group::class, $group, $business);
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

        $group = Group::query()->create(['name' => 'Enlaces', 'icon' => 'fas fa-file-image' ]);
        $group->save();
        $this->addBusinessToObject(Group::class, $group, $business);
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
