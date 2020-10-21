<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->users() as $user) {
            $user['email_verified_at'] = now();
            $user['password'] = bcrypt('1234qwer');
            $user['remember_token'] = Str::random(10);

            User::create($user);
        }
    }

    private function users()
    {
        return [
            [
                'account' => "sayat.a",
                'role_id' => Role::findByCode('host')->id,
                'email' => "amanbaev.sayat@gmail.com",
                'phone' => "+77763442424",
            ]
        ];
    }
}
