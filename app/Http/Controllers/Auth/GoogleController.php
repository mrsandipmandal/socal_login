<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Socialite;
use Google_Client;
use Google_Service_PeopleService;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class GoogleController extends Controller
{

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();

        /* for mobile number */
        // return Socialite::driver('google')
        //     ->scopes(['openid', 'profile', 'email', 'https://www.googleapis.com/auth/user.phonenumbers.read'])
        //     ->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            // Get user details
            $googleId = $googleUser->id;
            $name = $googleUser->name;
            $email = $googleUser->email;
            $avatar = $googleUser->avatar;

            // You can now use these details to register or log the user in
            $user = User::where('google_id', $googleId)->orWhere('email', $email)->first();

            if ($user) {
                $user->update([
                    'google_id' => $googleId,
                    'name' => $name,
                    'avatar' => $avatar,
                ]);
            } else {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'google_id' => $googleId,
                    'avatar' => $avatar,
                    'password' => encrypt('123') // Dummy password for initial registration
                ]);
            }

            Auth::login($user);
            return redirect('/home');
        } catch (\Exception $e) {
            return redirect('/login?error')->withErrors(['msg' => 'Failed to login with Google.']);
        }
    }

    /* for get Mobile No */
    /* public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $client = new Google_Client();
        $client->setAccessToken($googleUser->token);

        $service = new Google_Service_PeopleService($client);
        $people = $service->people->get('people/me', ['personFields' => 'phoneNumbers']);

        $phoneNumbers = $people->getPhoneNumbers();
        $phoneNumber = $phoneNumbers ? $phoneNumbers[0]->getValue() : null;

        $user = User::where('google_id', $googleUser->id)->first();

        if ($user) {
            $user->update([
                'google_id' => $googleUser->id,
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'avatar' => $googleUser->avatar,
                'phone_number' => $phoneNumber,
            ]);
        } else {
            $user = User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
                'avatar' => $googleUser->avatar,
                'phone_number' => $phoneNumber,
                'password' => encrypt('123')
            ]);
        }

        Auth::login($user);
        return redirect('/home');
    } */


}
