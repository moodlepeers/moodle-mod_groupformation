<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * The main groupformation configuration form
 *
 * @package mod_groupformation
 * @copyright 2014 Nora Wester
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die ('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/groupformation/lib.php'); // Not in the template.
require_once($CFG->dirroot . '/mod/groupformation/locallib.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/xml_loader.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/util.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/define_file.php');

class mod_groupformation_mod_form extends moodleform_mod {
    private $store;

    /**
     * (non-PHPdoc)
     *
     * @see moodleform::definition()
     */
    public function definition() {
        global $PAGE, $USER;

        $this->store = new mod_groupformation_storage_manager ($this->_instance);

        // Log access to page.
        groupformation_info($USER->id, $this->_instance, '<view_settings>');

        // Import jQuery and js file.
        groupformation_add_jquery($PAGE, 'settings_functions.js');

        $mform = &$this->_form;

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->setExpanded('general');

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('groupformationname', 'groupformation'), array(
            'size' => '64'));
        if (!empty ($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        // Adding the standard "intro" and "introformat" fields.
        $this->standard_intro_elements();

        // Adding the availability settings.
        $mform->addElement('header', 'timinghdr', get_string('availability'));
        $mform->setExpanded('timinghdr');
        // No changes possible hint.
        $changemsg = '<div class="fitem" id="nochangespossible"';
        if (!$this->store->is_editable()) {
            $changemsg .= ' ><span value="1"';
        } else {
            $changemsg .= ' style="display:none;"><span value="0"';
        }
        $changemsg .= ' style="color:red;">' . get_string('availability_nochangespossible', 'groupformation') .
            '</span></div>';
        $mform->addElement('html', $changemsg);

        $mform->addElement('date_time_selector', 'timeopen', get_string('feedbackopen', 'feedback'), array(
            'optional' => true));
        $mform->addElement('date_time_selector', 'timeclose', get_string('feedbackclose', 'feedback'), array(
            'optional' => true));
        // Adding the rest of groupformation settings, spreeading all them into this fieldset.
        $mform->addElement('header', 'groupformationsettings', get_string('groupformationsettings', 'groupformation'));
        $mform->setExpanded('groupformationsettings');

        $this->generate_html_for_non_js($mform);

        $this->generate_html_for_js($mform);

        // Add standard grading elements.
        $this->standard_grading_coursemodule_elements();

        $features = new stdClass();
        $features->groups = true;
        $features->groupings = true;
        $features->groupmembersonly = true;

        $this->standard_coursemodule_elements($features);

        $this->apply_admin_defaults();

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();
    }

    /**
     * (non-PHPdoc)
     *
     * @see moodleform_mod::validation()
     */
    public function validation($data, $files) {
        $errors = array();

        // Check if szenario is selected.
        if ($data ['szenario'] == 0) {
            $errors ['szenario'] = get_string('scenario_error', 'groupformation');
        }

        // Check if groupname is too long.
        if (strlen($data ['groupname']) > 100) {
            $errors ['groupname'] = get_string('groupname_error', 'groupformation');
        }

        // Check if maxmembers or maxgroups is selected and number is chosen.
        if ($data ['groupoption'] == 0) {
            if (!(is_numeric($data ['maxmembers'])) || !(intval($data ['maxmembers']) > 0)) {
                $errors ['maxmembers'] = get_string('maxmembers_error', 'groupformation');
            }
        } else if ($data ['groupoption'] == 1) {
            if (!(is_numeric($data ['maxgroups'])) || !(intval($data ['maxgroups']) > 0)) {
                $errors ['maxgroups'] = get_string('maxgroups_error', 'groupformation');
            }
        }

        // Check if evaluation method is selected.
        if ($data ['evaluationmethod'] == 0) {
            $errors ['evaluationmethod'] = get_string('evaluationmethod_error', 'groupformation');
        }

        if ($data ['evaluationmethod'] == 2 &&
            (!(is_numeric($data ['maxpoints'])) || !(intval($data ['maxpoints']) <= 100) ||
                !(intval($data ['maxpoints']) > 0))
        ) {
            $errors ['maxpoints'] = get_string('maxpoints_error', 'groupformation');
        }

        return $errors;
    }

    /**
     * generates HTML code for JS version
     *
     * @param unknown $mform
     */
    private function generate_html_for_js(&$mform) {
        global $PAGE;
        // Open div tag for js related content.
        $mform->addElement('html', '<div id="js-content" style="display:none;">');

        $mform->addElement('html', mod_groupformation_util::get_info_text_for_teacher(false));
        // Add scenario related HTML.
        $mform->addElement('html', '
                    <div class="gf_settings_pad">
                <div class="gf_pad_header">' . get_string('scenario_description', 'groupformation') . '
                    <span class="required"></span>
                </div>
                <div class="js_errors" id="szenario_error">
                    <p></p>
                </div>

                <div id="szenarioradios">
                    <div class="grid gf_grid_m_minus">
                    <div class="col_m_33">
                        <input type="radio" name="js_szenario" id="project" value="project"  />
                        <label class="col_m_100 szenarioLabel" id="label_project" for="project" ><div class="sz_header">' .
            get_string('scenario_projectteams', 'groupformation')
            . '</div><p><small>' .
            get_string('scenario_projectteams_description', 'groupformation') . '</small></p>
		                                </label>
                </div>
                <div class="col_m_33">
                    <input type="radio" name="js_szenario" id="homework" value="homework" />
                    <label class="col_m_100 szenarioLabel" id="label_homework" for="homework" ><div class="sz_header">' .
            get_string('scenario_homeworkgroups', 'groupformation') .
            '</div>
                        <p><small>' .
            get_string('scenario_homeworkgroups_description', 'groupformation') . '</small></p>
                    </label>
                </div>
                         <div class="col_m_33">
                            <input type="radio" name="js_szenario" id="presentation" value="presentation" />
                            <label class="col_m_100 szenarioLabel" for="presentation"><div class="sz_header">' .
            get_string('scenario_presentationgroups', 'groupformation') . '</div>
                                <p><small>' .
            get_string('scenario_presentationgroups_description', 'groupformation') . '</small></p>
                            </label>
                        </div>
                    </div> <!-- /grid  -->
                </div> <!-- /szenarioRadios -->
            </div> <!-- /gf_setting_pad -->
		');

        // Wrapper of the szenario.
        $mform->addElement('html', '<div id="js_szenarioWrapper">');

        // Wrapper for preknowledge.
        $mform->addElement('html', '<div class="gf_settings_pad">');

        // Add checkbox preknowledge.
        $mform->addElement('html', '
                    <div class="gf_pad_header">
                        <label class="gf_label" for="id_js_knowledge">
                          <input type="checkbox" id="id_js_knowledge" name="chbKnowledge" value="wantKnowledge" />
                          ' . get_string('knowledge_description', 'groupformation') . '</label><span class="optional"></span>
                    </div>');

        // Add dynamic input fields preknowledge and Preview.
        $mform->addElement('html', '
					<div class="gf_pad_content" id="js_knowledgeWrapper">
					<!-- <p>' . get_string('knowledge_description_extended', 'groupformation') . '</p> -->
                       <p id="knowledeInfo"></p>
						<p id="knowledeInfoProject" style="display:none;">' .
            get_string('knowledge_info_project', 'groupformation') . '</p>
                        <p id="knowledeInfoHomework" style="display:none;">' .
            get_string('knowledge_info_homework', 'groupformation') . '</p>
            <p id="knowledeInfoPresentation" style="display:none;">' .
            get_string('knowledge_info_presentation', 'groupformation') . '</p>
            <p id="stringAddInput" style="display:none;">' . get_string('add_line', 'groupformation') . '</p>
            <div class="grid">
            <div id="prk">
            <div class="multi_field_wrapper persist-area">
                                <div class="col_m_50">
            <!-- <div id="" class="btn_wrap">
            <label>
            <button type="button" class="add_field gf_button gf_button_circle gf_button_small"></button>' .
            get_string('add_line', 'groupformation') . '</label>
                                </div> -->
                <h5>' . get_string('input', 'groupformation') . '</h5>
                <div class="multi_fields">
                    <div class="multi_field" id="inputprk0">
                        <input class="respwidth js_preknowledgeInput" type="text">
                        <button type="button" class="remove_field gf_button gf_button_circle gf_button_small"></button>
                    </div>
                    <div class="multi_field" id="inputprk1">
                        <input class="respwidth js_preknowledgeInput" type="text">
                        <button type="button" class="remove_field gf_button gf_button_circle gf_button_small"></button>
                    </div>
                    <div class="multi_field" id="inputprk2">
                        <input class="respwidth js_preknowledgeInput lastInput" type="text" placeholder="' .
            get_string('add_line', 'groupformation') . '">
                        <button type="button" class="remove_field gf_button gf_button_circle gf_button_small" disabled="disabled">
                        </button>
                    </div>
                        </div>
                    </div> <!-- /col_50 -->
                    <!-- Die Vorschau      -->
                    <div class="col_m_50">
                        <h5>' . get_string('preview', 'groupformation') . '</h5>
                    <div class="col_m_100">' .
            /* '<h4 class="view_on_mobile">'.get_string('knowledge_question','groupformation').'</h4>'. */

            '<table class="responsive-table">
            <colgroup><col class="firstCol">
                <col width="36%">
            </colgroup>

            <thead>
              <tr>
            <th scope="col">' .
            get_string('knowledge_question', 'groupformation') . '</th>
            <th scope="col"><div class="legend">' .
            get_string('knowledge_scale', 'groupformation') . '</div></th>
            </tr>
            </thead>
            <tbody id="preknowledges">
            <tr class="knowlRow" id="prkRow0">
            <th scope="row">' .
            get_string('knowledge_dummy', 'groupformation') . ' 1</th>
                                            <td data-title="' .
            get_string('knowledge_scale', 'groupformation') . '" class="range"><span >0</span>
            <input type="range" min="0" max="100" value="0" /><span>100</span></td>
                                          </tr>
                                        <tr class="knowlRow" id="prkRow1">
                                            <th scope="row">' .
            get_string('knowledge_dummy', 'groupformation') . ' 2</th>
                                            <td data-title="' .
            get_string('knowledge_scale', 'groupformation') . '" class="range"><span >0</span>
            <input type="range" min="0" max="100" value="0" /><span>100</span></td>
                                          </tr>
                                        <tr class="knowlRow" id="prkRow2">
                                            <th scope="row">' .
            get_string('knowledge_dummy', 'groupformation') . ' 3</th>
                                            <td data-title="' .
            get_string('knowledge_scale', 'groupformation') . '" class="range"><span >0</span>
            <input type="range" min="0" max="100" value="0" /><span>100</span></td>
                                          </tr>
                                        </tbody>
                                      </table>
                                </div>

                        </div> <!-- /col_50 -->
                    </div>  <!-- /multi_field_wrapper-->
                    </div> <!-- Anchor-->

                </div> <!-- /.grid -->
            </div> <!-- /.js_knowledgeWrapper -->
                ');

        // Close wrapper for preknowledge.
        $mform->addElement('html', '</div>');

        // Wrapper for topics.
        $mform->addElement('html', '<div class="gf_settings_pad">');

        // Add checkbox topics.
        $mform->addElement('html', '
                        <div class="gf_pad_header">
                            <label class="gf_label" for="id_js_topics">
                              <input type="checkbox" id="id_js_topics" name="chbTopics" value="wantTopics">
                              ' . get_string('topics_description', 'groupformation') . '</label>
                              <span id="topicsStateLabel" class="optional"></span>
                        </div>');

        // Add dynamic input fields topics with preview.
        $mform->addElement('html', '
                            <div class="gf_pad_content" id="js_topicsWrapper">

                                <p>' . get_string('topics_description_extended', 'groupformation') . '</p>

                                    <div class="grid">
                                    <div id="tpc">
                                    <div class="multi_field_wrapper persist-area">
                                        <div class="col_m_50">


        <!--                      Die Input Felder-->
        <h5>' . get_string('input', 'groupformation') . '</h5>
            <div class="multi_fields">
                <div class="multi_field" id="inputtpc0">
                    <input class="respwidth js_topicInput" type="text">
                    <button type="button" class="remove_field gf_button gf_button_circle gf_button_small"></button>
                </div>
                <div class="multi_field" id="inputtpc1">
                    <input class="respwidth js_topicInput" type="text">
                    <button type="button" class="remove_field gf_button gf_button_circle gf_button_small"></button>
                </div>
                <div class="multi_field" id="inputtpc2">
                    <input class="respwidth js_topicInput lastInput" type="text" placeholder="' .
            get_string('add_line', 'groupformation') . '">
            <button type="button" class="remove_field gf_button gf_button_circle gf_button_small" disabled="disabled">
                    </button>
                </div>
            </div>
        </div> <!-- /col_50 -->

        <!--                      Die Vorschau      -->
                                            <div class="col_m_50">

                                                <h5>' . get_string('preview', 'groupformation') . '</h5>

                                                <div class="col_m_100">' .

                '<p id="topicshead">' . get_string('topics_question', 'groupformation') . '</p>
                    <span id="topicsDummy" style="display:none;">' .
                get_string('topics_dummy', 'groupformation') . '</span>
                    <ul class="sortable_topics" id="previewTopics">
                      <li class="topicLi" id="tpcRow0" class=""><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>' .
                get_string('topics_dummy', 'groupformation') . '1</li>
                      <li class="topicLi" id="tpcRow1" class=""><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>' .
                get_string('topics_dummy', 'groupformation') . '2</li>
                      <li class="topicLi" id="tpcRow2" class=""><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>' .
                get_string('topics_dummy', 'groupformation') . '3</li>
                    </ul>
                                               </div>
                                            </div> <!-- /col_50 -->
                                        </div>  <!-- /multi_field_wrapper-->
                                        </div> <!-- Anchor-->

                                    </div> <!-- /.grid -->
                                </div> <!-- /.topicWrapper -->
        					');

        // Close wrapper for topics.
        $mform->addElement('html', '</div>');

        // Wrapper for Groupsize Options.
        $mform->addElement('html', '<div class="gf_settings_pad">');

        // Add Groupsize Options.
        $mform->addElement('html', '

                <div class="gf_pad_header">' . get_string('groupoption_description', 'groupformation') .
            '<span class="required"></span><span class="toolt" tooltip="' .
            get_string('groupoption_help', 'groupformation') . '"></span>

                </div>

                <div class="settings_info" id="groupSettingsInfo">
                        <p>' . get_string('groupSettingsInfo', 'groupformation') . '</p>
                </div>

                <div class="js_errors" id="maxmembers_error">
                             <p></p>
                </div>
                <div class="js_errors" id="maxgroups_error">
                        <p></p>
                </div>

                <div class="gf_pad_content">
                <p><span id="studentsInCourse"><b>');

        $context = $PAGE->context;
        $count = count(get_enrolled_users($context, 'mod/groupformation:onlystudent'));

        $mform->addElement('html', $count . '</b></span> ' . get_string('students_enrolled_info', 'groupformation') . '</p>
            <div class="grid">
            <div class="col_m_50"><label>
            <input type="radio" name="group_opt" id="group_opt_size" value="group_size" checked="checked" />
            ' . get_string('maxmembers', 'groupformation') . '</label>
            <input type="number" class="group_opt" id="group_size" min="0" max="100" value="0" /></div>
            <div class="col_m_50"><label><input type="radio" name="group_opt" id="group_opt_numb" value="numb_of_groups"/>
            ' . get_string('maxgroups', 'groupformation') . '</label>
            <input type="number" class="group_opt" id="numb_of_groups"  min="0" max="100" value="0" disabled="disabled" /></div>
            </div>
            </div> <!-- /grid -->
                ');

        // Close wrapper for Groupsize Options.
        $mform->addElement('html', '</div>');

        // Wrapper for Groupname.
        $mform->addElement('html', '<div class="gf_settings_pad">');

        $mform->addElement('html', '
                <div class="gf_pad_header">' .

            get_string('groupname', 'groupformation') .
            '<span class="optional"></span><span class="toolt" tooltip="' .
            get_string('groupname_help', 'groupformation') . '"></span>
                </div>
                <div class="gf_pad_content">
                    <input type="text" class="respwidth" id="js_groupname" />
                </div>
                ');

        // Close wrapper for Groupname.
        $mform->addElement('html', '</div>');

        // Wrapper for evaluation options.
        $mform->addElement('html', '<div class="gf_settings_pad">');

        // Add evaluation options.
        $mform->addElement('html', '
            <div class="gf_pad_header">' . get_string('evaluationmethod_description', 'groupformation') . '
            <span class="required"></span>
            </div>
            <div class="js_errors" id="evaluationmethod_error">
                        <p></p>
                </div>
                <div class="js_errors" id="maxpoints_error">
                        <p></p>
                </div>
                <div class="gf_pad_content">
                    <select id="js_evaluationmethod">
                        <option value="chooseM">' . get_string('choose_evaluationmethod', 'groupformation') . '</option>
                        <option value="grades">' . get_string('grades', 'groupformation') . '</option>
                        <option value="points">' . get_string('points', 'groupformation') . '</option>
                        <option value="justpass">' . get_string('justpass', 'groupformation') . '</option>
                        <option value="novaluation">' . get_string('noevaluation', 'groupformation') . '</option>
                    </select>
                    <span id="max_points_wrapper"><input type="number" id="max_points"  min="0" max="100" value="100" />
                    <span class="toolt" tooltip="' .
        get_string('evaluation_point_info', 'groupformation') . '"></span></span>
                </div>
                ');

        // Close wrapper for evaluation options.
        $mform->addElement('html', '</div>');

        // Add checkbox only-active-students.
        $mform->addElement('html', '
            <div class="gf_pad_header">
                <label class="gf_label" for="id_js_onlyactivestudents">
                  <input type="checkbox" id="id_js_onlyactivestudents" name="chbOnlyactivestudents" value="onlyactivestudents">
                  ' . get_string('onlyactivestudents_description', 'groupformation') .
            '</label><span id="onlyactivestudentsStateLabel" class="optional"></span><span class="toolt" tooltip="' .
            get_string('groupoption_onlyactivestudents', 'groupformation') . '"></span>
                            </div>');

        // Add checkbox email-notification.
        $mform->addElement('html', '
                    <div class="gf_pad_header">
                <label class="gf_label" for="id_js_emailnotifications">
                  <input type="checkbox" id="id_js_emailnotifications" name="chbEmailnotifications" value="wantEmailnotifications">
                  ' . get_string('emailnotifications_description', 'groupformation') . '</label>
                  <span id="emailnotificationsStateLabel" class="optional"></span>
                    </div>');

        // Close wrapper of the szenario.
        $mform->addElement('html', '</div>');

        // Close div tag for js related content.
        $mform->addElement('html', '</div id="js-content">');
    }

    /**
     * generates moodle form elements for non-JS version
     *
     * @param moodleform_mod $mform
     */
    public function generate_html_for_non_js(&$mform) {
        $changemsg = '<div class="fitem" id="nochangespossible"';
        if (!$this->store->is_editable()) {
            $changemsg .= ' ><span value="1"';
        } else {
            $changemsg .= ' style="display:none;"><span value="0"';
        }
        $changemsg .= ' style="color:red;">' . get_string('nochangespossible', 'groupformation') . '</span></div>';
        $mform->addElement('html', $changemsg);

        // Open div tag for non js related content.
        $mform->addElement('html', '<div id="non-js-content">');

        // Add field Szenario choice.
        $mform->addElement('select', 'szenario', get_string('scenario', 'groupformation'), array(
            get_string('choose_scenario', 'groupformation'), get_string('scenario_projectteams', 'groupformation'),
            get_string('scenario_homeworkgroups', 'groupformation'),
            get_string('scenario_presentationgroups', 'groupformation')), null);

        $mform->addRule('szenario', get_string('scenario_error', 'groupformation'), 'required', null, 'client');

        // Add fields for Knowledge questions.
        $mform->addElement('checkbox', 'knowledge', get_string('knowledge', 'groupformation'));
        $mform->addElement('textarea', 'knowledgelines', get_string('knowledge', 'groupformation'),
            'wrap="virtual" rows="10" cols="50"');

        $mform->disabledIf('knowledgelines', 'knowledge', 'notchecked');

        // Add fields for topic choices.
        $mform->addElement('checkbox', 'topics', get_string('topics', 'groupformation'));
        $mform->addElement('textarea', 'topiclines', get_string('topics', 'groupformation'),
            'wrap="virtual" rows="10" cols="50"');

        $mform->disabledIf('topiclines', 'topics', 'notchecked');

        // Add fields for max members or max groups.
        $radioarray = array();
        $radioarray [] = &$mform->createElement('radio', 'groupoption', '', get_string('maxmembers', 'groupformation'), 0, null);
        $radioarray [] = &$mform->createElement('radio', 'groupoption', '', get_string('maxgroups', 'groupformation'), 1, null);
        $mform->addGroup($radioarray, 'radioar', get_string('groupoptions', 'groupformation'), array(
            ' '), false);
        $mform->addRule('radioar', get_string('maxmembers_error', 'groupformation'), 'required', null, 'client');

        $mform->addElement('text', 'maxmembers', get_string('maxmembers', 'groupformation'), null);
        $mform->addElement('text', 'maxgroups', get_string('maxgroups', 'groupformation'), null);

        $mform->setType('maxmembers', PARAM_NUMBER);
        $mform->setType('maxgroups', PARAM_NUMBER);

        $mform->disabledIf('maxmembers', 'groupoption', 'eq', '1');
        $mform->disabledIf('maxgroups', 'groupoption', 'eq', '0');
        $mform->disabledIf('maxmembers', 'groupoption', 'eq', '1');

        // Add group name field.
        $mform->addElement('text', 'groupname', get_string('groupname', 'groupformation'), array(
            'size' => '64'));
        if (!empty ($CFG->formatstringstriptags)) {
            $mform->setType('groupname', PARAM_TEXT);
        } else {
            $mform->setType('groupname', PARAM_CLEAN);
        }
        $mform->addHelpButton('groupname', 'groupname', 'groupformation');

        // Add field for evaluation method.
        $mform->addElement('select', 'evaluationmethod', get_string('evaluationmethod_description', 'groupformation'),
            array(
                get_string('choose_evaluationmethod', 'groupformation'),
                get_string('grades', 'groupformation'), get_string('points', 'groupformation'),
                get_string('justpass', 'groupformation'), get_string('noevaluation', 'groupformation')),
            null);

        $mform->addRule('evaluationmethod', get_string('evaluationmethod_error', 'groupformation'), 'required', null,
            'client');

        $mform->addElement('text', 'maxpoints', get_string('maxpoints', 'groupformation'));

        $mform->disabledIf('maxpoints', 'evaluationmethod', 'neq', '2');
        $mform->setType('maxpoints', PARAM_NUMBER);

        $mform->addElement('checkbox', 'onlyactivestudents', get_string('onlyactivestudents', 'groupformation'));

        $mform->addElement('checkbox', 'emailnotifications', get_string('emailnotifications', 'groupformation'));
        $mform->setDefault('emailnotifications', true);
        // Close div tag for non-js related content.
        $mform->addElement('html', '</div id="non-js-content">');
    }
}

