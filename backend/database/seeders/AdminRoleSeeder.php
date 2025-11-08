<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminRoleSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', '86jamg@gmail.com');
        $user = User::where('email', $email)->first();
        if ($user) {
            $user->role = 'admin';
            $user->is_blocked = false;
            $user->save();
            $this->command?->info("User {$email} promoted to admin.");
        } else {
            $this->command?->warn("User {$email} not found. No changes made.");
        }
    }
}
