<?php

require_once 'src/Psr4Autoloader.php';
$loader = new \Reporter\Psr4Autoloader;
$loader->register();
$loader->addNamespace('Reporter', 'src');
$loader->addNamespace('Reporter', 'tests');