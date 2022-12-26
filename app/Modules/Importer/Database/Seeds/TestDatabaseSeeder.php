<?php

namespace App\Modules\Test\Database\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Core\Rbac\Permission;

class TestDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $list = $this->getPermissions();

        $result = Permission::set('test', $list);

        foreach ($result as $info) {
            $this->command->info($info);
        }
    }

    /**
     * Get permissions list
     */
    public function getPermissions()
    {
        return [
            /*
            'test.index'   => 'Test index',
            'test.show'    => 'Test show',
            'test.store'   => 'Test store',
            'test.update'  => 'Test update',
            'test.destroy' => 'Test destroy',
            */
        ];
    }
}
