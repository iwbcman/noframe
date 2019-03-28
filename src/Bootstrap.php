<?php
namespace App;
use Http\HttpRequest;
use Http\HttpResponse;
require __DIR__ . '/../vendor/autoload.php';

error_reporting(E_ALL);
$environment = 'development';
/**
 * Register the error handler
 */
$whoops = new \Whoops\Run;
if ($environment !== 'production') {
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
} else {
    $whoops->pushHandler(function ($e) {
        echo 'Todo: Friendly error page and send an email to the developer';
    });
}
$whoops->register();
//throw new \Exception;
$request = new HttpRequest($_GET, $_POST, $_COOKIE, $_FILES, $_SERVER);
$response = new HttpResponse;
//$content = '<h1>Hellow World</h1>';
/**
$dispatcher = \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) {
   $r->addRoute('GET', '/hello-world', function (){
       echo 'Hello World';
   });
});
 *
 */
$routeDefinitionCallback = function (\FastRoute\RouteCollector $r) {
  $routes = include ('Routes.php');
  foreach ($routes as $route) {
      $r->addRoute([0], $route[1], $route[2]);
  }
};
$dispatcher = \FastRoute\simpleDispatcher($routeDefinitionCallback);
$routeInfo = $dispatcher->dispatch($request->getMethod(), $request->getPath());
switch ($routeInfo[0]) {
    case \FastRoute\Dispatcher::NOT_FOUND:
        $response->setContent('404 - Page not found');
        $response->getStatusCode(404);
        break;
    case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $response->setContent('405 - Method not allowed');
        $response->getStatusCode(405);
        break;
    case \FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        call_user_func($handler, $vars);
        break;
}
//$response->setContent($content);
foreach ($response->getHeaders() as $header) {
    header($header, false);
}
echo $response->getContent();