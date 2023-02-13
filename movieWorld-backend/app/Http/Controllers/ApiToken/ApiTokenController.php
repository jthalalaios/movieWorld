<?php

namespace App\Http\Controllers\ApiToken;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiToken\StoreApiTokenRequest;
use App\Http\Resources\User\UserAuthenticationResource;
use App\Models\User;
use Illuminate\Support\Facades\Lang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response;

class ApiTokenController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\ApiTokens\StoreApiTokenRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreApiTokenRequest $request)
    {
        /** @var \App\Models\User */
        $user = User::where('username', $request->input('username'))->first();

        if (is_null($user) || !Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'data'    =>  [],
                'message' => 'ERROR',
                'metadata' => ['response_message' => Lang::get('auth.failed')],
            ], Response::HTTP_BAD_REQUEST);
        }

        $token = $user->createToken($request->input('username'))->plainTextToken;

        return (new UserAuthenticationResource($user))->additional(['metadata' => ['response_message' => Lang::get('messages.login_succeded')]])->setToken($token)->response()->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        /** @var \App\Models\User */
        $authUser = $request->user();
        /** @var \Laravel\Sanctum\PersonalAccessToken */
        $personalAccessToken = $authUser->currentAccessToken();

        $personalAccessToken->delete();

        return response()->json(['message' => Lang::get('messages.logout_succeded')], Response::HTTP_OK);
    }
}
