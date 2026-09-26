<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'name' => config('auth.local_user.name'),
            'email' => config('auth.local_user.email'),
            'password' => 'password',
        ]);

        $user->markEmailAsVerified();
    }
}
