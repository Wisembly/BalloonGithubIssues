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
            return new Response("var a=".json_encode($view).";
                document.getElementsByTagName('body')[0].innerHTML=a;
                var style = document.createElement('link');
                style.setAttribute('type','text/css');
                style.setAttribute('href','http://dev/BalloonGithubIssues/web/bootstrap/bootstrap.css');
                style.setAttribute('rel','stylesheet');
                document.getElementsByTagName('head')[0].insertBefore(style);
                var style = document.createElement('link');
                style.setAttribute('type','text/css');
                style.setAttribute('href','http://dev/BalloonGithubIssues/web/css/bookmarklet_login.css');
                style.setAttribute('rel','stylesheet');
                document.getElementsByTagName('html')[0].setAttribute('style','overflow-y:hidden;')
                document.getElementsByTagName('head')[0].insertBefore(style);
            ");
    }

    $userData = $app['github']->getUserData();

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
                if ($request->request->get('bookmarklet')) {
                    return new Response("
                        <script type='text/javascript'>
                            window.
                            parent.
                            document.
                            getElementById('BalloonGithubIssuesFrame').
                            parentNode.
                            removeChild(
                                window.
                                parent.
                                document.
                                getElementById('BalloonGithubIssuesFrame'
                                )
                            );
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
            if (!$request->query->has('redirect')) {
                $f =  $app['twig']->render('add.html.twig', array(
                    'form'          => $form->createView(),
                    'bookmarklet'   => true,
                )); 
                return new Response("
                var a=".json_encode($f).";
                document.getElementsByTagName('body')[0].innerHTML=a;
                var img = document.createElement('img');
                img.setAttribute('src','".$userData['avatar_url']."');
                img.setAttribute('style','width:55px; position:absolute; top:80px; left:10px; height:55px;');
                document.getElementsByTagName('body')[0].appendChild(img);
                var style = document.createElement('link');
                style.setAttribute('type','text/css');
                style.setAttribute('href','http://dev/BalloonGithubIssues/web/bootstrap/bootstrap.css');
                js = document.createElement('script');
                js.setAttribute('type','text/javascript');
                js.setAttribute('src','http://dev/BalloonGithubIssues/web/js/session-0.4.js');
                document.getElementsByTagName('head')[0].appendChild(js);
                style.setAttribute('rel','stylesheet');
                document.getElementsByTagName('html')[0].setAttribute('style','overflow-y:hidden;')
                document.getElementsByTagName('head')[0].insertBefore(style);
                var isFlashPresent = false;
                window.session = {start: function(sess){
                    userData = ' screensize:'+session.device.screen.width+'x'+session.device.screen.height+',';
                    userData += ' browser:'+session.browser.browser+'/'+session.browser.version+':'+session.browser.os+',';
                    userData += ' lang:'+session.locale.lang+',';
                    userData += ' flash:'+session.plugins.flash+',';
                    document.getElementById('form_userData').value=userData;

                }}
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
            ));
        }
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

    if ($request->request->has('bookmarklet')) {
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
    if ($request->query->has('bookmarklet')) {
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
