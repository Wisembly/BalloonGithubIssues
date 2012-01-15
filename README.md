# Purpose of this project

This tool aims to give a simple access to all non-technical protagonists of a projet to Github issues and add some extra features like screenshots upload.

Based on Silex micro-framework and Twitter Bootstrap.

## Install

Clone the project:
`git clone git@github.com:guillaumepotier/BalloonGithubIssues.git`

Retrieve submodules content:
`git submodule update --init`

Create your local config file and edit it:
`cp app/config/config.php.dist app/config/config.php`
`vi app/config/config.php`

Chmod your upload dir
`chmod 777 web/upload`

## Config

* `debug` : `boolean` -> whether in production or not
* `locale`: `string` -> default app language (en, fr currently)
* `repositories` : `array` -> list of repositories managed by the tool
* `labels` : `array` -> auto-assign labels for new issues (yet, must be existing labels)

## Requirements

* PHP 5.3.x
* curl extension activated

## TODO

* debug labels not assigned when creating a new issue

## Bookmarklet :

javascript:
 s = document.createElement('script');
 s.setAttribute('type','text/javascript');
 s.setAttribute('src','http://dev/BalloonGithubIssues/web/js/init_bookmarklet.js');
 s.setAttribute('id','main_js_container');
 j = document.createElement('script');
 j.setAttribute('type','text/javascript');
 j.setAttribute('src','http://code.jquery.com/jquery-1.7.1.min.js');
 document.getElementsByTagName('head')[0].appendChild(j);document.getElementsByTagName('head')[0].appendChild(s);
