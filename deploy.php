<?php
namespace Deployer;

require 'recipe/common.php';

set('default_stage', 'prod');

host('todonime.ru')
	->stage('prod')
	->user('deploy')
	->identityFile('~/.ssh/id_rsa-deploy')
	->set('repository', 'https://github.com/grutenko/gamercorn_bot.git')
	->set('branch', 'master')
	->set('deploy_path', '/srv/gamercorn_bot/')
	->set('shared_files', [
		'.env',
		'composer.lock'
	])
	->set('shared_dirs', [
		'vendor'
	]);

task('deploy:composer', "cd {{release_path}} && composer install --no-dev");
task('deploy:js', "cd {{release_path}}/client && npm run build");
task('deploy:server-restart', "cd {{release_path}} && php bin/bot.php stop && php bin/bot.php start -d");


task('deploy', [
	'deploy:info',
	'deploy:prepare',
	'deploy:lock',
	'deploy:release',
	'deploy:update_code',
	'deploy:shared',
	'deploy:writable',
	'deploy:clear_paths',
	'deploy:symlink',
	'deploy:composer',
	'deploy:js',
	'deploy:unlock',
	'cleanup',
	'success',
	'deploy:server-restart'
]);

after('deploy:failed', 'deploy:unlock');