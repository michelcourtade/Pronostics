<?php

// doc in http://deployer.org/docs

require 'recipe/common.php';

set('keep_releases', 10);

task('install', function () {
    cd('{{deploy_path}}/current');
    run('composer install');
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
    ->identityFile()
    ->forwardAgent()
    ->stage('production')
    ->env('deploy_path', '/var/www/clients/client3/web94/web/prod')
    ->env('branch', 'develop')
;

server('preprod', 'albator.dwf.fr', 22)
    ->user('pronostics-rugby')
    ->forwardAgent()
    ->identityFile()
    ->stage('pre-production')
    ->env('deploy_path', '/var/www/clients/client3/web94/web/preprod')
    ->env('branch', 'cleanup')
;

set('repository', 'git@github.com:michelcourtade/pronostics.git');
