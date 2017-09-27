<?php

namespace App\Http\Controllers;

use App\Games\Engines\EncounterEngine;
use App\Telegram\Bot;
use Auth;
use Cache;
use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use View;

class SiteController extends Controller
{
    const NEWS_KEY = 'SITE_VK_NEWS';

    /**
     * SiteController constructor.
     */
    public function __construct()
    {
        $leftMenu = [
            route('index')    => 'Главная',
            route('settings') => 'Настройки бота',
            route('calendar') => 'Календарь игр',
        ];
        if (\Auth::user()) {
            $leftMenu[route('profile')] = 'Профиль';
            $leftMenu[route('logout')]  = 'Выход';
        } else {
            $leftMenu[route('login')] = 'Войти';
        }
        View::share('leftMenu', $leftMenu);
    }

    public function index()
    {
        try {
            $posts = $this->getLastNews();
        } catch (\Exception $e) {
            $posts = [];
        }

        return view('index')->with('posts', $posts)->with('title', 'Главная');
    }

    /**
     * @return array
     */
    public function getLastNews()
    {
        if ($news = \Cache::get(self::NEWS_KEY)) {
            return $news;
        }
        Carbon::setLocale('ru');
        $settings = [
            'domain'       => 'foxbot_project',
            'count'        => 10,
            'access_token' => env('VK_ACCESS_TOKEN'),
        ];
        $url      = 'https://api.vk.com/method/wall.get?' . http_build_query($settings);
        $dataRaw  = file_get_contents($url);

        $dataRaw = json_decode($dataRaw)->response;
        unset($dataRaw[0]);
        $posts = [];
        foreach ($dataRaw as $data) {
            $post = [
                'time'     => Carbon::createFromTimestamp($data->date),
                'title'    => 'Новость #' . (count($posts) + 1),
                'text'     => $data->text,
                'id'       => $data->id,
                'comments' => $data->comments->count,
                'likes'    => $data->likes->count,
                'reposts'  => $data->reposts->count,
                'images'   => [],
            ];
            if (isset($data->attachments)) {
                foreach ($data->attachments as $attachment) {
                    if ($attachment->type === 'photo') {
                        $post['images'][] = str_replace('http:/', '/vk/image', $attachment->photo->src_big);
                    }
                }
            }
            $posts[] = $post;
        }

        Cache::put(self::NEWS_KEY, $posts, 60 * 24);

        return $posts;
    }

    public function settings()
    {
        return view('page.settings')->with('title', 'Настройки');
    }

    public function vk()
    {
        return view('page.vk')->with('title', 'Настройки');
    }

    public function settingsNew()
    {
        return view('page.settings')->with('title', 'Настройки');
    }

    public function calendar()
    {
        return view('calend')->with('title', 'Календарь игр');
    }

    public function logout()
    {
        Auth::logout();

        return redirect('/');
    }

    public function profile()
    {
        return view('profile')->with('title', 'Профиль');
    }

    public function staticAction(...$args)
    {
        $disk = \Storage::disk('s3');
        $fileName = implode('/', $args);
        try {
            return $disk->get($fileName);
        } catch (FileNotFoundException $e) {
            $url = sprintf('http://%s', implode('/', $args));
            $content = file_get_contents($url);
            $disk->put($fileName, $content, 'public');

            return $content;
        }
    }
}
