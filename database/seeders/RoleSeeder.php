<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(){
        $role=Role::create(['name'=>'Administrador']); // administrador.. aceso a todo....
        $role->permissions()->attach([1,2,3,4,5,8]);

        $role=Role::create(['name'=>'Moderador']); // moderador... acceso a ciertos datos que no son sensibles... aun falta añadir una logica
        $role->permissions()->attach([1,2,3,4,5,8]);

        $role=Role::create(['name'=>"Usuario"]); // aqui son los turistas del aplicativo
        $role->permissions()->attach([6]);

        $role=Role::create(['name'=>"Emprendedor"]); // es quien publica producto servicio tambien se registra
        $role->permissions()->attach([7]);
    }

}
