if ($('body').hasClass('index')) {
    $('#milestones').hide();
    $('#issues_tabs').show();
    $('.see_issues').parent().addClass('active');

    $('.see_milestones').click(function(){
        $('.see_issues').parent().removeClass('active');
        $('.see_milestones').parent().addClass('active');
        $('#issues').fadeOut('slow');
        $('#milestones').fadeIn('slow');
    });

    $('.see_issues').click(function(){
        $('.see_milestones').parent().removeClass('active');
        $('.see_issues').parent().addClass('active');
        $('#milestones').fadeOut('slow');
        $('#issues').fadeIn('slow');
    });
}

if ($('body').hasClass('add')) {
    $('#form_issue').focus();
}
