$(document).ready(function() {
    
    $('input[name=group_opt]').change(function(e){
        // disable all inputs .second
//        $('.second').css('opacity', '.5');
        $('.second').attr('disabled', 'disabled');
        // enable the current input .second
//        $('#'+$(this).val()).css('opacity', '1');
        $('#'+$(this).val()).removeAttr('disabled');
    });

});
