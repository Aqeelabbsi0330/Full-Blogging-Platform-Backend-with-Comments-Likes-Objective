<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\UserToken;
use App\Models\RefreshToken;
use App\Models\BlacklistedAccessToken;
use App\Models\BlacklistedRefreshToken;
use Illuminate\Support\Facades\Hash;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Carbon\Carbon;
use Illuminate\Support\Str;


class BlogController extends Controller
{
    //Register user
    public function createUser(Request $request)
    {
        try {

            $name = $request->name;
            $email = $request->email;
            $password = $request->password;
            $role_id = $request->role_id;
            if (!$name || !$email || !$password || !$role_id) {
                return response()->json([
                    'error' => 'some thing missing pleade fill all fields. All fields are requeired '
                ]);
            }

            $user = new User;
            $user->name = $name;
            $user->email = $email;
            $user->password = $password;
            $user->role_id = $role_id;
            $user->save();

            // $user = User::create($request->only(['name', 'email', 'password']));
            //we can use the short method for assigning ok
            return response()->json([
                'status' => 'succes',
                'data' => [
                    'user_name' => $user->name,
                    'email' => $user->email,
                    'role_id' => $user->role_id
                ]
                // 'user' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                $e->getFile(),
                $e->getCode()
            ], 404);
        }
    }
    public function login(Request $request)
    {
        try {
            $email = $request->email;
            $password = $request->password;
            if (!$email || empty($password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'enter credentials'
                ]);
            }
            $user = User::where('email', $email)->first();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'user does not exist with this email'
                ]);
            }
            if (!(Hash::check($password, $user->password))) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'invalid credentails'
                ]);
            }
            // dd([
            //     'app_timezone' => config('app.timezone'),
            //     'php_default'  => date_default_timezone_get(),
            //     'laravel_now'  => now()->toDateTimeString(),
            //     'utc_now'      => now('UTC')->toDateTimeString(),
            //     'karachi_now'  => now('Asia/Karachi')->toDateTimeString(),
            // ]);
            // exit;
            $payload = [
                'sub' => $user->id,
                'email' => $user->email,
                'iat' => time(),
                'exp' => time() + 24 * 60 * 60, // expire in one houre
                'jti' => (string) Str::uuid()
            ];
            $key = env('JWT_SECRET');
            $token = JWT::encode($payload, $key, 'HS256');

            $refreshPayload = [
                'sub' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'iat' => time(),
                'exp' => Carbon::now()->addDays(30)->timestamp,
                'jti' => (string) Str::uuid()
            ];
            $refreshToken = JWT::encode($refreshPayload, $key, 'HS256');
            $hashRefreshToken = Hash::make($refreshToken);
            $refreshTokenModel = new RefreshToken();
            $refreshTokenModel->user_id = $user->id;
            $refreshTokenModel->refresh_token = $hashRefreshToken;
            $refreshTokenModel->jti = $refreshPayload['jti'];
            $refreshTokenModel->device_type = $request->input('device_type', 'web');
            $refreshTokenModel->ip_address = $request->ip();
            // $refreshTokenModel->expire_at = Carbon::createFromTimestamp($refreshPayload['exp']);
            $refreshTokenModel->expire_at = Carbon::createFromTimestamp($refreshPayload['exp'])
                ->toDateTimeString();

            $userToken = new UserToken();
            $userToken->user_id = $user->id;
            $userToken->access_token = $token;
            $userToken->jti = $payload['jti'];
            $userToken->device_type = $request->input('device_type', 'web');
            $userToken->ip_address = $request->ip();
            // $userToken->expire_at = Carbon::createFromTimestamp($payload['exp']);
            // $userToken->created_by = $user->id;
            // $userToken->updated_by = $user->id;
            $userToken->expire_at = Carbon::createFromTimestamp($payload['exp'])
                ->toDateTimeString();

            $userToken->save();
            // var_dump($token);
            // exit;
            $refreshTokenModel->save();
            $data = [
                'user' => [
                    'token' => $token,
                    'refresh_token' => $refreshToken,
                    'token_expire_at' => Carbon::parse($userToken->expire_at)->setTimezone('Asia/Karachi')->toDateTimeString(),
                    'refresh_token_expire_at' => Carbon::parse($refreshTokenModel->expire_at)
                        ->setTimezone('Asia/Karachi')->toDateTimeString(),
                ]
            ];
            return response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'data' => $data
            ])->cookie(
                'refresh_token',
                $refreshToken,
                60 * 24 * 30,
                '/',
                null,
                false,
                true,
                false,
                'None'
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'something wrong please try again',
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                ],
                404
            );
        }
    }
    public function logout(Request $request)
    {
        try {
            $token = $request->bearerToken();
            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'please login '
                ]);
            }
            $userToken = UserToken::where('access_token', $token)->first();
            if (!$userToken) {
                return response()->json(['error' => 'Invalid token'], 401);
            }
            if ($userToken->expire_at < Now()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'token is expired'
                ]);
            }
            $user_id = $userToken->user_id;
            $refreshToken = RefreshToken::where('user_id', $user_id)->get();
            $getRefreshToken = null;
            foreach ($refreshToken as $r) {
                if (Hash::check($refreshToken, $r->refresh_token));
                $getRefreshToken = $r;
                break;
            }
            // print_r($getRefreshToken);
            BlacklistedAccessToken::create([
                'jti' => $userToken->jti ?? null,   // JWT ID
                'access_token' => $token,
                'user_id' => $userToken->user_id,
                'expire_at' => $userToken->expire_at,
            ]);
            $userToken->forceDelete();
            BlacklistedRefreshToken::Create(
                [
                    'jti' => $getRefreshToken->jti,
                    'refresh_token' => $getRefreshToken->refresh_token,
                    'user_id' => $getRefreshToken->user_id,
                    'expire_at' => $getRefreshToken->expire_at
                ]
            );
            $getRefreshToken->forceDelete();
            return response()->json(['message' => 'Logout successful'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 404);
        }
    }
    public function profile()
    {
        try {
            return response()->json([
                'status' => 'success',
                'message' => 'reached at profile'
            ]);
        } catch (\Exception $e) {
            return response()->json([

                'status' => 'error',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 404);
        }
    }
}
