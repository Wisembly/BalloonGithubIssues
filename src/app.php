<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/** 
* Get issues list
**/
$app->get('/', function (Request $request) use ($app) {
    $issues = $app['github']->getIssues($app['repo']['user'], $app['repo']['repo']);
    $milestones = $app['github']->getMilestones($app['repo']['user'], $app['repo']['repo']);

    if (isset($issues['message']) && sizeof($issues) == 1) {
        $request->getSession()->setFlash('warning', 'Issues not found or protected. Please log in with your GitHub credidentials');
        $issues = array();
        $milestones = array();
    }

    return $app['twig']->render('index.html.twig', array(
        'issues'        => $issues,
        'milestones'    => $milestones,
    ));
})
->bind('index');

/** 
* Close bookmarklet
**/
$app->get('/bookmarklet/{action}', function (Request $request, $action) use ($app) {
    switch($action){
        case 'remove':
            $params = "{'action':'remove'}";
        break;
    }
    return "<script type='text/javascript'>window.parent.postMessage($params, '*');</script>";
})
->assert('action', 'remove')
->bind('bookmarklet');

/** 
* Add an issue
**/
$app->match('/add', function (Request $request) use ($app) {

    if ($request->get('src') == 'bookmarklet.js' && false == $app['github']->isLogged()) {
        return new Response($app['bookmarklet']->render('login_form', $app['twig']->render('bookmarklet_login.html.twig', array())));
    }

    $userData = $app['github']->getUserData();

    foreach ($app['config']['repositories'] as $repository) {
        $repoInfo = $repository['user'] . '/' . $repository['repo'];
        $repositories[$repoInfo] = $repoInfo;
    }

    $defaultRepo = $app['repo']['user'] . '/' . $app['repo']['repo'];

    $form = $app['form.factory']->createBuilder('form');

    if (false != $app['config']['pending_repo'] && !in_array($app['user']['username'], $app['config']['pending_repo']['allowed_users'])) {
        $form = $form->add('repository', 'hidden', array('data' => $app['config']['pending_repo']['user'] . '/' . $app['config']['pending_repo']['repo']));
    } else {
        if (count($repositories) > 1) {
            $form = $form->add('repository', 'choice', array(
                'label'     => $app['translator']->trans('repository'),
                'choices'   => $repositories,
                'preferred_choices' => array($defaultRepo => $defaultRepo),
                'required'  => true,
            ));
        } else {
            $form = $form->add('repository', 'hidden', array('data' => array_pop($repositories)));
        }
    }

    $form = $form->add('issue', 'text', array(
                     'label'     => $app['translator']->trans('issue'),
                     'required'  => true,
                 ))
                 ->add('description', 'textarea', array(
                     'label'     => $app['translator']->trans('description'),
                     'required'  => false,
                 ))
                 ->add('fileUpload', 'file', array(
                     'label'     => $app['translator']->trans('fileupload'),
                     'required'  => false
                 ))
                 ->add('userData', 'hidden', array(
                     'required'  => false
                 ))
                 ->getForm();

    if ($request->getMethod() == 'POST') {
        $form->bindRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $files = $request->files->get($form->getName());
            $body = $data['description']."<br/>--------------------------<br/><i>".$data['userData']."</i>";

            if (isset($files['fileUpload']) && null !== $files['fileUpload']) {
                $filename = time().'_'.uniqid().'.'.$files['fileUpload']->guessExtension();
                $files['fileUpload']->move(__DIR__.'/../web/upload/', $filename);
                $fileUrl = $app['config']['base_url'].'/upload/'.$filename;
                $body .= "\n\n".'<img src="'.$fileUrl.'" alt="Included Screenshot" style="max-width: 712px;" /><br/>[See fullsize]('.$fileUrl.')';
            }

            $repoInfo = explode('/', $data['repository']);
            $user = $repoInfo[0];
            $repo = $repoInfo[1];
            
            $result = $app['github']->addIssue(
                $user,
                $repo,
                array(
                    'title'     => $data['issue'],
                    'body'      => $body,
                ));

            if (!empty($result) && !isset($result['message'])) {
                $request->getSession()->set('repo', array(
                    'user' => urldecode($user), 'repo' => urldecode($repo)
                ));

                if ($request->request->get('bookmarklet')) {
                    return $app->redirect($app['url_generator']->generate('bookmarklet',array('action'=>'remove')));
                } else {
                    $request->getSession()->setFlash('success', 'You successfully created your issue!');
                    return $app->redirect($app['url_generator']->generate('index'));
                }
            }
        }

        $request->getSession()->setFlash('error', 'Your issue has not been submitted: '.$result['message'].'!<br/>'.json_encode($result['errors']));
    }

    if ($request->query->get('src') == 'bookmarklet.js') {
        if (!$request->query->has('redirect')) {
            return new Response($app['bookmarklet']->render(
                'add_issue', 
                $app['twig']->render('add.html.twig', array('form' => $form->createView(), 'bookmarklet' => true)), 
                array('avatar_url' => $userData['avatar_url'])
            ));
        } else {
            return new Response($app['bookmarklet']->render('redirect'));
        }
    } else {
        return $app['twig']->render('add.html.twig', array(
            'form'          => $form->createView(),
            'issue'         => $request->request->get('issue', null),
            'description'   => $request->request->get('description', null),
        ));
    }
})
->bind('add');

