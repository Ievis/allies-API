<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\BadCredentionals;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('jwt.auth', ['except' => ['login', 'refresh', 'register', 'loginVK', 'loginCallbackVK']]);
    }

    public function register(RegisterRequest $request)
    {
        $data = $request->validated();
        $user = UserService::createUser($data);

        $token = auth()->login($user);
        return $this->respondWithToken($token);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();
        if (!$token = auth()->attempt($credentials)) throw new BadCredentionals();

        return $this->respondWithToken($token);
    }

    public function loginVK()
    {
        $client_id = env('VK_CLIENT_ID');
        $redirect_uri = env('SERVER_HOST') . '/api/v1/auth/vk/login/callback';
        $display = 'popup';
        $scope = 'friends,email,groups,offline';
        $response_type = 'code';
        $uri = 'https://oauth.vk.com/authorize?client_id=' . $client_id . '&redirect_uri=' . $redirect_uri
            . '&display=' . $display . '&scope=' . $scope . '&response_type=' . $response_type;

        return redirect()->away($uri);
    }

    public function loginCallbackVK(Request $request)
    {
        $code = $request->get('code');
        $error = $request->get('error');
        if ($error) return redirect()->route('login.vk');
        $response = Http::get('https://oauth.vk.com/access_token', [
            'client_id' => env('VK_CLIENT_ID'),
            'client_secret' => env('VK_CLIENT_SECRET'),
            'redirect_uri' => env('SERVER_HOST') . '/api/v1/auth/vk/login/callback',
            'code' => $code
        ])->json();

        $access_token = $response['access_token'] ?? null;
        $user_id = $response['user_id'] ?? null;

        if (empty($access_token) | empty($user_id)) return redirect()->route('login.vk');

        $response = Http::get('https://api.vk.com/method/users.get', [
            'access_token' => $access_token,
            'v' => '5.131',
            'fields' => 'photo_max_orig,email',
            'user_ids' => $user_id
        ])->json('response')[0];

        $user = User::where('vk_id', $response['id'])->withTrashed()->first();
        if (!empty($user->deleted_at)) {
            return redirect()->to('/');
        }

        $user = $user ?? UserService::createUser([
            'name' => $response['first_name'],
            'surname' => $response['last_name'],
            'image' => $response['photo_max_orig'],
            'vk_id' => $response['id'],
            'email' => $response['email'] ?? null,
        ]);
        DB::table('oauth2_tokens')->upsert([
            'type' => 'vk',
            'token_id' => $response['id'],
            'access_token' => $access_token,
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ], ['token_id'], ['access_token', 'updated_at']);

        $access_token = auth()->login($user);

        return redirect()->away(env('SERVER_HOST') . '/?access_token=' . $access_token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $user = auth()->user();
        $role = User::getRoleById($user['role_id']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'role' => $role,
                'email' => $user['email'],
                'image' => $user['image'],
            ]
        ])
            ->header('Charset', 'utf-8')
            ->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json([
            'success' => true,
            'message' => 'Вы успешно вышли из своего аккаунта'
        ])
            ->header('Charset', 'utf-8')
            ->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        if (!empty(auth()->user()->deleted_at)) {
            return response()->json([
                'success' => false,
                'message' => 'Ваш аккаунт заблокирован или удалён'
            ])
                ->header('Charset', 'utf-8')
                ->setEncodingOptions(JSON_UNESCAPED_UNICODE);;
        }
        return response()->json([
            'success' => true,
            'data' => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60
            ]
        ])
            ->header('Charset', 'utf-8')
            ->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }
}
