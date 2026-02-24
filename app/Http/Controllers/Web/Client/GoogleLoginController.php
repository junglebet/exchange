<?php

namespace App\Http\Controllers\Web\Client;

use App\Http\Controllers\Controller;

use App\Mail\Users\AdminUserRegistered;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Setting;
use Throwable;

class GoogleLoginController extends Controller
{
    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(): RedirectResponse
    {
        try
        {
            $user = Socialite::driver('google')->user();
        }
        catch (Throwable $e) {
            return redirect(route('login'))->with('error', 'Twitter authentication failed.');
        }

        $existingUser = User::where('email', $user->email)->first();

        if ($existingUser) {

            if(!$existingUser->google_id) {
                $existingUser->google_id = $user->id;
                $existingUser->update();
            }

            auth()->login($existingUser, true);
        } else {
            // Create a new user.
            $newUser = new User();
            $newUser->name = $user->name;
            $newUser->email = $user->email;
            $newUser->email_verified_at = Carbon::now();
            $newUser->google_id = $user->id;
            $newUser->referral_code = $this->getUniqueReferralCode();
            $newUser->password = bcrypt(request(Str::random('30')));
            $newUser->save();

            $newUser->assignRole('user');

            // Admin Email Notification
            $adminEmail = Setting::get('notification.admin_email', false);
            $notificationAllowed = Setting::get('notification.new_user_registered', false);

            if($adminEmail && $notificationAllowed) {
                $route = route('admin.users') . "?search=" . $user->email;
                Mail::to($adminEmail)->queue(new AdminUserRegistered($user->email, $route));
            }

            // Log in the new user.
            auth()->login($newUser, true);
        }

        return redirect()->intended('/');
    }

    public function getUniqueReferralCode() {

        $str = generate_string();

        if(User::where('referral_code', $str)->exists()) {
            return $this->getUniqueReferralCode();
        }

        return $str;
    }
}
