    var iframeId = "BalloonGithubIssuesFrame";

function initBookMarkLet()
{
    // config
    var host = 'http://dev/BalloonGithubIssues/web';
    var cssStyle = 'http://dev/BalloonGithubIssues/web/css/bookmarklet_frame_style.css';


    // Create and insert the main container
    var container = document.createElement('iframe');
    container.setAttribute('id',iframeId);
    document.getElementsByTagName('body')[0].insertBefore(container);

    // Load javascript...
    var js = document.createElement('script');
    js.setAttribute('type','text/javascript');
    js.setAttribute('src','http://code.jquery.com/jquery-1.7.1.min.js');
    container.contentWindow.document.getElementsByTagName('head')[0].appendChild(js);

    var js = document.createElement('script');
    js.setAttribute('type','text/javascript');
    js.setAttribute('src','http://dev/BalloonGithubIssues/web/add?src=bookmarklet.js&iframeid='+iframeId);
    container.contentWindow.document.getElementsByTagName('head')[0].appendChild(js);

    //...and styles
    var style = document.createElement('link');
    style.setAttribute('type','text/css');
    style.setAttribute('href',cssStyle);
    style.setAttribute('rel','stylesheet');
    document.getElementsByTagName('body')[0].insertBefore(style);

    // Show BookMarkLet
    $('#'+iframeId).fadeIn('slow');
}

function closeIframe(iframeId)
{
    var iframe = document.getElementById(iframeId);
    iframe.parentNode.removeChild(iframe);
}

initBookMarkLet();
