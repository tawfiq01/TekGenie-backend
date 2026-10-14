<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Rules\BdMobileNumber;
use App\Support\BdPhone;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user (FR-ACC-001, VR-01…04).
     *
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function create(array $input): User
    {
        $validated = Validator::make($input, [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'phone' => ['required', 'string', new BdMobileNumber],
            'email' => [
                'required',
                'string',
                'email',
                'max:191',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ])->validate();

        $phone = BdPhone::normalize($validated['phone']);

        Validator::make(['phone' => $phone], [
            'phone' => [Rule::unique(User::class, 'phone')],
        ])->validate();

        return User::create([
            'name' => $validated['name'],
            'phone' => $phone,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);
    }
}
