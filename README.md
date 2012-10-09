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

#### Pending repo
`pending_repo` : use a temporary repository for lambda users if you do not want to give access to your final repository to them. Define in which repo pending issues are stored and transfer them to final repo after approval

`false` if do not want to use this feature

````
'pending_repo' => array(
    'user' => 'userforpendingrepo',
    'repo' => 'repoforpendingrepo',
    'allowed_users' => array('thisusercanapprovependingissue', 'thisonetoo', 'andalsothatone'),
);
```

## Changelog

* v 1.3.0 Added labels 
* v 1.2.0 Added Pending Issues
* v 1.1.0 Added Bookmarklet
* v 1.0.0 Initial version

## Requirements

* PHP 5.3.x
* curl extension activated

## Screenshots

![add issue bookmarklet](https://github.com/Balloon/BalloonGithubIssues/raw/master/doc/add_an_issue.png)
<p>Add an issue via Bookmarklet</p>

![add issue bookmarklet 2](https://github.com/Balloon/BalloonGithubIssues/raw/master/doc/add_an_issue_2.png)
<p>Add an issue via Bookmarklet (bis)</p>

![Github Issue with screen](https://github.com/Balloon/BalloonGithubIssues/raw/master/doc/github_issue.png)
<p>View screenshot directly in Issue + extra infos!</p>