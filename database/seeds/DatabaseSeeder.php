<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UsersTableSeeder::class);
        $this->call(RolesTableSeeder::class);
        $this->call(Permission::class);
        $this->call(CompanySeeder::class);
        $this->call(CircleSeeder::class);
        $this->call(PoratSettingSeeder::class);
        $this->call(PaymodeSeeder::class);
        $this->call(ProviderSeeder::class);
        $this->call(ApiSeeder::class);
    }
}
