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
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/groupformation/lib.php'); // Not in the template.
require_once($CFG->dirroot . '/mod/groupformation/locallib.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/xml_loader.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/util.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/define_file.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/template_builder.php');

/**
 * Class mod_groupformation_mod_form
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_mod_form extends moodleform_mod {

    /** @var mod_groupformation_storage_manager */
    private $store;

    /**
     * (non-PHPdoc)
     *
     * @see moodleform::definition()
     */
    public function definition() {
        global $PAGE, $USER, $CFG;

        $this->store = new mod_groupformation_storage_manager ($this->_instance);

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
     * @param array $data
     * @param array $files
     * @return array
     * @throws coding_exception
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
     * Generates HTML code for JS version
     *
     * @param unknown $mform
     * @throws coding_exception
     */
    private function generate_html_for_js(&$mform) {
        global $PAGE;

        $mathprepcourse = mod_groupformation_data::is_math_prep_course_mode();
        $teacherinfo = mod_groupformation_util::get_info_text_for_teacher(false);

        $templatebuilder = new mod_groupformation_template_builder();
        $templatebuilder->set_template('editform');

        $assign = array();
        $assign['teacherinfo'] = $teacherinfo;

        $subjects = ['Computer Science', 'Math', 'English', 'Physics'];
        $assign['subjects'] = $subjects;

        $context = $PAGE->context;
        $enrolledstudents = array_keys(get_enrolled_users($context, 'mod/groupformation:onlystudent'));
        $enrolledprevusers = array_keys(get_enrolled_users($context, 'mod/groupformation:editsettings'));
        $diff = array_diff($enrolledstudents, $enrolledprevusers);

        $count = count($diff);

        $assign['count'] = $count;
        $assign['mathprepcourse'] = $mathprepcourse;

        $templatebuilder->assign_multiple($assign);

        $output = $templatebuilder->load_template();

        $mform->addElement('html', $output);
    }

    /**
     * Generates moodle form elements for non-JS version
     *
     * @param moodleform_mod $mform
     * @throws coding_exception
     * @throws dml_exception
     */
    public function generate_html_for_non_js(&$mform) {
        global $CFG;
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

        // Add one of bin question.
        $mform->addElement('checkbox', 'oneofbin', get_string('oneOfBinQuestion', 'groupformation'));
        $mform->addElement('textarea', 'oneofbinquestion', get_string('oneOfBinQuestion', 'groupformation'),
            'wrap="virtual" rows="1" cols="50"');
        $mform->addElement('textarea', 'oneofbinanswers', get_string('oneOfBinAnswers', 'groupformation'),
            'wrap="virtual" rows="10" cols="50"');
        $mform->addElement('text', 'oneofbinimportance', get_string('oneOfBinImportance', 'groupformation'), null);
        $mform->setType('oneofbinimportance', PARAM_INT);
        $mform->addElement('select', 'oneofbinrelation', get_string('oneOfBinRelation', 'groupformation'), array(
            get_string('homogenous', 'groupformation'),
            get_string('heterogenous', 'groupformation')), null);

        $mform->disabledIf('oneofbinquestion', 'oneofbin', 'notchecked');
        $mform->disabledIf('oneofbinanswers', 'oneofbin', 'notchecked');
        $mform->disabledIf('oneofbinimportance', 'oneofbin', 'notchecked');
        $mform->disabledIf('oneofbinrelation', 'oneofbin', 'notchecked');



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

        $array = array(
            get_string('choose_evaluationmethod', 'groupformation'),
            get_string('grades', 'groupformation'),
            get_string('points', 'groupformation'),
            get_string('justpass', 'groupformation'),
            get_string('noevaluation', 'groupformation')
        );

        // Add field for evaluation method.
        $mform->addElement('select', 'evaluationmethod',
            get_string('evaluationmethod_description', 'groupformation'), $array, null);
        $mform->addRule('evaluationmethod', get_string('evaluationmethod_error', 'groupformation'), 'required', null,
            'client');
        $mform->addElement('text', 'maxpoints', get_string('maxpoints', 'groupformation'));
        $mform->disabledIf('maxpoints', 'evaluationmethod', 'neq', '2');
        $mform->setType('maxpoints', PARAM_NUMBER);
        $mform->addElement('checkbox', 'onlyactivestudents', get_string('onlyactivestudents', 'groupformation'));
        $mform->addElement('checkbox', 'allanswersrequired', get_string('allanswersrequired', 'groupformation'));
        $mform->addElement('checkbox', 'emailnotifications', get_string('emailnotifications', 'groupformation'));
        $mform->setDefault('emailnotifications', false); // TODO delete if feature is fixed.

        // Close div tag for non-js related content.
        $mform->addElement('html', '</div id="non-js-content">');
    }
}

