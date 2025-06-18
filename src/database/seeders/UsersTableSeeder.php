<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Str;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin2025'),
            'role' => 'admin',
        ]);
    
        foreach (range(1, 10) as $i) {
            User::create([
                'name' => "ユーザー{$i}",
                'email' => "user{$i}@example.com",
                'password' => Hash::make('password2025'),
                'role' => 'user',
            ]);
        }
    }
}
