<?php

use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        App\Model\Role::create([
            "name" => "Super Admin",
            'slug' => "admin"
        ]);

        App\Model\Role::create([
            "name" => "Admin",
            'slug' => "whitelable"
        ]);

        App\Model\Role::create([
            "name" => "Master Distributor",
            'slug' => "md"
        ]);

        App\Model\Role::create([
            "name" => "Distributor",
            'slug' => "distributor"
        ]);

        App\Model\Role::create([
            "name" => "Retailer",
            'slug' => "retailer"
        ]);

        App\Model\Role::create([
            "name" => "Api Partner",
            'slug' => "apiuser"
        ]);
    }
}
