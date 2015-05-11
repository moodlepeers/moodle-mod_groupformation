$(document).ready(function() {
    
    // hide validation error alerts and show them if needed
    // if css attribute "display:none" and show on validation error, they will not displayed properly
    $(".errors p").hide();
    
    //$("#fitem_id_szenario").hide();
    //$("#fitem_id_knowledge").hide();
    //$("#fitem_id_knowledgelines").hide();
    var topicCounter = 3; //counts topics to make group numbers
    
    var preknwCounter = 3;
    
    var stringOfPreknowledge = "";

    var stringOfTopics = "";
    
    //$('#fitem_id_knowledge').insertBefore('.knowledgeWrapper');
    
    // TODO @Eduard hier hab ich das reaktive mal begonnen, doch es muss ja generisch für alle auftauchenden Zeilen sein
    // Ich dachte man könnte vllt eine Zeile und die entsprechende VorschauZeile hidden bereit halten und immer kopieren mit umbenannter ID
    // kann man solche angelegten Dokumente denn dann auch mit Jquery funktionen wie dem keyup verknüpfen? Muss man ja um den Inhalt in die Vorschau zu bekommen
    // @Rene ich benutze auch keyup. Es gibt jedoch noch eine feature Funktion. Dabei soll eine neue Zeile auftauchen, wenn eine Eingabe erfolgt ist. 
    // So müsste man den + Button nicht mehr betätigen. Wenn ich einfach nur keyup nutze würde man mit jedem neuen Buchstabe eine Zeile generieren. 
    // Ich habe es mir folgendermassen überlegt: 
    // Man prüft und übergibt ein mal bei "focus" die Id der Zeile in der gerade die Eingabe erfolgt. Bei "keyup" wird die Eingabe an die 
    // Vorschau und den tatsächlichen Textfeld der versteckt ist übergeben. Eine neue Zeile wird nur dann generiert wenn es keine Zeilen mit höherer ID(der jetzigen Zeile) gibt.
    // Momentan ist die Übergabe der Eingabe nicht implementiert. Die Vorschau der Themen und Vorwissen haben unterschiedliche HTML Struktur,
    // trotzdem versuche ich diese generisch zu machen. 
    
    $('#js_id_knowledge').keyup(function () {
    	$('#prkRow0_span').html($('#js_id_knowledge').val());
    
    });
    
    //if knowledge gets checked
    $('#id_js_knowledge').click(function(){
    	if ($('#id_knowledge').prop('checked')){
    		$('#id_knowledge').prop('checked',false);
    	}else{
    		$('#id_knowledge').prop('checked', true);
    	}
    	$(".js_knowledgeWrapper").toggle();
    });
    
    //if topics gets checked
    $('#id_js_topics').click(function(){
    	if ($('#id_topics').prop('checked')){
    		$('#id_topics').prop('checked',false);
    	}else{
    		alert("false Dialog");
    		$('#id_topics').prop('checked', true);
    	}
    	$(".js_topicsWrapper").toggle();
    });
    
    //if knowledge was checked last time
    if ($('#id_knowledge').prop('checked')){
    	$('#id_js_knowledge').prop('checked',true);
    	$('#id_knowledge').prop('checked',true);
    	$(".js_knowledgeWrapper").toggle();
    }
    
    //if topics was checked last time
    if ($('#id_topics').prop('checked')){
    	$('#id_js_topics').prop('checked',true);
    	$('#id_topics').prop('checked',true);
    	$(".js_topicsWrapper").toggle();
    }
    
    $('#js_evaluationmethod').val($('#id_evaluationmethod').val());
    $('#group_size').val($('#id_maxmembers').val());
    $('#numb_of_groups').val($('#id_maxgroups').val());
    
    // change of evaluationmethod in js changes non-js field
    $('#js_evaluationmethod').change(function() {
    	$('#id_evaluationmethod').val( this.value);
    });
    
    // change of numb_of_groups in js changes non-js field
    $('#numb_of_groups').keyup(function() {
    	$('#id_maxgroups').val(parseInt(this.value));
    });
    $('#numb_of_groups').change(function() {
    	$('#id_maxgroups').val(this.value);
    });
    
    // change of group_size in js changes non-js field
    $('#group_size').keyup(function() {
    	$('#id_maxmembers').val(parseInt(this.value));
    });
    $('#group_size').change(function() {
    	$('#id_maxmembers').val(this.value);
    });
    
    //toggle with checkbox
    $('input[type="checkbox"]').click(function(){
        
            // If you add topics, number of groups option will adapt
            if($(this).attr("value")=="wantTopics"){
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
//      $multifieldID = 'input' + $cat + $('.multi_field', $wrapper).length;
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
         stringOfPreknowledge += $(this).val() + '\n';
	  });
     $('#id_knowledgelines').val(stringOfPreknowledge);
 }
   
   function writeTopicsToField(){
     stringOfTopics = '';
	  $('.js_topicInput').each(function(){
		  stringOfTopics += $(this).val() + '\n';
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
        
        // click non-js radio buttons
        if ($(this).prop('id')=='group_opt_size'){
        	$('#id_groupoption_0').click();
        	$('#id_maxgroups').val(0);
        }else{
        	$('#id_groupoption_1').click();
            $('#id_maxmembers').val(0);
        }
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

});
