<?php

namespace App\Http\Controllers;

use App\Telegram\Bot;
use App\Telegram\Commands\StartCommand;
use Illuminate\Http\Request;

class TelegramController extends Controller
{

    public function setup()
    {
        $result = [];
        try {
            $result['status'] = Bot::action()->setWebhook(\URL::to('hook'));
        } catch (\Exception $e) {
            $result['message'] = $e->getMessage();
            $result['status']  = false;
        }

        return response()->json($result);
    }

    public function generateToken(Request $request)
    {
        $data = $request->all();

        $token = sha1(http_build_query($data));

        $expire = 60 * 10;

        \Cache::put(StartCommand::CACHE_KEY_START . $token, json_encode($data), $expire);

        return response()->json(['token' => $token]);
    }

    public function newhook()
    {
        header("HTTP/1.1 202");
        ob_flush();
        flush();

        $bot = Bot::getClient();

        $bot->run();

        return response('ok', 200);
    }
}
