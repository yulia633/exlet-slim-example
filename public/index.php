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
    return $this->get('renderer')->render($response, 'index.phtml');
})->setName('index');

$app->get('/users', function ($request, $response) {
    

    //$repo = new Repository();
    //$json = $repo->all('users'); //all

    //$users = $request->getParsedBodyParam('user');
    $json = new Repository('users');

    $allUsers = $json->select();//get arrayall
    // $users = $json->select();
   
    // $users = [];

    // foreach ($allUsers as $key => $value) {
    //    // var_dump($value) ;
    //     if (!array_key_exists($value, $users)) {
    //         $users[$key][] = $allUsers[$key];
    //     }
       
    // }

    // var_dump($users);die;



    //$users = $json->readFile();
  // var_dump($json->select(["id" => $id]));die;

    // $page = $request->getQueryParam('page', 1);
    // $per = $request->getQueryParam('per', 5);
    // $offset = ($page - 1) * $per;

    // $sliceUsers = [];
    // foreach ($allUsers as $user) {
    //     $sliceUsers[] = array_slice($user, $offset, $per);
    // }

 //  var_dump($allUsers);die;
    

    $flash = $this->get('flash')->getMessages();
    $page = $request->getQueryParam('page', 1);
    $per = $request->getQueryParam('per', 5);
    $offset = ($page - 1) * $per;


   $sliceUsers = array_slice($allUsers, $offset, $per);
   // var_dump($sliceUsers);die;

    $search = $request->getQueryParam('search');
    $filteredUsers = array_filter($allUsers, fn ($user) => is_numeric(strpos($user['nickname'] ?? " ", $search)));
    $users = $search ? $filteredUsers : $sliceUsers;

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

// $insertID = $json->insert(array('column' => 'value'));

  //$insertUser = $repo->add();
 // $insertName = $json->insert(array('name' => 'ADA', 'email' => 'email', 'id' => '3'));

  //$insertUser = $repo->add(array($name => 'name', $email => 'email', $id => 'id'));
  //$insertName2 = $json->add(array('name' => 'UUU', 'email' => 'u@rt', 'id' => '1'));

   //$user = $repo->add(array('id' => $id, 'nickname' => $nickname, 'email' => $email));

//     $insertUser = $repo->add(['nickname' => $nickname]);
   

   // $insertID = $repo->add(array('column' => 'value'));
    
  // var_dump($insertUser2); die;
  // var_dump($insertUser2); die;


    // $user = $request->getParsedBodyParam('user');
    $errors = $validator->validate($user);

    if (empty($errors)) {

        // $repo->add($user);
        //$user = $repo->add(['id' => $id]);
     //  $usersData = $repo->add(array('id' => $user['id'], 'nickname' => $user['nickname'], 'email' => $user['email']));
    // $usersData = $repo->add(array('name' => 'UUU', 'email' => 'u@rt', 'id' => '1'));  
    // var_dump($insertName2);die;

      // $usersData = $repo->add([]); //????

    //   $id = $user['id'];
    //   $nickname = $user['nickname'];
    //   $email = $user['email'];
      $data = ['id' => $user['id'], 'nickname' => $user['nickname'], 'email' => $user['email']];
   // var_dump($data);die;

    //  $insert = $repo->add(array('nickname' => $nickname, 'email' => $email, 'id' => $id));
    $users = $repo->add($data);
   //var_dump($users);die;

    //  $insertName2 = $json->insert(array('name' => 'UUU', 'email' => 'u@rt', 'id' => '1'));

     //  $repo->add(['nickname' => $nickname, 'email' => $email, 'id' => $id]);

        // $insertID = $json->insert(array('column' => 'value'));

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

    // var_dump($id);die;
    
    $users = $repo->select(['id' => $id]);
   //$users = $repo->select();
    //$users = $repo->select([]);
   // var_dump($users);die;

    // $users = $repo->select(array('id' => '6067288632d29'));
    // $users = $repo->select($users[$id]); //get
    $params = [
        'users' => $users,
    ];
    return $this->get('renderer')->render($response, 'users/show.phtml', $params);
})->setName('user');

// $app->get('/users/{id}/edit', function ($request, $response, $args) {
//     $repo = new Repository('users');
//     $id = $args['id'];

//     //$user = $repo->select($id); //get
//     $user = $repo->select(['id' => $id]);

//    // var_dump($user);die;

//     $messages = $this->get('flash')->getMessages();
//     $params = [
//         'user' => $user,
//         'userData' => $user,
//         'errors' => [],
//         'flash' => $messages
//     ];
//     return $this->get('renderer')->render($response, 'users/edit.phtml', $params);
// })->setName('editUser');

$app->get('/users/{id}/edit', function ($request, $response, $args) {
    $repo = new Repository('users');
    $id = $args['id'];
    $user = $repo->select(['id' => $id]);
  //  var_dump($user);die;

  $messages = $this->get('flash')->getMessages();

    // $id = $args['id'];
    // $users = getData();
    // $user = $users[$id];
    
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

    
   $user = $repo->select(['id' => $id]);

   $data = $request->getParsedBodyParam('user');

  

  

//    var_dump($data);
//    var_dump($id);


   $validator = new Validator();
   $errors = $validator->validate($data);

// if (is_null($data['id'])) {
//     echo 1;
//     $data['id'] = $id;
// }

var_dump($user);//die;

foreach ($user as $key => $value) {
   // $value['id'] = $data['id'];
    $value['nickname'] = $data['nickname'];
    $value['email'] = $data['email'];
    $user = $repo->add1($value);
}

// $user = $repo->add1($user);

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
