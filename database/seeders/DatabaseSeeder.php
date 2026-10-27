<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Validator::make(config('assessment.admin'), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', Password::min(12)],
        ])->validate();

        User::firstOrNew(['email' => $admin['email']])->forceFill([
            'name' => $admin['name'],
            'password' => $admin['password'],
            'is_admin' => true,
        ])->save();

        foreach (['Electronics', 'Office', 'Home'] as $name) {
            Category::firstOrCreate(['name' => $name]);
        }
    }
}
