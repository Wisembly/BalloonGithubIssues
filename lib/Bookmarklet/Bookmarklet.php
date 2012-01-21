<?php

namespace Bookmarklet;

class Bookmarklet {

    private $base_url;

    public function __construct($base_url)
    {
        $this->base_url = $base_url;
    }

    public function render($dispatch, $view = null, $params = array())
    {
        switch ($dispatch) {
            case 'login_form':
                return "var a=".json_encode($view).";
                    document.getElementsByTagName('body')[0].innerHTML=a;
                    var style = document.createElement('link');
                    style.setAttribute('type','text/css');
                    style.setAttribute('href','".$this->base_url."/bootstrap/bootstrap.css');
                    style.setAttribute('rel','stylesheet');
                    document.getElementsByTagName('head')[0].insertBefore(style);
                    var style = document.createElement('link');
                    style.setAttribute('type','text/css');
                    style.setAttribute('href','".$this->base_url."/css/style.css');
                    style.setAttribute('rel','stylesheet');
                    document.getElementsByTagName('html')[0].setAttribute('style','overflow-y:hidden;')
                    document.getElementsByTagName('head')[0].insertBefore(style);";
            break;

            case 'add_issue':
                return "var a=".json_encode($view).";
                document.getElementsByTagName('body')[0].innerHTML=a;
                var img = document.createElement('img');
                img.setAttribute('src','".$params['avatar_url']."');
                img.setAttribute('style','width:55px; position:absolute; top:80px; left:10px; height:55px;');
                document.getElementsByTagName('body')[0].appendChild(img);
                var style = document.createElement('link');
                style.setAttribute('type','text/css');
                style.setAttribute('href', '".$this->base_url."/bootstrap/bootstrap.css');
                js = document.createElement('script');
                js.setAttribute('type','text/javascript');
                js.setAttribute('src', '".$this->base_url."/js/session-0.4.js');
                document.getElementsByTagName('head')[0].appendChild(js);
                style.setAttribute('rel','stylesheet');
                document.getElementsByTagName('html')[0].setAttribute('style','overflow-y:hidden;')
                document.getElementsByTagName('head')[0].insertBefore(style);
                window.session = {start: function(sess){
                    userData = ' screensize:'+session.device.screen.width+'x'+session.device.screen.height+',';
                    userData += ' browser:'+session.browser.browser+'/'+session.browser.version+':'+session.browser.os+',';
                    userData += ' lang:'+session.locale.lang+',';
                    userData += ' flash:'+session.plugins.flash+',';
                    document.getElementById('form_userData').value=userData;
                }}";
            break;

            case 'redirect':
                return "<script type='text/javascript'>
                    var js = document.createElement('script');
                    js.setAttribute('type','text/javascript');
                    js.setAttribute('src', ".$this->base_url."'/add?src=bookmarklet.js');
                    document.getElementsByTagName('head')[0].appendChild(js);
                </script>";
            break;

            case 'login':
                return "<script type='text/javascript'>
                    var js = document.createElement('script');
                    js.setAttribute('type','text/javascript');
                    js.setAttribute('src','".$this->base_url."/add?src=bookmarklet.js');
                    document.getElementsByTagName('head')[0].appendChild(js);
                </script>";
            break;

            case 'logout':
                return "<script type='text/javascript'>
                    var js = document.createElement('script');
                    js.setAttribute('type','text/javascript');
                    js.setAttribute('src', ".$this->base_url."'/add?src=bookmarklet.js');
                    document.getElementsByTagName('head')[0].appendChild(js);
                </script>";
            break;
        }
    }
}

