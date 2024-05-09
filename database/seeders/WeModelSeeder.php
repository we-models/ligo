<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Group;
use App\Models\NewPermission;
use App\Models\NewRole;
use App\Models\SystemConfiguration;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class WeModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function applyPermission(array $data, $role){
        foreach ($data as $permission){
            $role->givePermissionTo($permission['name']);
        }
    }

    public function addBusinessToObject($class, $object, $business){
        DB::table('model_has_business')->insert([
            'model_type' => $class,
            'model_id' => $object->id,
            'business' => $business->id
        ]);
    }

    public function run()  {

        //Create the first Business for the system
        $business = Business::query()->create([
            'code' => '59c8fc2b-912b-4da7-b552-6ff423ebdc7a',
            'name' => 'We Models Agency',
            'description' => 'Is the general system'
        ]);
        $business->save();

        //Create de general Role for development
        $role  = NewRole::query()->create([
            'name' =>  'Wemodels Administrator',
            'guard_name' => 'web',
            'public' => false,
            'icon' => 'fab fa-readme',
            'description' => '<p>Is the system developer </p>',
            'is_admin' => true
        ]);
        $role->save();

        //Create the first user for administration with password Abc123.....
        $user = User::query()->create([
            'name' => 'Axel Mora', 'email' => 'wemodelsecuador@gmail.com', 'password' => Hash::make('2022WMAxlM'), 'code' => generateUserCode()
        ]);
        $user->save();

        DB::table('users')->where('id', $user->getAuthIdentifier())->update(['email_verified_at' => date('Y-m-d H:i:s')]);

        //Assign user to business
        $this->addBusinessToObject(User::class, $user, $business);

        //Assign role to user
        $user->assignRole($role->name);

        //Assign role to business
        $this->addBusinessToObject(NewRole::class, $role, $business);


        $group = Group::query()->where('name', '=','Home')->first();
        $this->addBusinessToObject(Group::class, $group, $business);
        $this->applyPermission([
            ['name' => 'home'],
            ['name' => 'slider.index' ],
            ['name' => 'slider.all'   ],
            ['name' => 'slider.create'],
            ['name' => 'slider.store' ],
            ['name' => 'slider.show'  ],
            ['name' => 'slider.edit'  ],
            ['name' => 'slider.update'],
            ['name' => 'slider.destroy'],
            ['name' => 'slider.details'],
            ['name' => 'slider.logs'  ],
        ], $role);


        $group = Group::query()->where('name', '=', 'Roles')->first();
        $this->addBusinessToObject(Group::class, $group, $business);
        $this->applyPermission([
            ['name' => 'role.index'],
            ['name' => 'role.all'],
            ['name' => 'role.show'],
            ['name' => 'role.details'],
            ['name' => 'role.logs'],
        ], $role );


        $group = Group::query()->where('name', '=', 'Permisos')->first();
        $this->addBusinessToObject(Group::class, $group, $business);
        $this->applyPermission([
            ['name' => 'permission.index',       ],
            ['name' => 'permission.all',         ],
            ['name' => 'permission.details',     ],
            ['name' => 'permission.show',        ],
            ['name' => 'permission.assign_role', ],
            ['name' => 'permission.assign_group',]
        ], $role );

        $this->applyPermission([
            ['name' => 'assign.objects',  ],
            ['name' => 'assign.save',     ],
        ], $role);


        $group = Group::query()->where('name', '=', 'Empresas')->first();
        $this->addBusinessToObject(Group::class, $group, $business);
        $this->applyPermission([
            ['name' => 'business.index'   ],
            ['name' => 'business.all'     ],
            ['name' => 'business.show'    ],
            ['name' => 'business.edit'    ],
            ['name' => 'business.update'  ],
            ['name' => 'business.details' ],
            ['name' => 'business.logs'    ],
        ], $role );


        $group = Group::query()->where('name', '=', 'Grupos')->first();
        $this->addBusinessToObject(Group::class, $group, $business);
        $this->applyPermission([
            ['name' => 'group.index'  ],
            ['name' => 'group.all'    ],
            ['name' => 'group.create' ],
            ['name' => 'group.store' ],
            ['name' => 'group.show'   ],
            ['name' => 'group.details' ],
            ['name' => 'group.logs'   ],
        ], $role );


        $group = Group::query()->where('name', '=', 'Usuarios')->first();
        $this->addBusinessToObject(Group::class, $group, $business);
        $this->applyPermission([
            ['name' => 'user.index'     ],
            ['name' => 'user.all'       ],
            ['name' => 'user.create'    ],
            ['name' => 'user.store'     ],
            ['name' => 'user.show'      ],
            ['name' => 'user.edit'      ],
            ['name' => 'user.update'    ],
            ['name' => 'user.destroy'   ],
            ['name' => 'user.logs'      ],
            ['name' => 'user.details'   ],
            ['name' => 'user.assign_role'],
        ], $role );


        $group = Group::query()->where('name', '=', 'Configuration')->first();
        $this->addBusinessToObject(Group::class, $group, $business);
        $this->applyPermission([
            ['name' => 'configuration.index'],
            ['name' => 'configuration.all'],
            ['name' => 'configuration.details'],
            ['name' => 'configuration.show'],
            ['name' => 'system.index'],
            ['name' => 'system.all'],
            ['name' => 'system.store'],
            ['name' => 'system.details'],
            ['name' => 'system.show'],
            ['name' => 'datatype.all'],

            ['name' => 'font.index'],
            ['name' => 'font.all'],
            ['name' => 'font.create'],
            ['name' => 'font.edit'],
            ['name' => 'font.update'],
            ['name' => 'font.store'],
            ['name' => 'font.destroy'],
            ['name' => 'font.details'],
            ['name' => 'font.logs'],
            ['name' => 'font.show']

        ], $role );


        $group = Group::query()->where('name', '=', 'Media files')->first();
        $this->addBusinessToObject(Group::class, $group, $business);
        $this->applyPermission([
            ['name' => 'image.index'],
            ['name' => 'image.all' ],
            ['name' => 'image.store'],
            ['name' => 'file.index'],
            ['name' => 'file.all'],
            ['name' => 'file.store'],
        ], $role );

        $group = Group::query()->where('name', '=', 'Posts')->first();
        $this->addBusinessToObject(Group::class, $group, $business);
        $this->applyPermission([
            ['name' => 'object_type.index',  ],
            ['name' => 'object_type.all',    ],
            ['name' => 'object_type.create', ],
            ['name' => 'object_type.store',  ],
            ['name' => 'object_type.show',   ],
            ['name' => 'object_type.edit',   ],
            ['name' => 'object_type.update', ],
            ['name' => 'object_type.destroy',],
            ['name' => 'object_type.logs',   ],
            ['name' => 'object_type.details',   ],
            ['name' => 'object_type.assign_role'],

            ['name' => 'object.index',  ],
            ['name' => 'object.all',    ],
            ['name' => 'object.create', ],
            ['name' => 'object.store',  ],
            ['name' => 'object.show',   ],
            ['name' => 'object.edit',   ],
            ['name' => 'object.update', ],
            ['name' => 'object.destroy',],
            ['name' => 'object.details',],
            ['name' => 'object.logs',   ],

            ['name' => 'object_type_relation.index',  ],
            ['name' => 'object_type_relation.all',    ],
            ['name' => 'object_type_relation.create', ],
            ['name' => 'object_type_relation.store',  ],
            ['name' => 'object_type_relation.show',   ],
            ['name' => 'object_type_relation.edit',   ],
            ['name' => 'object_type_relation.update', ],
            ['name' => 'object_type_relation.destroy',],
            ['name' => 'object_type_relation.details',],
            ['name' => 'object_type_relation.logs',   ],

            ['name' => 'field.index',  ],
            ['name' => 'field.all',    ],
            ['name' => 'field.create', ],
            ['name' => 'field.store',  ],
            ['name' => 'field.show',   ],
            ['name' => 'field.edit',   ],
            ['name' => 'field.update', ],
            ['name' => 'field.destroy',],
            ['name' => 'field.details',],
            ['name' => 'field.logs',   ],

        ], $role );

        $group = Group::query()->where('name', '=', 'Enlaces')->first();
        $this->addBusinessToObject(Group::class, $group, $business);
        $this->applyPermission([
            ['name' => 'link.index',  ],
            ['name' => 'link.all',    ],
            ['name' => 'link.create', ],
            ['name' => 'link.store',  ],
            ['name' => 'link.show',   ],
            ['name' => 'link.edit',   ],
            ['name' => 'link.update', ],
            ['name' => 'link.destroy',],
            ['name' => 'link.details',],
            ['name' => 'link.logs',   ],

        ], $role );

        $system_configs = [
            ['configuration' => 4, 'value' => 'developer.ecuador@gmail.com'],
            ['configuration' => 5, 'value' => 'qpurcuhlmvnwwqtf'],
            ['configuration' => 6, 'value' => 'ssl'],
        ];

        foreach ($system_configs as $config){
            $config[BUSINESS_IDENTIFY] = $business->id;
            SystemConfiguration::query()->create($config);
        }

    }
}
