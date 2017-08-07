<?php
namespace Deployer;
require './vendor/deployer/deployer/recipe/laravel.php';


//set('branch', 'feature_track');
set('repository', 'git@github.com:akeinhell/redfoxbot.git');

add('shared_files', [
    '.env',
]);

// Laravel shared dirs
set('shared_dirs', [
    'storage',
    'node_modules',
    'bower_components'
]);

add('writable_dirs', [
    'storage',
]);

// Servers
$configMethod = getenv('CIRCLECI') ? 'configFile' : 'identityFile';
$configArgs = getenv('CIRCLECI') ? '~/.ssh/config' : '~/.ssh/id_rsa';

host('production')
    ->hostname('redfoxbot.ru')
    ->user('ubuntu')
    ->set('keep_releases', 5)
    ->set('deploy_path', '/var/www/telegram')
    ->{$configMethod}($configArgs);

task('npm', function () {
    run('cd ' . get('release_path'). ' && yarn install');
    run('cd ' . get('release_path'). ' && npm run build');
});

desc('Update version');
task('env', function(){
    run('cd ' . get('release_path'). ' && php artisan deploy:env');
});

desc('clear cache');
task('cache-clear', function(){
    run('cd ' . get('release_path'). ' && php artisan config:clear');
});

// Tasks
desc('Restart PHP-FPM service');
task('php-fpm:restart', function () {
    run('sudo systemctl restart php7.0-fpm.service');
    run('sudo systemctl restart php7.1-fpm.service');
});
after('deploy:vendors', 'npm');
after('npm', 'env');
after('deploy:symlink', 'php-fpm:restart');
after('php-fpm:restart', 'cache-clear');
