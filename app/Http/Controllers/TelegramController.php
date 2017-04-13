<?php

namespace App\Http\Controllers;

use App\Exceptions\TelegramCommandException;
use App\Services\Redfoxbot\RedfoxbotService;
use App\Telegram\Commands\StartCommand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Redfoxbot;
use Symfony\Component\HttpFoundation\Response;
use Telegram\Bot\Objects\Update;

class TelegramController extends Controller
{
    /**
     * Устанавливает web-hooks
     * @return Response
     */
    public function setup(): Response
    {
        \Telegram::setWebhook([
            'url' => \URL::to('newbot'),
        ]);

        return response('Done', 201);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function generateToken(Request $request): JsonResponse
    {
        $data = $request->all();

        $token = sha1(http_build_query($data));

        $expire = 60 * 10;

        \Cache::put(StartCommand::CACHE_KEY_START . $token, json_encode($data), $expire);

        return response()->json(['token' => $token]);
    }

    /**
     * Основная точка входа сообщения из телеграмма
     * @return Response
     */
    public function newhook(): Response
    {
        /** @var Update $update */
        $update = \Telegram::commandsHandler(true);
        if ($update->has('text')) {
            try {
                Redfoxbot::parseUpdate($update);

                return response('ok', 201);
            } catch (TelegramCommandException $e) {
                Redfoxbot::sendMessage($update->getMessage()->getChat()->getId(), $e->getMessage());
            } catch (\Exception $e) {
                Log::error(__LINE__ . $e->getMessage(), $e->getTrace());
            }
        }

        return response('ok', 200);
    }
}
