<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function time()
    {
        return response()->json(['time' => Carbon::now()]);
    }

    public function auth(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('The provided credentials are incorrect.')],
            ]);
        }

        // 2FA Auth
        if($user->two_factor_secret) {
            $request->validate([
                'twofa' => 'required',
            ]);

            $google2fa = app(Google2FA::class);

            $valid = $google2fa->verifyKey(decrypt($user->two_factor_secret), (string)$request->get('twofa'), 1);

            if($valid) {
                throw ValidationException::withMessages([
                    'twofa' => [__('2FA Code is invalid or expired.')],
                ]);
            }
        }


        return response()->json([
            'status' => true,
            'user' => [
                'verified' => (bool)$user->kyc_verified_at,
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name
            ],
            'token' => $user->createToken($request->device_name)->plainTextToken
        ]);
    }

    public function user(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'registered_date' => Carbon::parse($user->created_at)->format('d.m.Y'),
            'referral_code' => $user->referral_code,
            'verified' => (bool)$user,
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'email_verified' => Carbon::parse($user->email_verified_at)->format('d.m.Y'),
            'kyc_verified' => Carbon::parse($user->kyc_verified_at)->format('d.m.Y'),
        ]);
    }

    public function languageFile(Request $request) {

        $slug = $request->get('lang', app()->getLocale());

        $file = resource_path('/lang/' . $slug . '.json');

        if(file_exists($file)) {
            return response()->json(json_decode(file_get_contents($file)));
        }

        $default = resource_path('/lang/en.json');

        return response()->json(json_decode(file_get_contents($default)));
    }
}
