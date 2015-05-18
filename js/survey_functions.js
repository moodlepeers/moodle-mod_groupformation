$(document).ready(function() {
    

    
    // Drag & Drop the topics/objects to sort them 
//        $('#sortable_topics').sortable(function(){
//        axis: 'y';
////        stop: function (event, ui) {
////	        var data = $(this).sortable('serialize');
////            $('span#order').text(data);
////            /*$.ajax({
////                    data: oData,
////                type: 'POST',
////                url: '/your/url/here'
////            });*/
////	   }
//    });
    
    
    
    
    // clickable wraper for input radios // Fragebogen
    $(".select-area").click(function() {
        $(this).find('input:radio').prop('checked', true);
    });

    
    // manipulate grades on change
    $( '#grade1' ).change(function() {
        var grade1 = $(this).val();
        $('#grade3 option').prop('selected', false)
                            .filter('[value="' + grade1 + '"]')
                            .prop('selected', true);

        $('#grade3 option').each(function(){
            if($(this).val() < grade1){
                $(this).attr('disabled',true);
            }
        });
    });

    
});
