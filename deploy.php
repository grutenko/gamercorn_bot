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
task('deploy:bot-restart', "sudo systemctl restart gamercorn-bot");
task('deploy:server-restart', "cd {{release_path}} && php bin/server.php stop && php bin/server.php start -d")


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
	'deploy:unlock',
	'cleanup',
	'success',
	'deploy:server-restart',
	'deploy:bot-restart'
]);

after('deploy:failed', 'deploy:unlock');