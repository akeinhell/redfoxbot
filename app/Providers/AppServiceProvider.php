<?php

namespace App\Providers;

use Blade;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        try {
            $versionFile = \File::get(base_path('stats.json'));
            $stats = \GuzzleHttp\json_decode($versionFile, true);
        } catch (InvalidArgumentException|FileNotFoundException $e) {
            $stats = [];
        }

        Blade::directive('asset', function ($params) use ($stats) {
            $args = explode(', ', str_replace(['(', ')'], '', $params));
            list($asset, $type) = array_pad($args, 2, '');
            $key = $asset . '.' . $type;
            $fileName = array_get($stats, $key);
            switch ($type) {
                    case 'js':
                        $template = '<script src=\'%s\'></script>';
                        break;
                    case 'css':
                        $template = '<link rel=\"stylesheet\" href=\'%s\'/>';
                        break;
                    default:
                        $template = null;
                }

            if ($template && $fileName && \File::exists(public_path($fileName))) {
                $string = sprintf($template, $fileName);
                return "<?php echo \"$string\"; ?>";
            }

            return null;
        });
    }

    /**
     * Register any application services.
     */
    public function register()
    {
    }
}
