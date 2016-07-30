<?php

error_reporting(E_ALL ^ E_NOTICE);
if (function_exists('xdebug_disable')) {
	//xdebug_disable();
}

require_once 'src/Psr4Autoloader.php';
$loader = new \Reporter\Psr4Autoloader;
$loader->register();
$loader->addNamespace('Reporter', 'src');
$loader->addNamespace('Reporter', 'tests');

$longopts  = array(
    "config::",
    "test::",
);
$options = getopt('', $longopts);

$config_file = isset($options['config']) ? $options['config']: 'config.ini';
$config = parse_ini_file($config_file);
$config['include_base'] = rtrim(dirname(__FILE__), '/') . '/';
chdir($config['include_base']);

if (isset($options['test'])) {
	$config['test_file'] = $options['test'];
}

if (isset($config['timezone'])) {
	date_default_timezone_set($config['timezone']);
}

if (isset($config['php_mailer_location']) && !is_readable($config['php_mailer_location'])) {
	trigger_error('PHPMailer include location could not found or is not readable.', E_USER_ERROR);
}
else if (isset($config['php_mailer_location']) && $config['php_mailer_location']) {
	require_once $config['php_mailer_location'];
}

$reporter = new \Reporter\Reporter($config);
$reporter->run();
