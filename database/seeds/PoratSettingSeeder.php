<?php

use Illuminate\Database\Seeder;

class PoratSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Model\PortalSetting::create(['name' => "Session Logout Time", "code" => "sessionout", "value" => "20000000"]);
    }
}
