<?php

use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        App\Model\Company::create([
            "companyname" => "My Company",
            'website' => "localhost/newproject",
            'status' => '1'
        ]);
    }
}
