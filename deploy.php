<?php

// doc in http://deployer.org/docs

require 'recipe/symfony.php';

// Symfony shared dirs
set('shared_dirs', ['app/logs']);

// Symfony shared files
set('shared_files', ['app/config/parameters.yml', 'web/uploads/documents']);

// Symfony writable dirs
set('writable_dirs', ['app/cache', 'app/logs']);

// Assets
set('assets', ['web/css', 'web/images', 'web/js']);

// Environment vars
env('env_vars', 'SYMFONY_ENV=prod');
env('env', 'prod');
set('keep_releases', 10);

task('install', function () {
    cd('{{deploy_path}}/current');
    run('composer update');
});

/**
 * Main task
 */
task('deploy', [
    'deploy:prepare',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:symlink',
    'cleanup',
    'install',
])->desc('Deploy Pronostics');

after('deploy', 'success');

server('prod', 'albator.dwf.fr', 22)
    ->user('pronostics-rugby')
    ->forwardAgent()
    ->identityFile()
    ->stage('production')
    ->env('deploy_path', '/var/www/clients/client3/web94/web/prod')
    ->env('branch', 'withoutsonatauser')
;

server('preprod', 'albator.dwf.fr', 22)
    ->user('pronostics-rugby')
    ->forwardAgent()
    ->identityFile()
    ->stage('pre-production')
    ->env('deploy_path', '/var/www/clients/client3/web94/web/preprod')
    ->env('branch', 'cleanup')
;

set('repository', 'git@github.com:michelcourtade/Pronostics.git');
