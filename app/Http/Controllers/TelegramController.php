<?php

namespace App\Http\Controllers;

use App\Exceptions\NoQuestSelectedException;
use App\Exceptions\TelegramCommandException;
use App\Telegram\Bot;
use App\Telegram\Commands\StartCommand;
use Illuminate\Http\Request;
use Log;

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

    public function healthCheck() {
        $health = Bot::action()->call('getWebhookInfo');

        return response()->json($health);
    }

    public function newhook()
    {
        header("HTTP/1.1 202");
        ob_flush();
        flush();

        $bot = Bot::getClient();

        try {
            $bot->run();
        } catch (NoQuestSelectedException $e) {
                Bot::sendMessage($e->getChatid(), 'Не выбрано задание. Выберите его с помощью команды /select');
        } catch (TelegramCommandException $e) {
            Log::error(get_class($e) . implode(PHP_EOL, [
                    'message' => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                ]));
            Bot::sendMessage($e->getChatid(), $e->getMessage());
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
