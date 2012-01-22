var iframeId = "BalloonGithubIssuesFrame";

function initBookMarkLet()
{
    // config
    var base_url = document.getElementById('balloon_github_issues_bookmarklet').getAttribute('class');
    var cssStyle = base_url + '/css/bookmarklet_frame.css';

    // Create and insert the main container
    var container = document.createElement('iframe');
    container.setAttribute('id',iframeId);
    document.getElementsByTagName('body')[0].insertBefore(container);

    // Load javascript...
    var js = document.createElement('script');
    js.setAttribute('type','text/javascript');
    js.setAttribute('src', base_url+'/js/jquery-1.7.1.min.js');
    container.contentWindow.document.getElementsByTagName('head')[0].appendChild(js);

    var js = document.createElement('script');
    js.setAttribute('type','text/javascript');
    js.setAttribute('src', base_url + '/add?src=bookmarklet.js');
    container.contentWindow.document.getElementsByTagName('head')[0].appendChild(js);

    var gif_loader = document.createElement('img');
    gif_loader.setAttribute('src', base_url+'/img/loader.gif');
    gif_loader.setAttribute('style','position:absolute; left:150px; top:145px;');
    container.contentWindow.document.getElementsByTagName('body')[0].appendChild(gif_loader);

    //...and styles
    var style = document.createElement('link');
    style.setAttribute('type','text/css');
    style.setAttribute('href',cssStyle);
    style.setAttribute('rel','stylesheet');
    document.getElementsByTagName('head')[0].insertBefore(style);

    // Show BookMarkLet
    document.getElementById(iframeId).style.display="block";
}

function onMessage(e) {
    var frameObject = document.getElementById(iframeId);
    switch(e.data.action){
        case 'remove':
            frameObject.parentNode.removeChild(frameObject);
        break;
    }
}

window.addEventListener("message", onMessage, true);
initBookMarkLet();

