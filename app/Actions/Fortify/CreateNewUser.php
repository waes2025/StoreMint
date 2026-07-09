<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
        ])->validate();

        return DB::transaction(function () use ($input) {
            $user = User::create([
                'first_name' => $input['first_name'],
                'last_name' => $input['last_name'],
                'username' => explode('@', $input['email'])[0].'_'.rand(100, 999),
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
                'user_type' => 'customer',
            ]);

            // Create contact record
            $contactId = DB::table('contacts')->insertGetId([
                'business_id' => $user->business_id ?? 1,
                'type' => 'customer',
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'name' => trim($user->first_name.' '.$user->last_name),
                'email' => $user->email,
                'mobile' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Link user and contact via user_contact_access
            DB::table('user_contact_access')->insert([
                'user_id' => $user->id,
                'contact_id' => $contactId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $user;
        });
    }
}
