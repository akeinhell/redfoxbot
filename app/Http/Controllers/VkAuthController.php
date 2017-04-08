<?php

namespace App\Http\Controllers;

use App\User;
use Auth;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use URL;

class VkAuthController extends Controller
{
    const VK_API_OAUTH = 'https://oauth.vk.com/';
    const VK_API       = 'https://api.vk.com/';
    const METHOD_AUTH  = 'authorize';
    const METHOD_INFO  = 'method/users.get';
    const METHOD_TOKEN = 'access_token';

    const client_id = 5278042;

    private static $REDIRECT_URL = '/auth/login';
    private $client;

    /**
     * VkAuthController constructor.
     */
    public function __construct()
    {
        $this->client       = new Client();
        self::$REDIRECT_URL = route('login');
    }

    public function auth(Request $request)
    {
        $code = $request->input('code');
        if ($code) {
            list($token, $email) = $this->getToken($code);
            $user                = $this->getUser($token);
            if ($this->authUser($user, $token, $email)) {
                return redirect('/');
            }
            throw new \Exception('Error Auth user', 1);
        }

        $params = [
            'client_id'     => self::client_id,
            'redirect_uri'  => URL::to(self::$REDIRECT_URL),
            'display'       => 'page',
            'response_type' => 'code',
            'v'             => '5.50',
            'scope'         => 'email, offline',
        ];

        $url = $this->build_url(self::VK_API_OAUTH . self::METHOD_AUTH, $params);

        return redirect($url);
    }

    private function getToken($code)
    {
        $params = [
            'client_id'     => env('VK_API_CLIENT_ID'),
            'client_secret' => env('VK_API_CLIENT_SECRET'),
            'redirect_uri'  => URL::to(self::$REDIRECT_URL),
            'code'          => $code,
            //'scope'=>'email'
        ];
        $url    = self::VK_API_OAUTH . self::METHOD_TOKEN . '?' . http_build_query($params);
        $data   = (string) $this->client->get($url, ['query' => $params])->getBody();
        $data   = json_decode($data);
        if (!isset($data->access_token)) {
            throw new \Exception('Error Processing Request', __LINE__);
        }

        return [$data->access_token, $data->email];
    }

    private function getUser($access_token)
    {
        $params = [
            'v'            => '5.50',
            'access_token' => $access_token,
            'fields'       => implode(',', ['photo_200', 'nickname', 'email']),
        ];
        $url      = 'https://api.vk.com/method/users.get';
        $response = (string) $this->client->get($url, ['query' => $params])->getBody();
        $data     = json_decode($response);

        return $data->response[0];
    }

    private function authUser($userData, $access_token, $email)
    {
        $photo = $userData->photo_200;
        unset($userData->photo_200);
        $user = User::find($userData->id);
        if ($user) {
            $user->setRawAttributes((array) $userData);
            $user->name         = $userData->first_name . ' ' . $userData->last_name;
            $user->access_token = $access_token;
            $user->photo        = $photo;
            $user->save();
        } else {
            $user               = new User((array) $userData);
            $user->id           = $userData->id;
            $user->name         = $userData->first_name . ' ' . $userData->last_name;
            $user->access_token = $access_token;
            $user->photo        = $photo;
            $user->email        = $email;
            $user->save();
        }

        Auth::login($user, true);

        return Auth::check();
    }

    /**
     * @param string $url
     */
    private function build_url($url, $params)
    {
        return $url . '?' . http_build_query($params);
    }
}
