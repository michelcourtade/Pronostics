<?php

// doc in http://deployer.org/docs

require 'recipe/common.php';


\Deployer\set('ssh_type', 'native');
\Deployer\set('ssh_multiplexing', true);

\Deployer\set('release_name', function () {
    // Set the deployment timezone
    if (!date_default_timezone_set(\Deployer\set('timezone', 'UTC'))) {
        date_default_timezone_set('UTC');
    }

    return date('YmdHis');
}); // name of folder in releases

// Symfony shared dirs
\Deployer\set('shared_dirs', ['app/logs', 'web/uploads/documents']);

// Symfony shared files
\Deployer\set('shared_files', ['app/config/parameters.yml']);

// Symfony writable dirs
\Deployer\set('writable_dirs', ['app/cache', 'app/logs']);

// Assets
\Deployer\set('assets', ['web/css', 'web/images', 'web/js']);

\Deployer\set('keep_releases', 10);

\Deployer\task('install', function () {
    \Deployer\cd('{{deploy_path}}/current');
    \Deployer\set('env', [
        'PHP_VERSION' => '/usr/bin/php7.2',
    ]);
    \Deployer\run('make configure', ['timeout' => 600]);
});

\Deployer\after('deploy:symlink', 'cachetool:clear:opcache');
\Deployer\task('cachetool:clear:opcache', function () {
    \Deployer\run('sudo /usr/sbin/apachectl graceful');
});
/**
 * Main task
 */
\Deployer\task('deploy', [
    'deploy:prepare',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:symlink',
    'cleanup',
    'install',
])->desc('Deploy YouBetSport');

\Deployer\after('deploy', 'success');

\Deployer\inventory('hosts.yml');

\Deployer\set('repository', 'https://github.com/michelcourtade/Pronostics.git');
