$(document).ready(function() {
    
    // hide validation error alerts and show them if needed
    // if css attribute "display:none" and show on validation error, they will not displayed properly
    $(".errors p").hide();
    
    
    var topicCounter = 3; //counts topics to make group numbers
    
    var preknwCounter = 3;
    
    var stringOfPreknowledge = "";

    var stringOfTopics = "";
    
    //toggle with checkbox
    $('input[type="checkbox"]').click(function(){
            if($(this).attr("value")=="wantKnowledge"){
                $(".knowledgeWrapper").toggle();
            }
        
            // If you add topics, number of groups option will adapt
            if($(this).attr("value")=="wantTopics"){
                $(".topicsWrapper").toggle();
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
    
    
    
    function addInput($wrapper, $cat){
//        $multifieldID = 'input' + $cat + $('.multi_field', $wrapper).length;
        $theID = parseInt($('.multi_field:last-child', $wrapper).attr('id').substr(8)) + 1
        $multifieldID = 'input' + $cat + $theID;

        // adds input field
        $('.multi_field:first-child', $wrapper).clone(true).attr('id',$multifieldID)
                                                            .appendTo($wrapper).find('input').val('').focus();  
        addPreview($wrapper, $cat, $theID);
    }
    
    function addPreview($wrapper, $cat, $theID){
        $previewRowID = $cat + 'Row' +  $theID;
        
        if($cat == 'prk'){
            $('.knowlRow:first-child', '#preknowledges').clone(true).attr('id',$previewRowID)
                                                                .appendTo('#preknowledges').find('th').text('');
        }
        if($cat == 'tpc'){
            topicCounter++;
           $('.topicLi:first-child', '#previewTopics').clone(true).attr('id',$previewRowID)
                                                                .appendTo('#previewTopics').html('<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>');
        }
    }
    
    function removeInput($wrapper, $cat, $field){
        if ($('.multi_field', $wrapper).length > 1){
            $theID = parseInt($field.parent('.multi_field').attr('id').substr(8))
            $previewRowID = $cat + 'Row' +  $theID;
            //remove Preview
            $('#' + $previewRowID).remove();
            //remove Input
            $field.parent('.multi_field').remove();
        }
    }
    
    
    //dynamic inputs function
    $('.multi_field_wrapper').each(function dynamicInputs() {
        var $wrapper = $('.multi_fields', this);
        var $cat = $(this).parent().attr('id');
        
        //add field on button
        $(".add_field", $(this)).click(function() {
            addInput($wrapper, $cat);
        });
        
        //removes field on button
        $('.multi_field .remove_field', $wrapper).click(function() {
            $field = $(this);
            removeInput($wrapper, $cat, $field);    
        });
        
        
           //write to the preview
       $('.multi_field input:text', $wrapper).focus(function() {
          $previewRowID = ($cat + 'Row' + parseInt($(this).parent().attr('id').substr(8)));
          $(this).keyup(function(){
              if ($cat == 'prk'){
                  $('#' + $previewRowID).children('th').text($(this).val());
                  writePreknowledgeToField();
              }
              if ($cat == 'tpc'){
                  $('#' + $previewRowID).html('<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>' + $(this).val());
                  writeTopicsToField();
              }
          });
          
      });
  });
  
    
  
  function writePreknowledgeToField(){
      stringOfPreknowledge = '';
	  $('.js_preknowledgeInput').each(function(){
          stringOfPreknowledge += $(this).val() + '~';
	  });
      $('#id_knowledgelines').val(stringOfPreknowledge);
  }
    
    function writeTopicsToField(){
      stringOfTopics = '';
	  $('.js_topicInput').each(function(){
          stringOfPreknowledge += $(this).val() + '~';
	  });
      $('#id_topiclines').val(stringOfTopics);
  }
    


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
        $('.sortable_topics').sortable({
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
