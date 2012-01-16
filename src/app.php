<?php

use Symfony\Component\HttpFoundation\Request;

// issues list
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

// add an issue
$app->match('/add', function (Request $request) use ($app) {
    $form = $app['form.factory']->createBuilder('form') 
            ->add('issue', 'text', array(
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
        ->getForm();

    if ($request->getMethod() == 'POST') {
        $form->bindRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $files = $request->files->get($form->getName());
            $body = $data['description'];

            if (isset($files['fileUpload']) && null !== $files['fileUpload']) {
                $filename = time().'_'.uniqid().'.'.$files['fileUpload']->guessExtension();
                $files['fileUpload']->move(__DIR__.'/../web/upload/', $filename);
                $protocol = strpos(strtolower($request->server->get('SERVER_PROTOCOL')),'https') === false ? 'http' : 'https';
                $fileUrl = $protocol.'://'.$request->server->get('HTTP_HOST').$app['url_generator']->generate('index').'upload/'.$filename;
                $body .= "\n\n".'<img src="'.$fileUrl.'" alt="Included Screenshot" style="max-width: 712px;" /><br/>[See fullsize]('.$fileUrl.')';
            }

            $result = $app['github']->addIssue(
                $app['repo']['user'],
                $app['repo']['repo'],
                array(
                    'title'     => $data['issue'],
                    'body'      => $body,
                ));

            if (!empty($result) && !isset($result['message'])) {
                $request->getSession()->setFlash('success', 'You successfully created your issue!');
                return $app->redirect($app['url_generator']->generate('index'));
            }
        }

        $request->getSession()->setFlash('error', 'Your issue has not been submitted: '.$result['message'].'!<br/>'.json_encode($result['errors']));
    }

    return $app['twig']->render('add.html.twig', array(
        'form'          => $form->createView(),
        'issue'         => $request->request->get('issue', null),
        'description'   => $request->request->get('description', null),
    ));
})
->bind('add');

// change repo
$app->get('/change/{user}/{repo}', function (Request $request, $user, $repo) use ($app) {
    if (null != $user && null != $repo) {
        $request->getSession()->set('repo', array('user' => urldecode($user), 'repo' => urldecode($repo)));
    }

    return $app->redirect($app['url_generator']->generate('index'));
})
->bind('change');

// log in
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

// log out
$app->get('/logout', function (Request $request) use ($app) {
    $app['user'] = null;
    $request->getSession()->set('user', null);
    $request->getSession()->setFlash('success', 'You successfully logged out!');
    return $app->redirect($app['url_generator']->generate('index'));
})
->bind('logout');

// manage logged in user session
$app->before(function(Request $request) use ($app) {
    /* Translations management */
    if ($app['config']['locale'] && isset($app['translator.messages'][$app['config']['locale']])) {
        $app['locale'] = $app['config']['locale'];
    }

    /* User management */
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

return $app;
