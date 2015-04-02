$(document).ready(function() {
    $('.multi_field_wrapper').each(function() {
        var $wrapper = $('.multi_fields', this);
        $(".add_field", $(this)).click(function(e) {
            $('.multi_field:first-child', $wrapper).clone(true).appendTo($wrapper).find('input').val('').focus();
        });
        $('.multi_field .remove_field', $wrapper).click(function() {
            if ($('.multi_field', $wrapper).length > 1)
                $(this).parent('.multi_field').remove();
        });
    });
});





//$(document).ready(function() {
//    var max_fields      = 10; //maximum input boxes allowed
//    var wrapperKn       = $(".input_knowledge_wrap"); //Fields wrapper
//    var wrapperTp       = $(".input_topic_wrap"); //Fields wrapper
//    var add_knowledge   = $(".add_knowledge_button"); //Add button ID
//    var add_topic       = $(".add_topic_button"); //Add button ID
//
//    
//    var x1 = 1; //initlal text box count
//    $(add_knowledge).click(function(e){ //on add input button click
//        e.preventDefault();
//        if(x1 < max_fields){ //max input box allowed
//            x1++; //text box increment
//            $(wrapperKn).append('<div><input class="respwidth" type="text" name="knowledge[]"/><a href="#" class="remove_knowledge">Remove</a></div>'); //add input box
//        }
//    });
//    
//    var x2 = 1; //initlal text box count
//    $(add_topic).click(function(e){ //on add input button click
//        e.preventDefault();
//        if(x2 < max_fields){ //max input box allowed
//            x2++; //text box increment
//            $(wrapperTp).append('<div><input class="respwidth" type="text" name="topic[]"/><a href="#" class="remove_topic">Remove</a></div>'); //add input box
//        }
//    });
//    
//    $(wrapperKn).on("click",".remove_knowledge", function(e){ //user click on remove text
//        e.preventDefault(); 
//        $(this).parent('div').remove(); 
//        x1--;
//    });
//    
//     $(wrapperTp).on("click",".remove_topic", function(e){ //user click on remove text
//        e.preventDefault(); 
//        $(this).parent('div').remove(); 
//        x2--;
//    });
//});



