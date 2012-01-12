<?php
require_once __DIR__.'/app/bootstrap.php';
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app->get('/', function (Request $request) use ($app) {
    $user = $request->getSession()->get('user', null);

    if (null !== $user && false === $app['github']->login($user['username'], $user['password'])) {
        $request->getSession()->setFlash('error', 'Bad credidentials');
        $request->getSession()->set('user', null);
    }

    $issues = $app['github']->getIssues('balloon', 'balloon4');

    if (isset($issues['message']) && sizeof($issues) == 1) {
        $request->getSession()->setFlash('warning', 'You must log in');
        $issues = array();
    }

    return $app['twig']->render('index.html.twig', array(
        'user'      => $user,
        'issues'    => $issues,
    ));
})
->bind('index');

$app->get('/login', function (Request $request) use ($app) {
    if (true === $app['github']->login($request->request->get('username'), $request->request->get('password'))) {
        $request->getSession()->set(
            'user', array(
                'username' => $request->request->get('username'),
                'password' => $request->request->get('password'),
            ));
    } else {
        $request->getSession()->set('user', null);
        $request->getSession()->setFlash('error', 'Bad credidentials');
    }

    return $app->redirect($app['url_generator']->generate('index'));
})
->bind('login')
->method('POST');

$app->get('/logout', function (Request $request) use ($app) {
    $request->getSession()->set('user', null);
    $request->getSession()->setFlash('success', 'You successfully logged out!');
    return $app->redirect($app['url_generator']->generate('index'));
});

$app->run();