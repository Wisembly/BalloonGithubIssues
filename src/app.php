<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

    if ($request->get('src') == 'bookmarklet.js' && false == $app['github']->isLogged()) {

        $view = $app['twig']->render('bookmarklet_login.html.twig', array());

        return new Response("var a=".json_encode($view).";  document.getElementsByTagName('body')[0].innerHTML=a;
        var style = document.createElement('link');
        style.setAttribute('type','text/css');
        style.setAttribute('href','http://dev/BalloonGithubIssues/web/css/bootstrap_1.4.0.min.css');
        style.setAttribute('rel','stylesheet');
        document.getElementsByTagName('html')[0].setAttribute('style','overflow-y:hidden;')
        document.getElementsByTagName('head')[0].insertBefore(style); ");
    }

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
                $body .= "\n\n".'[Included Screenshot]('.$fileUrl.')';
            }

            $result = $app['github']->addIssue(
                $app['repo']['user'],
                $app['repo']['repo'],
                array(
                    'title'     => $data['issue'],
                    'body'      => $body,
                ));

            if (!empty($result) && !isset($result['message'])) {
                if($request->request->get('bookmarklet')){
                    return new Response("
                    <script type='text/javascript'>
                    var js = document.createElement('script');
                    js.setAttribute('type','text/javascript');  
                    js.setAttribute('src','http://dev/BalloonGithubIssues/web/add?src=bookmarklet.js');
                    document.getElementsByTagName('head')[0].appendChild(js);
                    </script>
                    ");
                } else {
                    $request->getSession()->setFlash('success', 'You successfully created your issue!');
                    return $app->redirect($app['url_generator']->generate('index'));
                }
            }
        }

        $request->getSession()->setFlash('error', 'Your issue has not been submitted: '.$result['message'].'!<br/>'.json_encode($result['errors']));
    }
    
        if ($request->query->get('src') == 'bookmarklet.js') {
            $iframeid = $request->query->get('iframeid');
            if(!$request->query->has('redirect')) {
                $f =  $app['twig']->render('bookmarklet_add_html.twig', array(
                    'form'          => $form->createView(),
                    'issue'         => $request->request->get('issue', null),
                    'description'   => $request->request->get('description', null),
                )); 
                return new Response("
                var a=".json_encode($f).";
                document.getElementsByTagName('body')[0].innerHTML=a; 
                var style = document.createElement('link');
                style.setAttribute('type','text/css');
                style.setAttribute('href','http://dev/BalloonGithubIssues/web/css/bootstrap_1.4.0.min.css');
                style.setAttribute('rel','stylesheet');
                document.getElementsByTagName('html')[0].setAttribute('style','overflow-y:hidden;')
                document.getElementsByTagName('head')[0].insertBefore(style);
                ");
            } else {
                return new Response("
                <script type='text/javascript'>
                var js = document.createElement('script');
                js.setAttribute('type','text/javascript');  
                js.setAttribute('src','http://dev/BalloonGithubIssues/web/add?src=bookmarklet.js&iframeid=20');
                document.getElementsByTagName('head')[0].appendChild(js);
                </script>
            ");
            }
        } else {

    return $app['twig']->render('add.html.twig', array(
        'form'          => $form->createView(),
        'issue'         => $request->request->get('issue', null),
        'description'   => $request->request->get('description', null),
    )); }
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

    if($request->request->has('bookmarklet')) {
        if (true === $app['github']->login($request->request->get('username'), $request->request->get('password'))) {
            $request->getSession()->set(
                'user', array(
                'username' => $request->request->get('username'),
                'password' => $request->request->get('password'),
            ));
        }
        return new Response("
        <script type='text/javascript'>
        var js = document.createElement('script');
        js.setAttribute('type','text/javascript');  
        js.setAttribute('src','http://dev/BalloonGithubIssues/web/add?src=bookmarklet.js&iframeid=20');
        document.getElementsByTagName('head')[0].appendChild(js);
        </script> 
        ");
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

// log out
$app->get('/logout', function (Request $request) use ($app) {
    $app['user'] = null;
    $request->getSession()->set('user', null);
    $request->getSession()->setFlash('success', 'You successfully logged out!');
    if($request->query->has('bookmarklet')) {
        return new Response("
                    <script type='text/javascript'>
                        var js = document.createElement('script');
                        js.setAttribute('type','text/javascript');  
                        js.setAttribute('src','http://dev/BalloonGithubIssues/web/add?src=bookmarklet.js&iframeid=20');
                        document.getElementsByTagName('head')[0].appendChild(js);
                    </script> ");
    }
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
