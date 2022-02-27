<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    use WithoutModelEvents;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $adminUser = [
            'name' => 'Admin user',
            'email' => 'admin@aspire.com',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('secret'),
            'is_admin' => true,
            'remember_token' => Str::random(10)
        ];
        User::create($adminUser);
    }
}
