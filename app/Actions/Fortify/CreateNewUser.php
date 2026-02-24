<?php

namespace App\Actions\Fortify;

use App\Mail\KycDocuments\AdminKycReceived;
use App\Mail\Users\AdminUserRegistered;
use App\Models\User\User;
use Carbon\Carbon;
use Database\Seeders\Roles\RolesSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;
use Setting;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array  $input
     * @return \App\Models\User
     */
    public function create(array $input)
    {
        if(config('app.readonly')) {
            $rules = [
                'email' => 'required',
            ];
            $customMessages = [
                'required' => "In Demo Version we enabled READ ONLY mode to protect our demo content."
            ];

            Validator::make($input, $rules, $customMessages)->validate();
        };

        $status = setting('recaptcha.status', false);

        $site_key = config('captcha.sitekey');
        $site_secret = config('captcha.secret');

        $validationRules = [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'terms' => ['required', 'accepted']
        ];

        if($status && $site_key && $site_secret) {
            $validationRules['g-recaptcha-response'] = ['required', 'captcha'];
        }

        Validator::make($input, $validationRules)->validate();

        $referral_code = request()->get('referral', null);
        $referral = null;

        if($referral_code) {
            $referral = User::authorizable()->where('referral_code', $referral_code)->first();
        }

        $firstUser = User::first();

        $user = User::create([
            'name' => $input['email'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'referral_code' => $this->getUniqueReferralCode(),
            'referral_id' => $referral ? $referral->id : null,
        ]);

        $user->assignRole('user');

        if(!$firstUser) {

            $roles = array_column(RolesSeeder::ROLES, 'name');

            foreach ($roles as $role) {
                $user->assignRole($role);
            }

            $user->email_verified_at = Carbon::now();
            $user->kyc_verified_at = Carbon::now();
            $user->update();

            return $user;
        }

        // Admin Email Notification
        $adminEmail = Setting::get('notification.admin_email', false);
        $notificationAllowed = Setting::get('notification.new_user_registered', false);

        if($adminEmail && $notificationAllowed) {
            $route = route('admin.users') . "?search=" . $input['email'];
            Mail::to($adminEmail)->queue(new AdminUserRegistered($input['email'], $route));
        }
        // END Admin Email Notification

        return $user;

    }

    public function getUniqueReferralCode() {

        $str = generate_string();

        if(User::where('referral_code', $str)->exists()) {
            return $this->getUniqueReferralCode();
        }

        return $str;
    }
}
