$(document).ready(function() {
    
    // hide validation error alerts and show them if needed
    // if css attribute "display:none" and show on validation error, they will not displayed properly
    $(".errors p").hide();
    
    
    
    // TODO wenn JS und nonJS fehlerfrei funktioniert, die folgende Zeile einkommentieren
//    $("#non-js-content").hide();
    $("#js-content").show();
    

    var stringOfPreknowledge = "";

    var stringOfTopics = "";
    

    
    
      
///////////////////////////////////////////////////////////////////////////////////////////////      
///////////////////////////////////////////////////////////////////////////////////////////////    
    
    
  // Load Settings  
    
    //load the szenario which been choosen before
    if ($('#id_szenario option:selected').val() != 0){
    	 $('#js_szenarioWrapper').show('2000', 'swing');
    	var szenario = $('#id_szenario option:selected').val();
    	if(szenario == 1){
    			$("input[name='js_szenario'][value='project']").attr("checked","checked");
    			//check browser support first, before delete this
    			$('#knowledfeInfo').text($('#knowledfeInfoProject').text());
                $('#headerTopics').removeClass('required').addClass('optional');
                $('#id_js_topics').prop('disabled', false);
                
//	    		setSzenario('project');
    		}else if(szenario == 2){
    			$("input[name='js_szenario'][value='homework']").attr("checked","checked");
    			//check browser support first, before delete this
    			$('#knowledfeInfo').text($('#knowledfeInfoHomework').text());
                $('#headerTopics').removeClass('required').addClass('optional');
                $('#id_js_topics').prop('disabled', false);
                
//	    		setSzenario('homework');
    		}else if(szenario == 3){
    			$("input[name='js_szenario'][value='presentation']").attr("checked","checked");
    			//check browser support first, before delete this
    			$('#knowledfeInfo').text($('#knowledfeInfoPresentation').text());
                $('#headerTopics').removeClass('optional').addClass('required');
                $('#id_js_topics').prop('disabled', true);
                
	    		setSzenario('presentation');
    		}
    }
    

    
  //if knowledge was checked before
    if ($('#id_knowledge').prop('checked')){
        $('#id_js_knowledge').prop('checked',true);
        $('#id_knowledge').prop('checked',true);
        $("#js_knowledgeWrapper").show('2000', 'swing');

        //get the value of Moodle nativ field #id_knowledgelines, parse it and create dynamic input fields
        var lines = $('textarea[name=knowledgelines]').val().split('\n');
        $wrapper = $('#prk').find('.multi_fields');
        $cat = 'prk';
        $.each(lines, function(){
            addInput($wrapper, $cat, this);
        });
        for( var i = 0, l = 3; i < l; i++){
            //remove the first 3 dynamic fields which been created by default
            removeInput($wrapper, $cat, i);
        }
    }
  
  //if topics was checked before
    if ($('#id_topics').prop('checked')){
        $('#id_js_topics').prop('checked',true);
        $('#id_topics').prop('checked',true);
        $("#js_topicsWrapper").show('2000', 'swing');

        //get the value of Moodle nativ field #id_topiclines, parse it and create dynamic input fields 
        var lines = $('textarea[name=topiclines]').val().split('\n');
        $wrapper = $('#tpc').find('.multi_fields');
        $cat = 'tpc';
        $.each(lines, function(){
            addInput($wrapper, $cat, this);
        });
        for( var i = 0, l = 3; i < l; i++){
        //remove the first 3 dynamic fields which been created by default
            removeInput($wrapper, $cat, i);
        }
        // set the groupotions depending on topics
        var activeEllID = 'group_size';
        var activeElVal = $('#id_maxmembers').val();
        var nonActiveElVal = getTopicsNumb();

        adjustGropOptions(activeEllID, activeElVal, nonActiveElVal);
        $("#group_opt_numb").attr('disabled', 'disabled');
    }else{
    	//set the groupotions from the Moodle native inputs
    	if($('input[name=groupoption]:checked').val() == '0'){
            var activeEllID = 'group_size';
            var activeElVal = $('#id_maxmembers').val();
            var nonActiveElVal = $('#id_maxgroups').val();

            adjustGropOptions(activeEllID, activeElVal, nonActiveElVal);
        }else{
            var activeEllID = 'numb_of_groups';
            var activeElVal = $('#id_maxgroups').val();
            var nonActiveElVal = $('#id_maxmembers').val();

            adjustGropOptions(activeEllID, activeElVal, nonActiveElVal);
        }
    }
    
    
    if($('#id_evaluationmethod option:selected').val() != 0){
    	var opt = $('#id_evaluationmethod option:selected').val();
    	if(opt == '1'){
    		$('#max_points').prop('disabled', true);
    		$('#js_evaluationmethod option').prop('selected', false).filter('[value=grades]').prop('selected', true);
    	}else if(opt == '2'){
    		$('#js_evaluationmethod option').prop('selected', false).filter('[value=points]').prop('selected', true);
    		$('#max_points').prop('disabled', false);
    		$('#max_points').val($('#id_maxpoints').val());
    	}else if(opt == '3'){
    		$('#max_points').prop('disabled', true);
    		$('#js_evaluationmethod option').prop('selected', false).filter('[value=justpass]').prop('selected', true);
    	}else if(opt == '4'){
    		$('#max_points').prop('disabled', true);
    		$('#js_evaluationmethod option').prop('selected', false).filter('[value=novaluation]').prop('selected', true);
    	}
    }
    
    
    

    // End of Load Settings    
    
///////////////////////////////////////////////////////////////////////////////////////////////     
  
    
    
    $('.szenarioLabel').click(function(){
        if(!(typeof $("input[name='js_szenario']:checked").val() != 'undefined')){
            $('#js_szenarioWrapper').show('2000', 'swing');
        }
    });
    
    $("input[name='js_szenario']").change(function(){
        var szenario = $(this).val();
        setSzenario(szenario);
    });
    
    function setSzenario($szenario){
        if($szenario == 'project'){
        	$('#id_szenario option').prop('selected', false).filter('[value=1]').prop('selected', true);
        	
            $('#knowledfeInfo').text($('#knowledfeInfoProject').text());
            switchTopics('off');
            $('#headerTopics').removeClass('required').addClass('optional');
            $('#id_js_topics').prop('disabled', false);
            
            writeTextInput('#id_maxmembers', 0);
            writeTextInput('#id_maxgroups', 0);
            
        }else if($szenario == 'homework'){
        	$('#id_szenario option').prop('selected', false).filter('[value=2]').prop('selected', true);
        	
            $('#knowledfeInfo').text($('#knowledfeInfoHomework').text());
            switchTopics('off');
            $('#headerTopics').removeClass('required').addClass('optional');
            $('#id_js_topics').prop('disabled', false);
            
            writeTextInput('#id_maxmembers', 0);
            writeTextInput('#id_maxgroups', 0);
        }else if($szenario == 'presentation'){
        	$('#id_szenario option').prop('selected', false).filter('[value=3]').prop('selected', true);
        	
            $('#knowledfeInfo').text($('#knowledfeInfoPresentation').text());
            switchTopics('on');
            $('#headerTopics').removeClass('optional').addClass('required');
            $('#id_js_topics').prop('disabled', true);
            
            writeTextInput('#id_maxmembers', 0);
            writeTextInput('#id_maxgroups', getTopicsNumb());
        }
    }
    
    
    
    
 //if knowledge gets checked
    $('#id_js_knowledge').click(function(){
    	if ($('#id_knowledge').prop('checked')){
    		$('#id_knowledge').prop('checked',false);
    		$('#id_knowledgelines').attr('disabled', 'disabled');
            
            $("#js_knowledgeWrapper").hide('2000', 'swing');
    	}else{
    		$('#id_knowledge').prop('checked', true);
    		$('#id_knowledgelines').removeAttr('disabled');
            
            $("#js_knowledgeWrapper").show('2000', 'swing');
    	}
    	
    });
    
    //if topics gets checked
    $('#id_js_topics').click(function(){
    	if ($('#id_topics').prop('checked')){
            switchTopics('off');
    	}else{
            switchTopics('on');
    	}
    });
    
    function switchTopics($state){
        if($state == 'on'){
            $('#id_topics').prop('checked', true);
    		$('#id_topiclines').removeAttr('disabled');
            
            $('#id_js_topics').prop('checked',true);
            
            var activeElID = 'group_size';
            var activeElVal = 0;
            var nonActiveElVal = getTopicsNumb();
            adjustGropOptions(activeElID, activeElVal, nonActiveElVal);
            
            $("#group_opt_numb").attr('disabled', 'disabled');
            
            $("#js_topicsWrapper").show('2000', 'swing');
        }
        if($state == 'off'){
            $('#id_topics').prop('checked',false);
    		$('#id_topiclines').attr('disabled', 'disabled');
            
            $('#id_js_topics').prop('checked',false);
            
            var activeElID = 'group_size';
            var activeElVal = 0;
            var nonActiveElVal = 0;
            adjustGropOptions(activeElID, activeElVal, nonActiveElVal);
            
            $("#group_opt_numb").removeAttr('disabled');
            
            $("#js_topicsWrapper").hide('2000', 'swing');
        }
    }
    
    
    
    
    function addInput($wrapper, $cat, $value){
        $theID = parseInt($('.multi_field:last-child', $wrapper).attr('id').substr(8)) + 1
        $multifieldID = 'input' + $cat + $theID;

        // adds input field
        $('.multi_field:first-child', $wrapper).clone(true).attr('id',$multifieldID)
                                                            .appendTo($wrapper).find('input').val($value).focus();  
        addPreview($wrapper, $cat, $theID, $value);
    }
    
    function addPreview($wrapper, $cat, $theID, $value){
        $previewRowID = $cat + 'Row' +  $theID;
        
        if($cat == 'prk'){
            $('.knowlRow:first-child', '#preknowledges').clone(true).attr('id',$previewRowID)
                                                                .appendTo('#preknowledges').find('th').text($value);
        }
        if($cat == 'tpc'){
            $('.topicLi:first-child', '#previewTopics').clone(true).attr('id',$previewRowID)
                                                                .appendTo('#previewTopics').html('<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>' + $value);
        }
    }
    
    function removeInput($wrapper, $cat, $theID){
        if ($('.multi_field', $wrapper).length > 1){
            $previewRowID = $cat + 'Row' +  $theID;
            $multifieldID = 'input' + $cat + $theID;
            //remove Preview
            $('#' + $previewRowID).remove();
            //remove Input
            $('#' + $multifieldID).remove();
            
            //remove from Moodle native input field
            if($cat == 'prk'){
                synchronizePreknowledge();
            }
            if($cat == 'tpc'){
                synchronizeTopics();
                $('#numb_of_groups').val(getTopicsNumb());
                writeTextInput('#id_maxgroups', getTopicsNumb());
            }
        }
    }
    
    
    //dynamic inputs function
    $('.multi_field_wrapper').each(function dynamicInputs() {
        var $wrapper = $('.multi_fields', this);
        var $cat = $(this).parent().attr('id');
        
        //add new empty field on button
        $(".add_field", $(this)).click(function() {
            $value = '';
            addInput($wrapper, $cat, $value);
        });
        
        //removes field on button
        $('.multi_field .remove_field', $wrapper).click(function() {
            $theID = parseInt($(this).parent().attr('id').substr(8));
            
            removeInput($wrapper, $cat, $theID);    
        });
        
        
        // Create Preview and write to the native Moodle input
        $('.multi_field input:text', $wrapper).keyup(function() {
        	$previewRowID = ($cat + 'Row' + parseInt($(this).parent().attr('id').substr(8)));
                  if ($cat == 'prk'){
                      $('#' + $previewRowID).children('th').text($(this).val());
                      synchronizePreknowledge();
                  }
                  if ($cat == 'tpc'){
                      $('#' + $previewRowID).html('<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>' + $(this).val());
                      synchronizeTopics();
                      $('#numb_of_groups').val(getTopicsNumb());
                      writeTextInput('#id_maxgroups', getTopicsNumb());
                  }
              });
    });
    
        
  
    function synchronizePreknowledge(){
      stringOfPreknowledge = '';
      $('.js_preknowledgeInput').each(function(){
          if(!$(this).val() == ''){
            stringOfPreknowledge += $(this).val() + '\n';
          }
      });
      $('#id_knowledgelines').val(stringOfPreknowledge.slice(0, -1));
    }

    function synchronizeTopics(){
      stringOfTopics = '';
      $('.js_topicInput').each(function(){
          if(!$(this).val() == ''){
            stringOfTopics += $(this).val() + '\n';
          }
      });
      $('#id_topiclines').val(stringOfTopics.slice(0, -1));
    }
    
    

    //Groupoptions radiobutton listener
    $('input[name=group_opt]').change(function(e){
        var activeElVal = 0;
        var nonActiveElVal = 0;
        var activeElID = $(this).val();
        adjustGropOptions(activeElID, activeElVal, nonActiveElVal);
    });
    
    //Groupoptions values listener
    $('input[class=group_opt]').bind('keyup change', function(){
            var elID = $(this).attr('id');
            var elValue = $(this).val();
            if(elID == 'group_size'){
                writeTextInput('#id_maxmembers', elValue);
            }else{
                writeTextInput('#id_maxgroups', elValue);
            }
        });
    
    
    
    function adjustGropOptions($activeEllID, $activeElVal, $nonActiveElVal){
        if($activeEllID == 'group_size'){
        	$('#group_opt_size').prop('checked', true);
            $('#group_size').removeAttr('disabled').val($activeElVal);
            $('#numb_of_groups').attr('disabled', 'disabled').val($nonActiveElVal);
            
            //Moodle nativ fields
            $('#id_groupoption_0').prop('checked', true);
            $('#id_maxmembers').removeAttr('disabled');
            writeTextInput('#id_maxmembers', activeElVal);
            $('#id_maxgroups').attr('disabled', 'disabled');
            writeTextInput('#id_maxgroups', $nonActiveElVal);
            
        }else{
        	$('#group_opt_numb').prop('checked', true);
            $('#numb_of_groups').removeAttr('disabled').val($activeElVal);
            $('#group_size').attr('disabled', 'disabled').val($nonActiveElVal);
            
            //Moodle nativ fields
            $('#id_groupoption_1').prop('checked', true);
//            $('#id_maxgroups').removeAttr('disabled').val($activeElVal);
            $('#id_maxgroups').removeAttr('disabled');
            writeTextInput('#id_maxmembers', activeElVal);
            $('#id_maxmembers').attr('disabled', 'disabled');
            writeTextInput('#id_maxmembers', $nonActiveElVal);
        }
    }

    
    function getTopicsNumb(){
        var topicsCounter = 0;
        $('.js_topicInput').each(function(){
          if(!$(this).val() == ''){
            topicsCounter++;
          }
      });
        return topicsCounter;
    }    
    
    function writeTextInput($selectID, $value){
//    	$textVal = $value.toString();
        $($selectID).val($value);
    }
    
    // evaluation method listener
    $('#js_evaluationmethod').change(function(){
        if($(this).val()=='grades'){
            $('#id_evaluationmethod option').prop('selected', false).filter('[value=1]').prop('selected', true);
            $('#max_points').prop('disabled', true);
            $('#id_maxpoints').prop('disabled', true);
            $('#max_points').val(0);
            $('#id_maxpoints').val(0);
            
        }else if($(this).val()=='points'){
            $('#id_evaluationmethod option').prop('selected', false).filter('[value=2]').prop('selected', true);
            $('#max_points').prop('disabled', false);
            $('#id_maxpoints').prop('disabled', false);
            
        }else if($(this).val()=='justpass'){
            $('#id_evaluationmethod option').prop('selected', false).filter('[value=3]').prop('selected', true);
            $('#max_points').prop('disabled', true);
            $('#id_maxpoints').prop('disabled', true);
            $('#max_points').val(0);
            $('#id_maxpoints').val(0);
        }else if($(this).val()=='novaluation'){
            $('#id_evaluationmethod option').prop('selected', false).filter('[value=4]').prop('selected', true);
            $('#max_points').prop('disabled', true);
            $('#id_maxpoints').prop('disabled', true);
            $('#max_points').val(0);
            $('#id_maxpoints').val(0);
        }else if($(this).val()=='chooseM'){
            $('#id_evaluationmethod option').prop('selected', false).filter('[value=0]').prop('selected', true);
            $('#max_points').prop('disabled', true);
            $('#id_maxpoints').prop('disabled', true);
            $('#max_points').val(0);
            $('#id_maxpoints').val(0);
        }
    });
    
    
    // write max points to Moodle native Input
    $('#max_points').bind('keyup change', function(){
        $('#id_maxpoints').val($(this).val());
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
