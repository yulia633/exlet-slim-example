<?php

// Подключение автозагрузки через composer
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;
use App\Validator;
use App\Repository;

// Список пользователей
// Каждый пользователь – ассоциативный массив
// следующей структуры: id, firstName, lastName, email
$users = App\Generator::generate(100);

$container = new Container();
$container->set('renderer', function () {
    // Параметром передается базовая директория, в которой будут храниться шаблоны
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});

AppFactory::setContainer($container);
$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);
$router = $app->getRouteCollector()->getRouteParser();

$app->get('/', function ($request, $response) {
    return $this->get('renderer')->render($response, 'index.phtml');
});

$app->get('/users', function ($request, $response) use ($users) {
    $page = $request->getQueryParam('page', 1);
    $per = $request->getQueryParam('per', 5);
    $offset = ($page - 1) * $per;

    $sliceUsers = array_slice($users, $offset, $per);

    $search = $request->getQueryParam('search');
    if ($search) {
        $sliceUsers = collect($users)->filter(function ($user) use ($search) {
            return stripos($user['firstName'], $search) !== false;
        })->toArray();
    }
    $params = [
        'users' => $sliceUsers,
        'search' => $search,
        'page' => $page
    ];
    return $this->get('renderer')->render($response, 'users/index.phtml', $params);
});

$app->get('/users/new', function ($request, $response) {
    $params = [
        'users' => [
            'nickname' => '',
            'email' => '',
            'id' => '',
        ],
        'errors' => [],
    ];
    return $this->get('renderer')->render($response, 'users/new.phtml', $params);
});

$app->post('/users', function ($request, $response) use ($router) {
    $validator = new Validator();
    $repo = new Repository();

    $user = $request->getParsedBodyParam('user');
    $errors = $validator->validate($user);

    if (empty($errors)) {
        $repo->add($user);
        return $response->withRedirect('/users', 302);
    }
    $params = [
        'users' => $user,
        'errors' => $errors
    ];
    return $this->get('renderer')->render($response, 'users/new.phtml', $params);
});

$app->get('/users/{id}', function ($request, $response, $args) use ($users) {
    $id = $args['id'];
    $user = collect($users)->firstWhere('id', $id);
    $params = ['user' => $user];
    return $this->get('renderer')->render($response, 'users/show.phtml', $params);
});

$app->run();
