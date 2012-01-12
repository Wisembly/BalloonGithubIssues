<?php
require_once __DIR__.'/app/bootstrap.php';
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app->get('/', function (Request $request) use ($app) {
    $issues = $app['github']->getIssues($app['repo']['user'], $app['repo']['repo']);

    if (isset($issues['message']) && sizeof($issues) == 1) {
        $request->getSession()->setFlash('warning', 'Issues not found or protected. Please log in with your GitHub credidentials');
        $issues = array();
    }

    return $app['twig']->render('index.html.twig', array(
        'issues'    => $issues,
    ));
})
->bind('index');

$app->get('/add/{issue}', function (Request $request, $issue) use ($app) {
    
})
->bind('add');

$app->get('/change/{user}/{repo}', function (Request $request, $user, $repo) use ($app) {
    if (null != $user && null != $repo) {
        $request->getSession()->set('repo', array('user' => urldecode($user), 'repo' => urldecode($repo)));
    }

    return $app->redirect($app['url_generator']->generate('index'));
})
->bind('change');

$app->get('/login', function (Request $request) use ($app) {
    if (true === $app['github']->login($request->request->get('username'), $request->request->get('password'))) {
        $request->getSession()->set(
            'user', array(
                'username' => $request->request->get('username'),
                'password' => $request->request->get('password'),
            ));
        $request->getSession()->setFlash('success', 'Hoody '.ucFirst($request->request->get('username')).' !');
    } else {
        $request->getSession()->set('user', null);
        $request->getSession()->setFlash('error', 'Bad credidentials');
    }

    return $app->redirect($app['url_generator']->generate('index'));
})
->bind('login')
->method('POST');

$app->get('/logout', function (Request $request) use ($app) {
    $app['user'] = null;
    $request->getSession()->set('user', null);
    $request->getSession()->setFlash('success', 'You successfully logged out!');
    return $app->redirect($app['url_generator']->generate('index'));
});

$app->before(function(Request $request) use ($app) {
    $app['user'] = $request->getSession()->get('user', null);
    $app['repo'] = $request->getSession()->get('repo', array(
        'user' => $app['config']['repositories'][0]['user'],
        'repo' => $app['config']['repositories'][0]['repo'],
    ));

    if (null !== $app['user'] && false === $app['github']->login($app['user']['username'], $app['user']['password'])) {
        $request->getSession()->setFlash('error', 'Bad credidentials');
        $request->getSession()->set('user', null);
    }

    return;
});

$app->run();