<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Socialite;

class OAuthController extends Controller
{
    function OAuthRedirect(Request $request) {
        // this call back url is the url that the user will be redirected to frontend
        $driver = $request->query('driver');
        $callback_url = $request->query('callback_url','');
         $redirectUrl = Socialite::driver($driver)
            ->stateless()
            ->with(['state' => base64_encode($callback_url)])
            ->redirect()
            ->getTargetUrl();

        return response(['redirect_url' => $redirectUrl]);
    }
    function OAuthCallback(Request $request, $driver)
    {
        $callback_url = base64_decode($request->query('state', ''));
        try {
            $oauthUser = Socialite::driver($driver)->stateless()->user();
        } catch (\Exception $e) {
            return redirect($callback_url . '?error=' . $driver . '_oauth_failed');
        }
        
        // check if the user has a name, if not, use the nickname as the name (for GitHub)
        if($driver === 'github' && !$oauthUser->getName()) {
            $oauthUser->name = $oauthUser->getNickname();
        }

        $user = User::firstOrCreate(
            ['email' => $oauthUser->getEmail()],
            [
                'name' => $oauthUser->getName(),
                'profile_image' => $oauthUser->getAvatar(),
            ]
        );

        $user->save();

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        $token = $user->createToken('auth_token', ['exchange-new-token'], now()->addMinute())->plainTextToken;

        return redirect($callback_url . '?token=' . urlencode($token));
    }

    function OAuthExchangeToken(Request $request)
    {
        $user = $request->user();

        if (!$user->currentAccessToken()->can('exchange-new-token')) {
            return response(['message' => 'Invalid token.'], 403);
        }

        $user->currentAccessToken()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response([
            'message' => 'User signed in.',
            'user' => new UserResource($user),
            'token' => $token
        ], 200);
    }
}
