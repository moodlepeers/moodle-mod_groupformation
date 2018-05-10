require(['jquery'], function($) {
    $(document).ready(function () {
        $('#all_data_check').click(function () {
            if ($('#all_data_check').prop('checked')) {
                $('#all_data_true').removeClass('gf_hidden');
                $('#all_data_false').addClass('gf_hidden');
            } else {
                $('#all_data_true').addClass('gf_hidden');
                $('#all_data_false').removeClass('gf_hidden');
            }
        });
    });
});
