/**
 * moodle-mod_groupformation JavaScript for the settings page (scenario selection)
 * https://github.com/moodlepeers/moodle-mod_groupformation
 *
 *
 * @author Eduard Gallwas, Johannes Konert, René Röpke, Neora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require(['jquery', 'jqueryui'], function($) {
    $(document).ready(function () {
        // Hide validation error alerts and show them if needed.
        // If css attribute "display:none" and show on validation error, they will not displayed properly.
        $(".js_errors").hide();

        $(".settings_info").hide();

        $("#non-js-content").hide();
        $("#js-content").show();

        var studentsInCourse = $('#studentsInCourse').text();

        var stringOfPreknowledge = "";

        var stringOfTopics = "";

        var stringAddInput = $('#stringAddInput').text();

        loadGroupformationSettings();

        if (!($('#nochangespossible').css('display') == 'none')) {

            $('#js-content').find('input, button, select').prop('disabled', true);

            if ($('#id_topics').attr('checked', 'checked')) {
                $('#js-content').find('#group_opt_size, #group_size, #group_opt_numb').removeAttr('disabled');
            } else {
                $('#js-content').find('#group_size, #group_size, #group_opt_numb, #numb_of_groups').removeAttr('disabled');
            }

            $("select[id*='id_timeopen']").prop('disabled', true);
            $("input[id*='id_timeopen']").prop('disabled', true);
            $("select[id*='id_timeclose']").prop('disabled', true);
            $("input[id*='id_timeclose']").prop('disabled', true);
        }

        $('.szenarioLabel').click(function () {
            if (!(typeof $("input[name='js_scenario']:checked").val() != 'undefined')) {
                $('#js_scenarioWrapper').show('2000', 'swing');
            }
        });

        $('.sortable_topics').sortable({
            axis: 'y',
            stop: function (event, ui) {
                var data = $(this).sortable('serialize');
                $('span#order').text(data);
            }
        });

        $("input[name='js_scenario']").change(function () {
            setScenario($(this).val());
        });

        // Check errors.
        if ($('.error').length > 0) {
            var messages = $('span.error').map(function (i) {
                return $(this).text();
            });

            var ids = $('div.error').map(function (i) {
                return '#' + $(this).parent().prop('id').substr(9) + '_error';
            });

            $.each(ids, function (index, value) {
                $(value).show();
                $(value).find('p').text(messages.get(index));

            });
        }

        // If oob gets checked

        $('#id_js_oneofbin').click(function () {
            if ($('#id_oneofbin').prop('checked')) {
                $('#id_oneofbin').prop('checked', false);
                $('#id_oneofbinquestion').attr('disabled', 'disabled');
                $('#id_oneofbinanswers').attr('disabled', 'disabled');
                $('#id_oneofbinrelation').attr('disabled', 'disabled');
                $('#id_oneofbinimportance').attr('disabled', 'disabled');

                $("#js_oneOfBinWrapper").hide('2000', 'swing');
            } else {
                $('#id_oneofbin').prop('checked', true);
                $('#id_oneofbinquestion').removeAttr('disabled');
                $('#id_oneofbinanswers').removeAttr('disabled');
                $('#id_oneofbinrealtion').removeAttr('disabled');
                $('#id_oneofbinimportance').removeAttr('disabled');

                $("#js_oneOfBinWrapper").show('2000', 'swing');
            }

        });

        // If knowledge gets checked.
        $('#id_js_knowledge').click(function () {
            if ($('#id_knowledge').prop('checked')) {
                $('#id_knowledge').prop('checked', false);
                $('#id_knowledgelines').attr('disabled', 'disabled');

                $("#js_knowledgeWrapper").hide('2000', 'swing');
            } else {
                $('#id_knowledge').prop('checked', true);
                $('#id_knowledgelines').removeAttr('disabled');

                $("#js_knowledgeWrapper").show('2000', 'swing');
            }

        });

        /*
        $('#gfOneOfBinImpValues').val().change(funciton(){
            $("#gf_one_of_bin_Importance").html(5);
            console.log(es wurde etwas geändert);
        });
        */

        $('#gf_importance_slider').change(function(){
            var old = $('#gf_one_of_bin_Importance').html();
            old = old.replace(/\ /g,"");
            var r = old.split("");
            if (r[r.length -2] != 1){
                old = old.replace("1", "");
            }
            var l = old.replace(r[r.length-2], " " + $('#gf_importance_slider').val());
            $('#gf_one_of_bin_Importance').html(l);
        });

        // If topics gets checked.
        $('#id_js_topics').click(function () {
            if ($('#id_topics').prop('checked')) {
                switchTopics('off');
                switchKnowledge('enable');
            } else {
                switchTopics('on');
                switchKnowledge('off');
            }
        });

        // Dynamic inputs function.
        $('.multi_field_wrapper').each(function dynamicInputs() {
            var wrapper = $('.multi_fields', this);
            var cat = $(this).parent().attr('id');

            // Add new empty field with click on last input field.
            $('.multi_field input:text', wrapper).bind("click focus", function () {
                if ($('.multi_field:last-child', wrapper).attr('id') == $(this).parent().attr('id')) {
                    value = '';
                    addInput(wrapper, cat, value);
                }
            });

            // Removes field on button.
            $('.multi_field .remove_field', wrapper).click(function () {
                theID = parseInt($(this).parent().attr('id').substr(8));

                removeInput(wrapper, cat, theID);
            });

            // Create Preview and write to the native Moodle input.
            $('.multi_field input:text', wrapper).keyup(function () {
                $previewRowID = (cat + 'Row' + parseInt($(this).parent().attr('id').substr(8)));
                if (cat == 'prk') {
                    $('#' + $previewRowID).children('th').text($(this).val());
                    synchronizeKnowledge();
                }
                if (cat == 'tpc') {
                    $('#' + $previewRowID).html('<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>');
                    $('#' + $previewRowID).append(document.createTextNode($(this).val()));
                    synchronizeTopics();
                    computeGroupSizeParameters(0, countTopics());
                    setGroupSettings();
                }
                if (cat == 'oob'){
                    $('#' + $previewRowID).text($(this).val());
                    synchronizeoob();
                }
            });
        });

        // Group options radiobutton listener.
        $('input[name=group_opt]').click(function (e) {
            var activeElementID = $(this).val();
            adjustGroupSizeOptions(activeElementID);
        });

        // Group options values listener.
        $('input[class=group_opt]').bind('keyup change', function () {
            var elementID = $(this).attr('id');
            var elementValue = $(this).val();

            if (elementID == 'group_size') {
                computeGroupSizeParameters(elementValue, 0);
                setGroupSettings();
            } else {
                computeGroupSizeParameters(0, elementValue);
                setGroupSettings();
            }
        });

        // Write max points to Moodle native Input.
        $('#max_points').bind('keyup change', function () {
            $('#id_maxpoints').val($(this).val());
        });

        $('#js_groupname').keyup(function () {
            $('#id_groupname').val($(this).val());
        });

        $('#id_js_onlyactivestudents').click(function () {
            if ($('#id_onlyactivestudents').prop('checked')) {
                $('#id_onlyactivestudents').prop('checked', false);
            } else {
                $('#id_onlyactivestudents').prop('checked', true);
            }
        });

        $('#id_js_allanswersrequired').click(function () {
            if ($('#id_allanswersrequired').prop('checked')) {
                $('#id_allanswersrequired').prop('checked', false);
            } else {
                $('#id_allanswersrequired').prop('checked', true);
            }
        });

        $('#id_js_emailnotifications').click(function () {
            if ($('#id_emailnotifications').prop('checked')) {
                $('#id_emailnotifications').prop('checked', false);
            } else {
                $('#id_emailnotifications').prop('checked', true);
            }
        });

        // Evaluation method listener.
        $('#js_evaluationmethod').change(function () {
            if ($(this).val() == 'grades') {
                $('#id_evaluationmethod option').prop('selected', false).filter('[value=1]').prop('selected', true);
                $('#max_points_wrapper').prop('disabled', true).hide();

            } else if ($(this).val() == 'points') {
                $('#id_evaluationmethod option').prop('selected', false).filter('[value=2]').prop('selected', true);
                $('#max_points_wrapper').show();
                $('#id_maxpoints').prop('disabled', false).val($('#max_points').val());

            } else if ($(this).val() == 'justpass') {
                $('#id_evaluationmethod option').prop('selected', false).filter('[value=3]').prop('selected', true);
                $('#max_points_wrapper').hide();
            } else if ($(this).val() == 'novaluation') {
                $('#id_evaluationmethod option').prop('selected', false).filter('[value=4]').prop('selected', true);
                $('#max_points_wrapper').hide();

            } else if ($(this).val() == 'chooseM') {
                $('#id_evaluationmethod option').prop('selected', false).filter('[value=0]').prop('selected', true);
                $('#max_points_wrapper').hide();

            }
        });

        /**
         * Switch state of radio button for knowledge questions
         *
         * @param state
         */
        function switchKnowledge(state) {
            if (state == 'enable') {
                $('#id_knowledge').removeAttr('disabled');
                $('#id_js_knowledge').removeAttr('disabled');
            }
            if (state == 'disable') {
                $('#id_knowledge').attr('disabled', 'disabled');
                $('#id_js_knowledge').attr('disabled', 'disabled');
            }
            if (state == 'off') {
                $('#id_knowledge').prop('checked', false);
                $('#id_knowledge').attr('disabled', 'disabled');

                $('#id_js_knowledge').prop('checked', false);
                $('#id_js_knowledge').attr('disabled', 'disabled');

                $("#js_knowledgeWrapper").hide('2000', 'swing');
            }
        }

        /**
         * Switch state of radio button for topics
         *
         * @param state
         */
        function switchTopics(state) {
            if (state == 'enable') {
                $('#id_topics').removeAttr('disabled');
                $('#id_js_topics').removeAttr('disabled');
            }
            if (state == 'disable') {
                $('#id_topics').attr('disabled', 'disabled');
                $('#id_js_topics').attr('disabled', 'disabled');
            }
            if (state == 'on') {
                $('#id_topics').prop('checked', true);
                $('#id_topiclines').removeAttr('disabled');

                $('#id_js_topics').prop('checked', true);

                adjustGroupSizeOptions('none');

                $('#groupSettingsInfo').show('2000', 'swing');
                $("#js_topicsWrapper").show('2000', 'swing');
            }
            if (state == 'off') {
                $('#id_topics').prop('checked', false);
                $('#id_topiclines').attr('disabled', 'disabled');

                $('#id_js_topics').prop('checked', false);

                adjustGroupSizeOptions('group_size');

                $("#group_opt_numb").removeAttr('disabled');
                $("#group_opt_size").removeAttr('disabled');

                $('#groupSettingsInfo').hide('2000', 'swing');
                $("#js_topicsWrapper").hide('2000', 'swing');
            }
        }

        /**
         * Adds new input field for knowledge or topic questions
         *
         * @param wrapper
         * @param cat
         * @param value
         */
        function addInput(wrapper, cat, value) {
            $thisID = parseInt($('.multi_field:last-child', wrapper).attr('id').substr(8));
            $theNextID = $thisID + 1;

            $thisMultifieldID = 'input' + cat + $thisID;
            $nextMultifieldID = 'input' + cat + $theNextID;

            // Last field change style.
            $('.multi_field:last-child', wrapper).find('input[type="text"]').removeClass('lastInput').removeAttr('placeholder');
            $('.multi_field:last-child', wrapper).find('button').removeAttr('disabled');

            // Add input field.
            $('.multi_field:first-child', wrapper).clone(true).attr('id', $nextMultifieldID)
                .appendTo(wrapper).find('input').val(value).addClass('lastInput').attr('placeholder', stringAddInput);
            $('.multi_field:last-child', wrapper).find('button').attr('disabled', true);

            addPreview(wrapper, cat, $theNextID, value);
        }

        /**
         * Adds new preview element for knowledge or topics questions
         *
         * @param wrapper
         * @param cat
         * @param theID
         * @param value
         */
        function addPreview(wrapper, cat, theID, value) {
            $previewRowID = cat + 'Row' + theID;

            if (cat == 'prk') {
                $('.knowlRow:first-child', '#preknowledges').clone(true).attr('id', $previewRowID)
                    .appendTo('#preknowledges').find('th').text(value);
            }
            if (cat == 'tpc') {
                $('.topicLi:first-child', '#previewTopics').clone(true).attr('id', $previewRowID)
                    .appendTo('#previewTopics').html('<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>').append(document.createTextNode(value));
            }
            if (cat == 'oob'){
                $('.oobRow:first-child', '#oobpreviewdd').clone(true).attr('id', $previewRowID).appendTo('#oobpreviewdd').text(value);
            }
        }

        /**
         * Remove input for knowledge or topics question
         *
         * @param wrapper
         * @param cat
         * @param theID
         */
        function removeInput(wrapper, cat, theID) {
            if ($('.multi_field', wrapper).length > 1) {
                $previewRowID = cat + 'Row' + theID;
                $multifieldID = 'input' + cat + theID;
                // Remove Preview.
                $('#' + $previewRowID).remove();
                // Remove Input.
                $('#' + $multifieldID).remove();
                // Remove from Moodle native input field.
                if (cat == 'prk') {
                    synchronizeKnowledge();
                }
                if (cat == 'tpc') {
                    synchronizeTopics();
                    computeGroupSizeParameters(0, countTopics());
                    setGroupSettings();
                }
            }
        }

        /**
         * Synchronizes knowledge inputs and preview
         */
        function synchronizeKnowledge() {
            stringOfPreknowledge = '';
            $('.js_preknowledgeInput').each(function () {
                if (!$(this).val() == '') {
                    stringOfPreknowledge += $(this).val() + '\n';
                }
            });
            $('#id_knowledgelines').val(stringOfPreknowledge.slice(0, -1));
        }

        /**
         * Synchronizes topic inputs and preview
         */
        function synchronizeTopics() {
            stringOfTopics = '';
            $('.js_topicInput').each(function () {
                if (!$(this).val() == '') {
                    stringOfTopics += $(this).val() + '\n';
                }
            });
            $('#id_topiclines').val(stringOfTopics.slice(0, -1));
        }

        function synchronizeoob() {
            stringOfPreAnswers = '';
            $('.js_preknowledgeInput').each(function () {
                if (!$(this).val() == '') {
                    stringOfPreAnswers += $(this).val() + '\n';
                }
            });
            $('#id_oneofbinanswers').val(stringOfPreAnswers.slice(0, -1));
        }

        /**
         * Loads groupformation data from non-js fields
         */
        function loadGroupformationSettings() {

            // Load the scenario which been choosen before.
            if ($('#id_szenario option:selected').val() != 0) {

                $('#js_scenarioWrapper').show('2000', 'swing');

                var scenario = $('#id_szenario option:selected').val();

                if (scenario == 1) {

                    // Check scenario box.
                    $("input[name='js_scenario'][value='project']").attr("checked", "checked");

                    // Check browser support first, before delete this.
                    $('#knowledgeInfo').text($('#knowledgeInfoProject').text());
                    $('#topicsStateLabel').removeClass('required').addClass('optional');

                } else if (scenario == 2) {

                    // Check scenario box.
                    $("input[name='js_scenario'][value='homework']").attr("checked", "checked");

                    // Check browser support first, before delete this.
                    $('#knowledgeInfo').text($('#knowledgeInfoHomework').text());
                    $('#topicsStateLabel').removeClass('required').addClass('optional');

                } else if (scenario == 3) {

                    // Check scenario box.
                    $("input[name='js_scenario'][value='presentation']").attr("checked", "checked");

                    // Check browser support first, before delete this.
                    $('#knowledgeInfo').text($('#knowledgeInfoPresentation').text());
                    $('#topicsStateLabel').removeClass('optional').addClass('required');

                    switchTopics('on');
                    $('#id_js_topics').prop('disabled', true);

                    adjustGroupSizeOptions('none');

                    $("#js_topicsWrapper").show('2000', 'swing');
                }
            }

            // If knowledge was checked before.
            if ($('#id_knowledge').prop('checked')) {
                $('#id_js_knowledge').prop('checked', true);
                $('#id_knowledge').prop('checked', true);
                $("#js_knowledgeWrapper").show('2000', 'swing');

                // Get the value of Moodle nativ field #id_knowledgelines, parse it and create dynamic input fields.
                var lines = $('textarea[name=knowledgelines]').val().split('\n');
                wrapper = $('#prk').find('.multi_fields');
                cat = 'prk';
                $.each(lines, function () {
                    addInput(wrapper, cat, this);
                });
                for (var i = 0, l = 3; i < l; i++) {
                    // Remove the first 3 dynamic fields which been created by default.
                    removeInput(wrapper, cat, i);
                }
                addInput(wrapper, cat, '');
            }

            // If topics was checked before.
            if ($('#id_topics').prop('checked')) {
                $('#id_js_topics').prop('checked', true);
                $('#id_topics').prop('checked', true);
                $("#js_topicsWrapper").show('2000', 'swing');

                // Get the value of Moodle nativ field #id_topiclines, parse it and create dynamic input fields.
                var lines = $('textarea[name=topiclines]').val().split('\n');
                wrapper = $('#tpc').find('.multi_fields');
                cat = 'tpc';
                $.each(lines, function () {
                    addInput(wrapper, cat, this);
                });
                for (var i = 0, l = 3; i < l; i++) {
                    // Remove the first 3 dynamic fields which been created by default.
                    removeInput(wrapper, cat, i);
                }
                addInput(wrapper, cat, '');
                // Set the groupotions depending on topics.
                adjustGroupSizeOptions('none');
                $('#groupSettingsInfo').show('2000', 'swing');
                switchKnowledge('off');
                switchKnowledge('disable');

            } else {
                // Set the groupotions from the Moodle native inputs.
                if ($('input[name=groupoption]:checked').val() == '0') {
                    computeGroupSizeParameters($('#id_maxmembers').val(), 0);

                } else {
                    computeGroupSizeParameters(0, $('#id_maxgroups').val());
                }
            }

            if ($('#id_evaluationmethod option:selected').val() != 0) {
                var opt = $('#id_evaluationmethod option:selected').val();
                if (opt == '1') {
                    $('#max_points_wrapper').hide();
                    $('#js_evaluationmethod option').prop('selected', false).filter('[value=grades]').prop('selected', true);
                } else if (opt == '2') {
                    $('#js_evaluationmethod option').prop('selected', false).filter('[value=points]').prop('selected', true);
                    $('#max_points_wrapper').show();
                    $('#max_points').val($('#id_maxpoints').val());
                } else if (opt == '3') {
                    $('#max_points_wrapper').hide();
                    $('#js_evaluationmethod option').prop('selected', false).filter('[value=justpass]').prop('selected', true);
                } else if (opt == '4') {
                    $('#max_points_wrapper').hide();
                    $('#js_evaluationmethod option').prop('selected', false).filter('[value=novaluation]').prop('selected', true);
                }
            }
            $('#js_groupname').val($('#id_groupname').val());
            if ($('#id_onlyactivestudents').prop('checked')) {
                $('#id_js_onlyactivestudents').prop('checked', true);
            }

            if ($('#id_emailnotifications').prop('checked')) {
                $('#id_js_emailnotifications').prop('checked', true);
            }

            if ($('#id_allanswersrequired').prop('checked')) {
                $('#id_js_allanswersrequired').prop('checked', true);
            }

        }

        /**
         * Sets scenario and resets all relevant options
         *
         * @param scenario
         */
        function setScenario(scenario) {

            switchKnowledge('enable');

            if (scenario == 'project') {

                // Deactivate selected scenario and select scenario 'project'.
                $('#id_szenario option').prop('selected', false).filter('[value=1]').prop('selected', true);

                // Update info-text for knowledge questions.
                var infoText = $('#knowledgeInfoProject').text();
                $('#knowledgeInfo').text(infoText);

                switchTopics('off');
                $('#topicsStateLabel').removeClass('required').addClass('optional');
                $('#id_js_topics').prop('disabled', false);

                setGroupSettings();

            } else if (scenario == 'homework') {

                // Deactivate selected scenario and select scenario 'homework'.
                $('#id_szenario option').prop('selected', false).filter('[value=2]').prop('selected', true);

                // Update info-text for knowledge questions.
                var infoText = $('#knowledgeInfoHomework').text();
                $('#knowledgeInfo').text(infoText);

                switchTopics('off');

                $('#topicsStateLabel').removeClass('required').addClass('optional');
                $('#id_js_topics').prop('disabled', false);

                setGroupSettings();

            } else if (scenario == 'presentation') {

                // Deactivate selected scenario and select scenario 'presentation'.
                $('#id_szenario option').prop('selected', false).filter('[value=3]').prop('selected', true);

                // Update info-text for knowledge questions.
                var infoText = $('#knowledgeInfoPresentation').text();
                $('#knowledgeInfo').text(infoText);

                // Switch optional hint to required asterisk.
                $('#topicsStateLabel').removeClass('optional').addClass('required');

                switchTopics('on');
                switchTopics('disable');

                switchKnowledge('off');
                switchKnowledge('disable');

                setGroupSettings();
            }
        }

        /**
         * Adjust group size options
         *
         * @param activeElementID
         */
        function adjustGroupSizeOptions(activeElementID) {

            if (activeElementID == 'group_size') {
                $('#group_opt_size').prop('checked', true);
                $('#group_size').removeAttr('disabled').val(0);
                $('#numb_of_groups').attr('disabled', 'disabled').val(0);

                // Moodle native fields.
                $('#id_groupoption_0').prop('checked', true);
                $('#id_maxmembers').removeAttr('disabled');
                $('#id_maxgroups').attr('disabled', 'disabled');

                setGroupSettings();

            } else if (activeElementID == 'numb_of_groups') {
                $('#group_opt_numb').prop('checked', true);
                $('#numb_of_groups').removeAttr('disabled').val(0);
                $('#group_size').attr('disabled', 'disabled').val(0);

                // Moodle native fields.
                $('#id_groupoption_1').prop('checked', true);
                $('#id_maxgroups').removeAttr('disabled');
                $('#id_maxmembers').attr('disabled', 'disabled');

                setGroupSettings();

            } else {
                $('#group_opt_numb').prop('checked', true);

                $("#group_size").attr('disabled', 'disabled');
                $("#numb_of_groups").attr('disabled', 'disabled');
                $("#group_opt_numb").attr('disabled', 'disabled');
                $("#group_opt_size").attr('disabled', 'disabled');

                // Moodle native fields.
                $('#id_groupoption_0').prop('checked', true);
                $('#id_maxmembers').removeAttr('disabled');
                $('#id_maxgroups').attr('disabled', 'disabled');

                computeGroupSizeParameters(0, countTopics());
                setGroupSettings();
            }
        }

        /**
         * Computes group size parameters
         *
         * @param maxMembers
         * @param maxGroups
         * @returns {boolean}
         */
        function computeGroupSizeParameters(maxMembers, maxGroups) {
            if (maxMembers == 0) {
                maxMembers = Math.ceil(studentsInCourse / maxGroups);
                if (maxMembers == 0) {
                    $('#group_size').val(1);
                }
            } else if (maxGroups == 0) {
                maxGroups = Math.ceil(studentsInCourse / maxMembers);
                $('#numb_of_groups').val(maxGroups);
            } else {
                $('#group_size').val(maxMembers);
                $('#numb_of_groups').val(maxGroups);
            }

            $('#group_size').val(maxMembers);
            $('#numb_of_groups').val(maxGroups);
        }

        /**
         * Counts topics
         *
         * @returns {number}
         */
        function countTopics() {
            var topicsCounter = 0;

            $('.js_topicInput').each(function () {
                if (!$(this).val() == '') {
                    topicsCounter++;
                }
            });

            return topicsCounter;
        }

        /**
         * Sets group settings
         */
        function setGroupSettings() {
            $('#id_maxgroups').val($('#numb_of_groups').val());
            $('#id_maxmembers').val($('#group_size').val());
        }

        function updateBtnWrapp() {
            $(".persist-area").each(function () {

                var el = $(this),
                    offset = el.offset(),
                    scrollTop = $(window).scrollTop(),
                    floatingWrapper = $(".floatingWrapper", this);

                if ((scrollTop > offset.top) && (scrollTop < offset.top + el.height())) {
                    floatingWrapper.css({
                        "visibility": "visible"
                    });
                } else {
                    floatingWrapper.css({
                        "visibility": "hidden"
                    });
                }
            });
        }

        // DOM Ready.
        $(function () {
            var clonedWrapper,
                theWidth = $('.col_100 h4').width();

            $(".persist-area").each(function () {
                clonedWrapper = $(".btn_wrap", this);
                clonedWrapper
                    .before(clonedWrapper.clone(true))
                    .css('width', theWidth)
                    .addClass("floatingWrapper");

            });
            $(window).scroll(updateBtnWrapp).trigger("scroll");
        });

        // Datepicker + validation of dates on submit.

        $.datepicker.regional['de'] = {
            dateFormat: 'dd.mm.yy',
            monthNames: ['Januar', 'Februar', 'M\u00e4rz', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'],
            dayNames: ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'],
            dayNamesMin: ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa']
        };

        // Initialize Datepicker.
        $("#startDate, #endDate").datepicker();

        $.datepicker.setDefaults($.datepicker.regional['de']);

        $("#startDate").datepicker("setDate", new Date()); // Set default startDate as current date.
        $('#endDate').datepicker("setDate", "+7");

        // Set endDate + 7 Days from startDate by default.
        $('#startDate').change(function () {
            var date = $(this).datepicker('getDate');
            date.setDate(date.getDate() + 7); // Add 7 days.
            $('#endDate').datepicker('setDate', date); // Set as default.
        });

        // Validating the docent form page 2.
        $("#docent_settings_2").submit(function (event) {
            var proceed = true;

            var startDate = $("#startDate").datepicker("getDate");
            var endDate = $("#endDate").datepicker("getDate");

            // Check the date.
            if (startDate >= endDate) {
                proceed = false;

                $('#error_date').css({display: "inline-block"});
            }

            // If form is valid submit form.
            if (proceed) {
                return true;
            }

            event.preventDefault(); // Prevent submitting .

            scrollTo('.errors'); // Scroll to the error messages.

        });

        // Scroll to function.
        function scrollTo($param) {
            $('html, body').animate({
                scrollTop: $($param).offset().top
            }, 80);
        }


    });
});