/** 
* Change repo
**/
$app->get('/change/{user}/{repo}', function (Request $request, $user, $repo) use ($app) {
    if (null != $user && null != $repo) {
        $request->getSession()->set('repo', array('user' => urldecode($user), 'repo' => urldecode($repo)));
    }

    return $app->redirect($app['url_generator']->generate('index'));
})
->bind('change');

/** 
* Approve an issue in temp repository
**/
$app->get('/approve/{user}/{repo}/{id}', function(Request $request, $user, $repo, $id) use ($app) {
    $pending_issue = $app['github']->getIssue($app['config']['pending_repo']['user'], $app['config']['pending_repo']['repo'], $id);

    if (isset($pending_issue['message'])) {
        $request->getSession()->setFlash('error', 'Error while trying to approve this issue (#1)');
        return $app->redirect($app['url_generator']->generate('index'));
    }

    $result = $app['github']->addIssue($user, $repo, array(
            'title'     => $pending_issue['title'],
            'body'      => $pending_issue['body'],
    ));

    if (isset($result['message'])) {
        $request->getSession()->setFlash('error', 'Error while trying to approve this issue (#2)');
        return $app->redirect($app['url_generator']->generate('index'));
    }

    $result = $app['github']->closeIssue($app['config']['pending_repo']['user'], $app['config']['pending_repo']['repo'], $id);

    if (isset($result['message'])) {
        $request->getSession()->setFlash('error', 'Error while trying to approve this issue (#3)');
        return $app->redirect($app['url_generator']->generate('index'));
    }

    $request->getSession()->setFlash('success', 'You successfully approved this issue!');
    return $app->redirect($app['url_generator']->generate('index'));
})
->bind('approve');

/**
 * Close an issue
 * 
 * @param Request $request
 * @param string $user
 * @param string $repo
 * @param integer $id
 */
$app->get('/close/{user}/{repo}/{id}', function(Request $request, $user, $repo, $id) use ($app) {
    $result = $app['github']->closeIssue($user, $repo, $id);
    if (isset($result['message'])) {
        $request->getSession()->setFlash('error', $result['message']);
    } else {
        $request->getSession()->setFlash('success', 'You successfully closed this issue!');
    }
    
    return $app->redirect($app['url_generator']->generate('index'));
})->bind('close');

/** 
* Log in
**/
$app->get('/login', function (Request $request) use ($app) {

    if ($request->request->has('bookmarklet')) {
        if (true === $app['github']->login($request->request->get('username'), $request->request->get('password'))) {
            $request->getSession()->set(
                'user', array(
                'username' => $request->request->get('username'),
                'password' => $request->request->get('password'),
            ));
        }
        return new Response($app['bookmarklet']->render('login'));
    }

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

/** 
* Log out
**/
$app->get('/logout', function (Request $request) use ($app) {
    $app['user'] = null;
    $request->getSession()->set('user', null);
    $request->getSession()->setFlash('success', 'You successfully logged out!');
    if ($request->query->has('bookmarklet')) {
        return new Response($app['bookmarklet']->render('logout'));
    }
    return $app->redirect($app['url_generator']->generate('index'));
})
->bind('logout');

/** 
* Manage logged user session
**/
$app->before(function(Request $request) use ($app) {
    /* Translations management */
    if ($app['config']['locale'] && isset($app['translator.messages'][$app['config']['locale']])) {
        $app['locale'] = $app['config']['locale'];
    }

    /* User management */
    $app['user'] = $request->getSession()->get('user', null);
    $repo = false == $app['config']['pending_repo'] ? array(
        'user'  =>  $app['config']['repositories'][0]['user'],
        'repo'  =>  $app['config']['repositories'][0]['repo'],
    ) : array(
        'user'  =>  $app['config']['pending_repo']['user'],
        'repo'  =>  $app['config']['pending_repo']['repo'],
    );

    $app['repo'] = $request->getSession()->get('repo', $repo);

    if (null !== $app['user'] && false === $app['github']->login($app['user']['username'], $app['user']['password'])) {
        $request->getSession()->setFlash('error', 'Bad credidentials');
        $request->getSession()->set('user', null);
    }

    return;
});

return $app;