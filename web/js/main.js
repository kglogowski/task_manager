$().ready(function(){
    $('.input-group-addon').click(function(){
       $(this).next('input').focus(); 
    });
});