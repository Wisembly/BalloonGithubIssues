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
* `base_url` : `string` -> url to your web directory, where index.php is located
* `locale`: `string` -> default app language (en, fr currently)
* `repositories` : `array` -> list of repositories managed by the tool

## Requirements

* PHP 5.3.x
* curl extension activated

## TODO

* add label(s) when creating a new issue

## Screenshots

![add issue bookmarklet](https://github.com/Balloon/BalloonGithubIssues/raw/master/doc/add_an_issue.png)
<p>Add an issue via Bookmarklet</p>

![add issue bookmarklet 2](https://github.com/Balloon/BalloonGithubIssues/raw/master/doc/add_an_issue_2.png)
<p>Add an issue via Bookmarklet (bis)</p>