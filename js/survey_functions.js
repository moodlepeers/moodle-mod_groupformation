$(document).ready(function() {
    

    
//    // Drag & Drop the topics/objects to sort them // Fragebogen
//        $('#sortable_topics').sortable({
//        axis: 'y',
//        stop: function (event, ui) {
//	        var data = $(this).sortable('serialize');
//            $('span#order').text(data);
//            /*$.ajax({
//                    data: oData,
//                type: 'POST',
//                url: '/your/url/here'
//            });*/
//	   }
//    });
//    
//    
    
    
    // clickable wraper for input radios // Fragebogen
    $(".select-area").click(function() {
        $(this).find('input:radio').prop('checked', true);
    });

    
//    // manipulate grades on change
//    $( '#gradeA' ).change(function() {
//        var gradeA = $(this).val();
//        $('#gradeC option').prop('selected', false)
//                            .filter('[value="' + gradeA + '"]')
//                            .prop('selected', true);
//
//        $('#gradeC option').each(function(){
//            if($(this).val() < gradeA){
//                $(this).attr('disabled',true);
//            }
//        });
//    });

    
});
