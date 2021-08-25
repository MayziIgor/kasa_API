<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
date_default_timezone_set('Europe/Kiev');
header("Access-Control-Allow-Origin: *");

use App\Controllers\AuthController;
use App\Controllers\Get_session_by_date_controll;
use App\Controllers\Get_tickets_by_session_controll;
use App\Controllers\Reserved_controll;
use App\Controllers\Confirm_controll;
use Bramus\Router\Router;

require '../vendor/autoload.php';

$router = new Router();

$router->setNamespace('App\Controllers');

$router->post('/Authenticate', function () {
    echo makeJson((new AuthController())->login());
});
$router->post('/Logout', function () {
    echo makeJson((new AuthController())->logout());
});
$router->post('/Get_session_by_date', function () {
    echo makeJson((new Get_session_by_date_controll())->Get_session_by_date());
});
$router->post('/Get_tickets_by_session', function () {
    echo makeJson((new Get_tickets_by_session_controll())->Get_tickets_by_session());
});
$router->post('/Reserved', function () {
    echo makeJson((new Reserved_controll())->Reserved());
});

$router->post('/Confirm', function () {
    echo makeJson((new Confirm_controll())->Confirm());
});

$router->run();
