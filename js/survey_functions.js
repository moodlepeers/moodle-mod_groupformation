$(document).ready(function() {

    // TODO Einkommentieren wenn die Topics in erfolgreich in DB geschrieben werden
    // $('#invisible_topics_inputs').hide();

    
    // Drag & Drop the topics/objects to sort them 
    $('.sortable_topics').sortable({
    	  axis: 'y',
    	  stop: function (event, ui) {
    	      var data = $(this).sortable('serialize');
    	      //$('span#order').text(data);

              $('#invisible_topics_inputs').find('input').remove();
              createTopicInputs();

    	      /*$.ajax({
    	              data: oData,
    	          type: 'POST',
    	          url: '/your/url/here'
    	      });*/
    	 }
    });

    // create hidden Inputs of Topics to write the order of Topics to db with $_POST method
    function createTopicInputs(){
        var sortedIDs = $( ".sortable_topics" ).sortable( "toArray" );
        $.each(sortedIDs, function(index, value){
            $('<input type="text" name="'+ value +'"/>').val(index +1).appendTo('#invisible_topics_inputs');
        });
    }
    createTopicInputs();


    // write to hidden inputs to mark range-inputs as valid when they get clicked
    $('.gf_range_inputs').click(function(){
        $('input[name="'+ $(this).prop('name')+'_valid"]').val(1);
    });



    // if no survey_warnings appear - remove the ccs class "noAnswer" from questions(with radiobuttons) without answer.
    // This happens when questions are viewed by student first time
    if($('.survey_warnings').length < 1){
        $('.responsive-table>tbody>tr.noAnswer').each(function(){
            $(this).removeClass('noAnswer');
        });
    }

    // clickable wraper for input radios // Fragebogen
    $(".select-area").click(function() {
        var name = $(this).find('input:radio').attr('name');
        $('input[name="'+ name +'"]').parent().removeClass('selected_label');
        $('input[name="'+ name +'"]').parent().parent().removeClass('noAnswer');
        $(this).addClass('selected_label');
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
