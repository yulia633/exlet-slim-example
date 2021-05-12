<?php

// Подключение автозагрузки через composer
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Slim\Middleware\MethodOverrideMiddleware;
use DI\Container;
use App\Validator;
use App\Repository;

// Старт сессии PHP
session_start();

// Список пользователей
// Каждый пользователь – ассоциативный массив
// следующей структуры: id, firstName, lastName, email
$users = App\Generator::generate(100);

$container = new Container();
$container->set('renderer', function () {
    // Параметром передается базовая директория, в которой будут храниться шаблоны
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});
$container->set('flash', function () {
    return new \Slim\Flash\Messages();
});

AppFactory::setContainer($container);
$app = AppFactory::create();
$app->add(MethodOverrideMiddleware::class);
$app->addErrorMiddleware(true, true, true);
$router = $app->getRouteCollector()->getRouteParser();

$app->get('/', function ($request, $response) {
    return $this->get('renderer')->render($response, 'users/index.phtml');
})->setName('index');

$app->get('/users', function ($request, $response) {
    $json = new Repository('users');

    $allUsers = $json->select();
    $flash = $this->get('flash')->getMessages();
    $page = $request->getQueryParam('page', 1);
    $per = $request->getQueryParam('per', 5);
    $offset = ($page - 1) * $per;


    $sliceUsers = array_slice($allUsers, $offset, $per);

    $search = $request->getQueryParam('search');
    
    
    $users = empty($search) 
        ? $sliceUsers
        : array_filter($allUsers, function ($user) use ($search) {
            $nickname = $user['nickname'];
            $value = mb_strpos($nickname, $search);

            return is_numeric($value);
        });

    $params = [
        'users' => $users,
        'search' => $search,
        'page' => $page,
        'flash' => $flash,
    ];
    return $this->get('renderer')->render($response, 'users/index.phtml', $params);
})->setName('users');

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
})->setName('new');

$app->post('/users', function ($request, $response) use ($router) {
    $validator = new Validator();
    $repo = new Repository('users');

    $user = $request->getParsedBodyParam('user');

    $errors = $validator->validate($user);

    if (empty($errors)) {
        $data = ['nickname' => $user['nickname'], 'email' => $user['email']];
        $users = $repo->add($data);

        $url = $router->urlFor('users');
        $this->get('flash')->addMessage('success', 'User has been added');
        return $response->withRedirect($url, 302);
    }
    $params = [
        'users' => $users,
        'errors' => $errors
    ];
    return $this->get('renderer')->render($response->withStatus(422), 'users/new.phtml', $params);
});

$app->get('/users/{id}', function ($request, $response, $args) {
    $repo = new Repository('users');
    $id = $args['id'];
    
    [$user] = $repo->select(['id' => $id]);
    $params = [
        'user' => $user,
    ];
    return $this->get('renderer')->render($response, 'users/show.phtml', $params);
})->setName('user');


$app->get('/users/{id}/edit', function ($request, $response, $args) {
    $repo = new Repository('users');
    $id = $args['id'];
    $user = $repo->select(['id' => $id]);

    $messages = $this->get('flash')->getMessages();
    
    $params = [
        'user' => $user,
        'errors' => [],
        'flash' => $messages
    ];
    return $this->get('renderer')->render($response, 'users/edit.phtml', $params);
});



$app->patch('/users/{id}', function ($request, $response, array $args) use ($router) {
    $id = $args['id'];
    $repo = new Repository('users');

    
    $user = $repo->findById($id);

    $data = $request->getParsedBodyParam('user');

    $validator = new Validator();
    $errors = $validator->validate($data);
    
    $formData['nickname'] = $data['nickname'];
    $formData['email'] = $data['email'];
    
    $repo->update($id, $formData);
    
        $this->get('flash')->addMessage('success', 'User has been updated');

        $url = $router->urlFor('users');

        return $response->withRedirect($url);
    

    $params = [
        'user' => $user,
        'data' => $data,
        'errors' => $errors,
        'flash' => [],
    ];

    $response = $response->withStatus(422);
    return $this->get('renderer')->render($response, 'users/edit.phtml', $params);
});

$app->delete('/users/{id}', function ($request, $response, array $args) use ($router) {
    $repo = new Repository('users');

    $id = $args['id'];
    $repo->destroy(['id' => $id]);

    $url = $router->urlFor('users');

    $this->get('flash')->addMessage('success', 'User has been deleted');
    return $response->withRedirect($url);
});

$app->run();


$users = [
    'asdklfj' => [
        'id' => 'asdklfj',
        'name' => 'test',
        'email' => 'test',
    ]
];
