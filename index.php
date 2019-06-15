<?php

use GuzzleHttp\Client;
use Symfony\Component\Console\Application;

require_once( "vendor/autoload.php" );
$app = new Application( "Laravel installer", '1.0' );
$app->add( new NewCommand( new Client( [ 'verify' => false ] ) ) );
$app->add( new ListsCommand() );
$app->run();