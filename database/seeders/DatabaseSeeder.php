<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'books.viewAny',
            'books.view',
            'books.create',
            'books.update',
            'books.delete',
            'books.restore',
            'books.forceDelete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $bibliotecario = Role::firstOrCreate(['name' => User::ROLE_BIBLIOTECARIO, 'guard_name' => 'web']);
        $docente = Role::firstOrCreate(['name' => User::ROLE_DOCENTE, 'guard_name' => 'web']);
        $estudiante = Role::firstOrCreate(['name' => User::ROLE_ESTUDIANTE, 'guard_name' => 'web']);

        $bibliotecario->syncPermissions($permissions);
        $docente->syncPermissions(['books.viewAny', 'books.view']);
        $estudiante->syncPermissions(['books.viewAny', 'books.view']);

        $this->call([
            BookSeeder::class,
        ]);
    }
}
