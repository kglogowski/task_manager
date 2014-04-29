function ajaxChangeContent(params) {
    append = params.hasOwnProperty('append') ? params['append'] : false;
    url = params['url'];
    id = params['id'];
    idLoad = params.hasOwnProperty('idLoad') ? params['idLoad'] : params['id'];
    data = params.hasOwnProperty('data') ? params['data'] : '';
    message = params.hasOwnProperty('msg') ? params['msg'] : null;
    $(idLoad).append('<div class="ajax-load" ></div>');
    $.ajax({
        url: url+"?msg="+message,
        type: "POST",
        data: data,
        success: function(msg) {
            if (append == false) {
                $(idLoad).html(msg);
            } else {
                $(idLoad).find('.ajax-load').remove();
            }
        }
    });

}