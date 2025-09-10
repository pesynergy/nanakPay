<?php

use Illuminate\Database\Seeder;

class ProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        App\Model\Provider::create(['name' => 'Fund','recharge1' => 'fund','recharge2' => 'fund','api_id' => '1','type' => 'fund',"status" => "1"]);
        App\Model\Provider::create(['name' => 'Utipancard','recharge1' => 'utipancard','recharge2' => 'utipancard','api_id' => '2','type' => 'pancard',"status" => "1"]);
    }
}
