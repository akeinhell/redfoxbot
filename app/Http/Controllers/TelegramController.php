<?php

namespace App\Http\Controllers;

use App\Exceptions\NoQuestSelectedException;
use App\Exceptions\TelegramCommandException;
use App\Helpers\Guzzle\Exceptions\NotAuthenticatedException;
use App\Telegram\Bot;
use App\Telegram\Commands\StartCommand;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Log;
use TelegramBot\Api\HttpException;

class TelegramController extends Controller
{

    public function setup()
    {
        $result = [];
        try {
            $result['status'] = Bot::action()->call('setWebhook', [
                'url'             => \URL::to('hook'),
                'max_connections' => 100,
                'allowed_updates' => json_encode(['message', 'callback_query']),
            ]);
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

    public function healthCheck()
    {
        $health = Bot::action()->call('getWebhookInfo');
        if ($errorDate = array_get($health, 'last_error_date')) {
            $health['last_error_date'] = Carbon::createFromTimestamp($errorDate)->toAtomString();
        }

        return response()->json($health);
    }

    public function newhook()
    {
        $bot = Bot::getClient();

        try {
            $bot->run();
        } catch (NoQuestSelectedException|NotAuthenticatedException|TelegramCommandException $e) {
            Bot::sendMessage($e->getChatid(), $e->getMessage());
        } catch (HttpException $e) {
            Log::warning(get_class($e) . implode(PHP_EOL, [
                    'message' => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                ]));
            app('sentry')->captureException($e);
        } catch (\Exception $e) {
            Log::critical(get_class($e) . implode(PHP_EOL, [
                    'message' => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                ]));
            app('sentry')->captureException($e);
        }

        return response('ok', 200);
    }
}
