<?php
/**
 * Step 1: Require the Slim Framework
 *
 * If you are not using Composer, you need to require the
 * Slim Framework and register its PSR-0 autoloader.
 *
 * If you are using Composer, you can skip this step.
 */

include 'etc/config.php';

require 'vendor/autoload.php';
require 'src/Fluid/Fluid.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Fluid\Fluid;


$config = new \Config\Config();
// Create Slim app
$app = new \Slim\App($config->getConfig());

// create a log channel
$logger = new \Monolog\Logger($config->getConfig()["settings"]['logger']['name']);
$file_handler = new \Monolog\Handler\StreamHandler($config->getConfig()["settings"]['logger']['path']);
$logger->pushHandler($file_handler);

// Fetch DI Container
$container = $app->getContainer();
//Add Logger to Continer
$container['logger'] = $logger;
// Register Twig View helper
$container['view'] = function ($c) {
    $view = new \Slim\Views\Twig('templates', [
        'cache' => 'cache',
        'auto_reload' => true
    ]);

    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $c['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($c['router'], $basePath));

    return $view;
};

$container['manager'] = new Fluid($config->getConfig()["settings"]['db'], $container['logger']);

// GET route
$app->get('/', function ($request, $response, $args) {
    $this['logger']->addInfo('Site reached');
    return $this->view->render($response, 'index.html.twig', [
    ]);
})->setName('index');

// Define named route
$app->get('/add', function ($request, $response, $args) {
    return $this->view->render($response, 'create.html.twig', [

    ]);
})->setName('create');

$app->get('/list', function ($request, $response, $args) {
    return $this->view->render($response, 'list.html.twig', [
        'tasks' => $this->manager->listTasks()
    ]);
})->setName('list');

$app->post('/save', function ($request, $response, $args) {
    return $this->view->render($response, 'save.html.twig', [
        'saved' => $this->manager->save($request->getParams())
    ]);
})->setName('save');

$app->get('/edit/{id}', function ($request, $response, $args) {
    return $this->view->render($response, 'edit.html.twig', [
        'task' => $this->manager->getTask($args['id'])
    ]);
})->setName('edit');

$app->post('/update/{id}', function ($request, $response, $args) {
    return $this->view->render($response, 'save.html.twig', [
        'saved' => $this->manager->update($args['id'], $request->getParams())
    ]);
})->setName('update');

// DELETE route
$app->get( '/delete/{id}', function ($request, $response, $args) {
    return $this->view->render($response, 'save.html.twig', [
        'saved' => $this->manager->deleteTask($args['id'])
    ]);
    }
)->setName('delete');

/**
 * Step 4: Run the Slim application
 *
 * This method should be called last. This executes the Slim application
 * and returns the HTTP response to the HTTP client.
 */
$app->run();
