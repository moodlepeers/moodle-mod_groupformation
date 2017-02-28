/**
 * moodle-mod_groupformation JavaScript
 * https://github.com/moodlepeers/moodle-mod_groupformation
 *
 *
 * @author Eduard Gallwas, Johannes Konert, René Röpke, Neora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$(document).ready(function () {

    $('#submit_groups').on('click', function(e){
        e.preventDefault();
        $('.gf_pad_content').find('.add_membs_to_g, .down').removeClass('down').parent().next().children('.add_one').remove();

        var group_string = {};
        var memb_ids = [];
        $('ul.memb_list').each(function(){
            var i = 0;
            $(this).children('li').each(function(){
                memb_ids.push(parseInt($(this).attr('id')));
            });
            group_string[$(this).attr('id').slice(9)] = memb_ids;
            memb_ids = [];
        });

        var json = JSON.stringify(group_string);
        $('#groups_string').val(json);

        // TODO: auskommentieren um zu debuggen!
        $('#edit_groups_form').submit();

    });

    function sticky_relocate() {
        var window_top = $(window).scrollTop();
        var div_top = $('#sticky-anchor').offset().top;
        var heigth = $('#sticky').css( "height" );
        var width = $('#sticky').css( "width" );
        if (window_top > div_top) {
            $('#sticky').addClass('stick');
            $('#first_group').css('padding-top', heigth);
            $('#sticky').css('width', width).find('.gf_pad_header_opaque').addClass('opaque');
            $('#edit_groups_header').hide();
        } else {
            $('#sticky').removeClass('stick').find('.gf_pad_header_opaque').removeClass('opaque');
            $('#first_group').css('padding-top', 0);
            $('#edit_groups_header').show();
        }
    }

    $(function () {
        $(window).scroll(sticky_relocate);
        sticky_relocate();
    });

    var edit_group_id = '';

    $(".add_membs_block").click(function(){

        var temp_id = $(this).next().attr('id');

        if(temp_id == edit_group_id){
            $(this).removeClass('group_active');
            $(this).children('.add_membs_to_g').removeClass("down");
            $(this).next().children('.add_one').remove();
            edit_group_id = '';
        }else{

            $('.gf_pad_content').find('.add_membs_to_g, .down').parent().removeClass('group_active')
            $('.gf_pad_content').find('.add_membs_to_g, .down').removeClass('down').parent().next().children('.add_one').remove();
            $(this).children('.add_membs_to_g').addClass("down");
            $(this).next().append('<li class="add_one tooltip" title="Click on selected users to add them to this group!">add member</li>');
            $(this).addClass('group_active');
            edit_group_id = temp_id;
        }
    });

    $('ul.memb_list').on('click', 'li', function(){
        var memb_id = $(this).attr('id');

        if(!$(this).is('.add_one, .memb_selected')){

            var cloned_memb = $(this).clone();

            $(this).toggleClass('memb_selected');

            cloned_memb.attr('id', "selected_" + memb_id );
            $('ul.selected_memb_list').append(cloned_memb);

        }else if($(this).is('.memb_selected')){

            $(this).toggleClass('memb_selected');

            $('ul.selected_memb_list').find('#selected_' + memb_id).remove();
        }
        count_members();
    });

    $('ul.selected_memb_list').on('click', 'li', function(){

        var selected_memb_id = $(this).attr('id').substr(9);

        if(edit_group_id != "") {
            $(this).remove();
            addMembToGroup(selected_memb_id);
            count_members();
        }

    });

    $('#unselect_all').click(function(){
        $('ul.selected_memb_list li').remove();
        $('.memb_selected').removeClass('memb_selected');
        $('#memb_counter').html(0 + ' ');
        count_members();
    });

    function addMembToGroup(memb_id){

        var the_member = $('.gf_pad_content').find('#' + memb_id);
        $(the_member).removeClass('memb_selected').remove().clone();

        $(the_member).insertBefore($('.gf_pad_content').find('#' + edit_group_id).find('li:last-child'));
        count_members();
    }

    function count_members(){
        var sel_counter = $('ul.selected_memb_list li').length;
        $('#memb_counter').html(sel_counter + ' ');
        if(sel_counter > 0){ $('#ux_hint_1').hide();}
        else{ $('#ux_hint_1').show();}

        $('ul.memb_list').each(function(){
            var g_counter = $(this).children('li').length;
            if($(this).children('li:last-child').hasClass('add_one')){g_counter--;}
            $(this).parent().prev().find('.g_memb_counter').html(' ' + g_counter);
        });

    };

    count_members();

});