
var FormValidator = function(id) {
    this.id = id;
    this.errors = new Array();
}

$.extend(FormValidator.prototype, {
    validateText: function(params) {
        name = params['name'];
        value = params['value'];
        required = params.hasOwnProperty('required') ? params['required'] : false;
        min_lenght = params.hasOwnProperty('min_lenght') ? params['min_lenght'] : false;
        max_lenght = params.hasOwnProperty('max_lenght') ? params['max_lenght'] : false;
        if (required && value == '') {
            this.addError(name, 'Pole wymagane');
        }
        if (min_lenght != false && min_lenght > value.length) {
            this.addError(name, 'Za mało znaków "minimum: '+min_lenght+'"');
        }
        if (max_lenght != false && max_lenght < value.length) {
            this.addError(name, 'Za dużo znaków "maksimum: '+max_lenght+'"');
        }

    },
    addError: function(name, error) {
        this.errors[name] = error;
    },
    clean: function() {
        $id = this.id;
        $($id).find('.error_list').remove();
    },
    isValid: function() {
        i = 0;
        $id = this.id;
        for(var name in this.errors) {
            $input = $($id).find("input[name='"+name+"']");
            i++;
        }
        return i == 0 ? true : false;
    },
    showErrors: function() {
        $id = this.id;
        $arr = this.errors;
        for(var name in $arr) {
            $input = $($id).find("input[name='"+name+"']");
            $input.parent().append('<div class="error_list">'+$arr[name]+'</div>');
        }
    }
});


function ajaxChangeContent(params) {
    append = params.hasOwnProperty('append') ? params['append'] : false;
    url = params['url'];
    id = params['id'];
    idLoad = params.hasOwnProperty('idLoad') ? params['idLoad'] : params['id'];
    data = params.hasOwnProperty('data') ? params['data'] : '';
    message = params.hasOwnProperty('msg') ? params['msg'] : null;
    $(idLoad).append('<div class="ajax-load" ></div>');
    $.ajax({
        url: url + "?msg=" + message,
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

function showFlash(params) {
    id = params['id'];
    message = params.hasOwnProperty('message') ? params['message'] : "Wpisz parametr message w wywołaniu funkcji";
    $(id).parent().find('.alert').remove();
    $(id).parent().find('input').val("");
    $(id).before("<div class='alert alert-success'>"+message+"</div>");
}