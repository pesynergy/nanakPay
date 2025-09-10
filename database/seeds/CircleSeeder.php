<?php

use Illuminate\Database\Seeder;

class CircleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        App\Model\Circle::create(['state' => 'ASSAM','plan_code' => '2']);
        App\Model\Circle::create(['state' => 'BIHAR JHARKHAND','plan_code' => '3']);
        App\Model\Circle::create(['state' => 'CHENNAI','plan_code' => '4']);
        App\Model\Circle::create(['state' => 'GUJARAT','plan_code' => '6']);
        App\Model\Circle::create(['state' => 'HARYANA','plan_code' => '7']);
        App\Model\Circle::create(['state' => 'HIMACHAL PRADESH','plan_code' => '8']);
        App\Model\Circle::create(['state' => 'JAMMU KASHMIR','plan_code' => '9']);
        App\Model\Circle::create(['state' => 'KARNATAKA','plan_code' => '10']);
        App\Model\Circle::create(['state' => 'KERALA','plan_code' => '11']);
        App\Model\Circle::create(['state' => 'KOLKATA','plan_code' => '12']);
        App\Model\Circle::create(['state' => 'MAHARASHTRA','plan_code' => '13']);
        App\Model\Circle::create(['state' => 'MADHYA PRADESH','plan_code' => '14']);
        App\Model\Circle::create(['state' => 'CHHATTISGARH','plan_code' => '0']);
        App\Model\Circle::create(['state' => 'MUMBAI','plan_code' => '15']);
        App\Model\Circle::create(['state' => 'NORTH EAST','plan_code' => '16']);
        App\Model\Circle::create(['state' => 'ORISSA','plan_code' => '17']);
        App\Model\Circle::create(['state' => 'PUNJAB','plan_code' => '18']);
        App\Model\Circle::create(['state' => 'RAJASTHAN','plan_code' => '19']);
        App\Model\Circle::create(['state' => 'TAMIL NADU','plan_code' => '20']);
        App\Model\Circle::create(['state' => 'UP EAST','plan_code' => '21']);
        App\Model\Circle::create(['state' => 'UP WEST','plan_code' => '22']);
        App\Model\Circle::create(['state' => 'WEST BENGAL','plan_code' => '23']);
        App\Model\Circle::create(['state' => 'DELHI NCR','plan_code' => '5']);
        App\Model\Circle::create(['state' => 'ANDHRA PRADESH','plan_code' => '1']);
        App\Model\Circle::create(['state' => 'Delhi/NCR','plan_code' => '1']);
        App\Model\Circle::create(['state' => 'UTTARAKHAND','plan_code' => '0']);
    }
}
