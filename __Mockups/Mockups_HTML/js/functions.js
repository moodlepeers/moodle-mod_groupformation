$(document).ready(function() {
    
    // hide validation error alerts and show them if needed
    // if css attribute "display:none" and show on validation error, they will not displayed properly
    $(".errors p").hide();
    
    
    var groupCounter = 3; //counts topics to make group numbers

    
    //toggle with checkbox
    $('input[type="checkbox"]').click(function(){
            if($(this).attr("value")=="wantKnowledge"){
                $(".knowledge").toggle();
            }
        
            // If you add topics, number of groups option will adapt
            if($(this).attr("value")=="wantTopics"){
                $(".topics").toggle();
                if( $("#group_opt_numb").attr('disabled') == 'disabled' ){
                    $("#group_opt_numb").removeAttr('disabled');
                    $('#numb_of_groups').val(0);
                }
                
                else{
                    $("#group_opt_numb").attr('disabled', 'disabled');
                    $("#group_opt_size").prop("checked", true);
                    $("#group_size").removeAttr('disabled');
                    $("#numb_of_groups").attr('disabled', 'disabled');
                    $('#numb_of_groups').val(groupCounter);
                }
            }
        });
    
    
    
    //dynamic inputs function
    $('.multi_field_wrapper').each(function() {
        var $wrapper = $('.multi_fields', this);
        $(".add_field", $(this)).click(function(e) {
            // adds input field
            $('.multi_field:first-child', $wrapper).clone(true).appendTo($wrapper).find('input').val('').focus();
            // count topics and make group number
            if($(this).parent().attr('class') == 'multi_field_wrapper topics'){
                groupCounter++;
                $('#numb_of_groups').val(groupCounter);
            }
        });
        
        
        $('.multi_field .remove_field', $wrapper).click(function() {
            if ($('.multi_field', $wrapper).length > 1){
//                alert($(this).parent().parent().attr('class'));
                if($(this).parent().parent().parent().attr('class') == 'multi_field_wrapper topics'){
//                if($(this).parentsUntil("div.multi_field_wrapper.topics") ){
                    groupCounter--;
                    $('#numb_of_groups').val(groupCounter);
                }
                //remove input field
                $(this).parent('.multi_field').remove();
            }
        });
    });


    //disable with radios

    $('input[name=group_opt], input[name=valid_opt]').change(function(e){
        
        // disable all inputs in .second and set value to 0
        $('.' + $(this).attr('name')).attr('disabled', 'disabled');
        $('.' + $(this).attr('name')).val(0);
        
        // enable the current input .second
        $('#'+$(this).val()).removeAttr('disabled');
    });

    
    
    
    
  /////////////////////// Sticky buttons ///////////////////////////////////////////////////////////
    
    function UpdateBtnWrapp() {
        $(".persist-area").each(function() {

        var el             = $(this),
           offset         = el.offset(),
           scrollTop      = $(window).scrollTop(),
           floatingWrapper = $(".floatingWrapper", this)

        if ((scrollTop > offset.top) && (scrollTop < offset.top + el.height())) {
            floatingWrapper.css({
            "visibility": "visible"
            });
        } else {
            floatingWrapper.css({
            "visibility": "hidden"
            });      
        };
    });
}

// DOM Ready      
    $(function() {

        var clonedWrapper,
            theWidth = $('.col_100 h4').width();


       $(".persist-area").each(function() {
           clonedWrapper = $(".btn_wrap", this);       
           clonedWrapper
             .before(clonedWrapper.clone(true))
    //            .css('width', widthYeah)
    //             .css("width", clonedWrapper.width())
           .css('width', theWidth)
             .addClass("floatingWrapper");

       });

       $(window).scroll(UpdateBtnWrapp).trigger("scroll");
        //    alert(theWidth);

    });

///////////////////////////////////////////////////////////////////////////////////////
    
    
    //
    //  Datepicker + validation of dates on submit
    //
    
    
    
    $.datepicker.regional['de'] = {
        dateFormat: 'dd.mm.yy',
        monthNames: ['Januar','Februar','M\u00e4rz','April','Mai','Juni','Juli','August','September','Oktober','November','Dezember'],
        dayNames: ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag','Samstag'],
        dayNamesMin: ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa']
    };
    
    $("#startDate, #endDate").datepicker(); // initialize Datepicker 
    if(true){                               //TODO Welche Sprache/Land wird verwendet, bei deutsch: true
        $.datepicker.setDefaults( $.datepicker.regional[ 'de' ] );
    }else $.datepicker.setDefaults( $.datepicker.regional[ '' ] ); //else default: engl.
    
    $("#startDate").datepicker("setDate", new Date() ); // Set default startDate as current date
//    $("#endDate").datepicker("setDate", new Date() );
//    var currentDate = $( "#startDate" ).datepicker( "getDate" );
    $('#endDate').datepicker("setDate", "+7");
    
    
    //set endDate + 7 Days from startDate by default
    $( '#startDate' ).change(function() {
            var date = $(this).datepicker('getDate');
            date.setDate(date.getDate() + 7); // Add 7 days
            $('#endDate').datepicker('setDate', date); // Set as default
      });
    
    
    
    // validating the docent form page 2
    $( "#docent_settings_2" ).submit(function( event ){
        var proceed = true;
        
        var startDate = $( "#startDate" ).datepicker( "getDate" );
        var endDate = $( "#endDate" ).datepicker( "getDate" );

        // check the date
        if (startDate >= endDate){
            proceed = false;
            
            $('#error_date').css({display: "inline-block"});
        }
        
        if(proceed){ //if form is valid submit form
            return true;
        }
        
        event.preventDefault();         // prevent submitting 
        
        scrollTo('.errors');            //scroll to the error messages
        
//        $('html, body').animate({       
//            scrollTop: $('.errors').offset().top
//        }, 80);
//        
    });
    
    // scroll to function
    function scrollTo($param) {
        $('html, body').animate({    
            scrollTop: $($param).offset().top
        }, 80);
    }
    
    
    
    //
    // Survey functions
    //
    
    
    // Drag & Drop the topics/objects to sort them // Fragebogen
        $('#sortable_topics').sortable({
        axis: 'y',
        stop: function (event, ui) {
	        var data = $(this).sortable('serialize');
            $('span#order').text(data);
            /*$.ajax({
                    data: oData,
                type: 'POST',
                url: '/your/url/here'
            });*/
	   }
    });
    
    
    
    
    // clickable wraper for input radios // Fragebogen
    $(".select-area").click(function() {
    $(this).find('input:radio').prop('checked', true);
    });

    
    // manipulate grades on change
    $( '#gradeA' ).change(function() {
        var gradeA = $(this).val();
        $('#gradeC option').prop('selected', false)
                            .filter('[value="' + gradeA + '"]')
                            .prop('selected', true);

        $('#gradeC option').each(function(){
            if($(this).val() < gradeA){
                $(this).attr('disabled',true);
            }
        });
    });

    
    
    //
    //
    //
    
});
