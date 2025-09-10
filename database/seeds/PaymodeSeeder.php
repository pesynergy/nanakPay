<?php

use Illuminate\Database\Seeder;

class PaymodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        App\Model\Paymode::create(["name" => "IMPS"]);
        App\Model\Paymode::create(["name" => "NEFT"]);
        App\Model\Paymode::create(["name" => "NET BANKING"]);
        App\Model\Paymode::create(["name" => "CASH"]);
        App\Model\Paymode::create(["name" => "OTHER"]);
    }
}
