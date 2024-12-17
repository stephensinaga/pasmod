<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserTest extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $UserData = [
            [
                'name' => 'Dev Cashier',
                'email' => 'thanos@gmail.com',
                'password' => 'dev123',
                'role' => 'cashier'
            ],
            [
                'name' => 'Cashier',
                'email' => 'pasmod@gmail.com',
                'password' => 'pasmod123',
                'role' => 'cashier'
            ],
            [
                'name' => 'Wologito',
                'email' => 'wologitosmg168@gmail.com',
                'password' => 'wologito168',
                'role' => 'admin'
            ],
        ];

        foreach ($UserData as $key => $val) {
            User::create($val);
        }
    }
}
