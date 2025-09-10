<?php

use Illuminate\Database\Seeder;

class ApiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        App\Model\Api::create(['product' => 'Fund Manager','name' => 'Fund','url' => 'fund','username' => 'fund','password' => 'fund','optional1' => 'fund','code' => 'fund','type' => 'fund','status' => '1']);
        App\Model\Api::create(['product' => 'Uti Pancard','name' => 'Uti Pancard','url' => 'localhost/edigital/api/pancard','username' => 'jopekdaEhe2osVn642jgrbyiBMCmMe','password' => 'jopekdaEhe2osVn642jgrbyiBMCmMe', 'optional1' => 'uti','code' => 'utipancard','type' => 'pancard','status' => '1']);
    }
}
 