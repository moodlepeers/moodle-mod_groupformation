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
 * Internal library of functions for module groupformation
 *
 * All the newmodule specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package mod_groupformation
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die ();

/**
 * Adds jQuery
 *
 * @param unknown $PAGE
 * @param string $filename
 */
function groupformation_add_jquery($PAGE, $filename = null) {
    global $CFG;
    $PAGE->requires->jquery();
    $PAGE->requires->jquery_plugin('ui');
    $PAGE->requires->jquery_plugin('ui-css');

    if (!is_null($filename)) {
        $PAGE->requires->js(new moodle_url($CFG->wwwroot.'/mod/groupformation/js/'.$filename));
    }
}

/**
 * Adds jQuery
 *
 * @param unknown $PAGE
 * @param string $filename in amd folder of mod
 */
function groupformation_add_js_amd($PAGE, $filename) {
    global $CFG;
    $PAGE->requires->js(new moodle_url($CFG->wwwroot.'/mod/groupformation/amd/src/'.$filename));
}

/**
 * Calls a JavaScript AMD module
 * @link https://docs.moodle.org/dev/Javascript_Modules
 *
 * @param unknown $PAGE
 * @param string moudlname AMD conform modulname. prefix it with groupformation if it is internal (e.g. groupformation/mymodule)
 * @param method  method to call as initialize in return object of AMD
 * @param params optional params to send to this initialize js method
 */
function groupformation_call_js_amd($PAGE, $modulname, $method, $params=null) {
    $PAGE->requires->js_call_amd($modulname, $method, $params);
}

/**
 * Determines instances of course module, course and groupformation by id
 *
 * @param int $id
 * @return array
 */
function groupformation_determine_instance($id) {
    list ($course, $cm) = get_course_and_cm_from_cmid($id, 'groupformation');
    $groupformation = groupformation_get_by_id($cm->instance);
    return [$course, $cm, $groupformation];
}

/**
 * Returns context for groupformation id
 *
 * @param int $groupformationid
 * @return context_course
 */
function groupformation_get_context($groupformationid) {
    $store = new mod_groupformation_storage_manager ($groupformationid);

    $courseid = $store->get_course_id();

    $context = context_course::instance($courseid);

    return $context;
}

/**
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param int $userid
 */
function groupformation_set_activity_completion($course, $cm, $userid) {
    $completion = new completion_info ($course);
    $completion->set_module_viewed($cm, $userid);
}

/**
 * send confirmation for finishing group formation
 *
 * @param stdClass $recipient
 * @param string $subject
 * @param string $message
 *
 */
function groupformation_send_message($recipient, $subject, $messagetext, $contexturl = null, $contexturlname = null) {
    global $DB;

    // Get admin user for setting as "userfrom".
    $admin = array_pop($DB->get_records('user', array(
        'username' => 'admin')));

    // Prepare the message.
    $message = new \core\message\message ();
    $message->component = 'moodle';
    $message->name = 'instantmessage';
    $message->userfrom = $admin;
    $message->userto = $recipient;
    $message->subject = $subject;
    $message->fullmessage = $messagetext;
    $message->fullmessageformat = FORMAT_MARKDOWN;
    $message->fullmessagehtml = '<p>' . $messagetext . '</p>';
    $message->smallmessage = $messagetext;
    $message->notification = '0';
    $message->contexturl = $contexturl;
    $message->contexturlname = $contexturlname;
    $message->replyto = "noreply@moodle.com";
    $content = array(
        '*' => array(
            'header' => ' test ', 'footer' => ' test ')); // Extra content for specific processor.
    $message->set_additional_content('email', $content);

    // Send message.
    message_send($message);
}

/**
 * Checks for cronjob whether it is running or not
 * @throws coding_exception
 */
function groupformation_check_for_cron_job() {
    global $DB;

    $record = $DB->get_record('task_scheduled', array(
        'component' => 'mod_groupformation', 'classname' => '\mod_groupformation\task\build_groups_task'));
    $now = time();
    $lastruntime = $record->lastruntime;

    if (($now - intval($lastruntime)) > 60 * 60 * 24) {
        echo '<div class="alert">' . get_string('cron_job_not_running', 'groupformation') . '</div>';
    }
}

/**
 * Reads questionnaire file
 *
 * @param mod_groupformation_storage_manager $store
 * @param string $filename
 */
function groupformation_import_questionnaire_configuration($filename = 'questionnaire.xml') {
    global $CFG, $DB;

    $xmlfile = $CFG->dirroot . '/mod/groupformation/xml_question/' . $filename;

    if (file_exists($xmlfile)) {
        $xml = simplexml_load_file($xmlfile);

        $currentversion = groupformation_get_current_questionnaire_version();
        $newversion = intval(trim($xml['version']));

        $newscenarios = array();

        foreach ($xml->scenarios->scenario as $scenarioname) {
            $categories = $scenarioname->categories;
            $scenariocats = array();
            foreach ($categories->category as $cat) {
                $scenariocats[] = trim($cat);
            }

            $newscenarios[trim($scenarioname->name)] = $scenariocats;
        }

        $newcategories = array();

        foreach ($xml->categories->category as $cat) {
            $newcategories[] = trim($cat);
        }

        $newlanguages = array();

        foreach ($xml->languages->language as $lang) {
            $newlanguages[] = trim($lang);
        }

        if ($newversion > $currentversion) {

            $xmlloader = new mod_groupformation_xml_loader();

            $number = 0;

            foreach ($newcategories as $category) {

                $prevversion = groupformation_get_catalog_version($category);

                foreach ($newlanguages as $language) {

                    $data = $xmlloader->save($category, $language);

                    $version = $data[0];
                    $numberofquestions = $data[1];
                    $questions = $data[2];

                    if ($version > $prevversion || !$prevversion) {
                        groupformation_delete_all_catalog_questions($category, $language);

                        $DB->insert_records('groupformation_question', $questions);
                        groupformation_add_catalog_version($category, $numberofquestions, $version, false);
                    }
                }
                $number += $numberofquestions;
            }

            $DB->delete_records('groupformation_scenario');
            $DB->delete_records('groupformation_scenario_cats');

            foreach($newscenarios as $name => $categories) {
                $record = new stdClass();
                $record->name = $name;
                $record->version = $newversion;
                $scenarioid = $DB->insert_record('groupformation_scenario', $record);
                foreach($categories as $category) {
                    $record = $DB->get_record('groupformation_q_version', array('category' => $category));
                    $newrecord = new stdClass();
                    $newrecord->scenario = $scenarioid;
                    $newrecord->category = $record->id;
                    $DB->insert_record('groupformation_scenario_cats', $newrecord);
                }
            }

            groupformation_add_catalog_version('questionnaire', $number, $newversion, false);
        }

    }

}

/**
 * Add new question from XML to DB
 *
 * @param string $category
 * @param int $numbers
 * @param unknown $version
 * @param boolean $init
 */
function groupformation_add_catalog_version($category, $numbers, $version, $init) {
    global $DB;

    $data = new stdClass ();
    $data->category = $category;
    $data->version = $version;
    $data->numberofquestion = $numbers;

    if ($init || $DB->count_records('groupformation_q_version', array(
            'category' => $category
        )) == 0
    ) {
        $DB->insert_record('groupformation_q_version', $data);
    } else {
        $data->id = $DB->get_field('groupformation_q_version', 'id', array(
            'category' => $category
        ));
        $DB->update_record('groupformation_q_version', $data);
    }
}

/**
 * Deletes all questions in a specific category
 *
 * @param string $category
 */
function groupformation_delete_all_catalog_questions($category, $language) {
    global $DB;

    $DB->delete_records('groupformation_question', array('category' => $category, 'language' => $language));
}

/**
 * Returns current questionnaire version
 *
 * @return mixed|null
 */
function groupformation_get_current_questionnaire_version() {
    global $DB;

    $field = $DB->get_field('groupformation_q_version', 'version', array('category' => 'questionnaire'));

    if ($field !== false) {
        return $field;
    } else {
        return 0;
    }
}

function groupformation_get_catalog_version($category) {
    global $DB;

    $field = $DB->get_field('groupformation_q_version', 'version', array('category' => $category));

    if ($field !== false) {
        return $field;
    } else {
        return 0;
    }
}

/**
 * Converts knowledge or topic array into XML-based syntax
 *
 * @param unknown $options
 * @return string
 */
function groupformation_convert_options($options) {
    $ops = array();
    foreach ($options as $key => $option) {
        if (is_number($key)){
            $key = 'OPTION';
        }
        $s = '<' . $key . '><![CDATA[';
        $s .= htmlentities($option, ENT_QUOTES | ENT_XHTML);
        $s .= ']]></' . $key . '>';
        $ops[] = $s;
    }
    $op = implode("", $ops);

    return $op;
}

/**
 * Returns z values as a lookup table.
 *
 * @return array
 */
function groupformation_z_lookup_table() {
    $zlookuptable = array();
    $zlookuptable['-3.0'] = 0.0013;
    $zlookuptable['-3'] = 0.0013;
    $zlookuptable['-2.99'] = 0.0014;
    $zlookuptable['-2.98'] = 0.0014;
    $zlookuptable['-2.97'] = 0.0015;
    $zlookuptable['-2.96'] = 0.0015;
    $zlookuptable['-2.95'] = 0.0016;
    $zlookuptable['-2.94'] = 0.0016;
    $zlookuptable['-2.93'] = 0.0017;
    $zlookuptable['-2.92'] = 0.0018;
    $zlookuptable['-2.91'] = 0.0018;
    $zlookuptable['-2.9'] = 0.0019;
    $zlookuptable['-2.89'] = 0.0019;
    $zlookuptable['-2.88'] = 0.0020;
    $zlookuptable['-2.87'] = 0.0021;
    $zlookuptable['-2.86'] = 0.0021;
    $zlookuptable['-2.85'] = 0.0022;
    $zlookuptable['-2.84'] = 0.0023;
    $zlookuptable['-2.83'] = 0.0023;
    $zlookuptable['-2.82'] = 0.0024;
    $zlookuptable['-2.81'] = 0.0025;
    $zlookuptable['-2.8'] = 0.0026;
    $zlookuptable['-2.79'] = 0.0026;
    $zlookuptable['-2.78'] = 0.0027;
    $zlookuptable['-2.77'] = 0.0028;
    $zlookuptable['-2.76'] = 0.0029;
    $zlookuptable['-2.75'] = 0.0030;
    $zlookuptable['-2.74'] = 0.0031;
    $zlookuptable['-2.73'] = 0.0032;
    $zlookuptable['-2.72'] = 0.0033;
    $zlookuptable['-2.71'] = 0.0034;
    $zlookuptable['-2.7'] = 0.0035;
    $zlookuptable['-2.69'] = 0.0036;
    $zlookuptable['-2.68'] = 0.0037;
    $zlookuptable['-2.67'] = 0.0038;
    $zlookuptable['-2.66'] = 0.0039;
    $zlookuptable['-2.65'] = 0.0040;
    $zlookuptable['-2.64'] = 0.0041;
    $zlookuptable['-2.63'] = 0.0043;
    $zlookuptable['-2.62'] = 0.0044;
    $zlookuptable['-2.61'] = 0.0045;
    $zlookuptable['-2.6'] = 0.0047;
    $zlookuptable['-2.59'] = 0.0048;
    $zlookuptable['-2.58'] = 0.0049;
    $zlookuptable['-2.57'] = 0.0051;
    $zlookuptable['-2.56'] = 0.0052;
    $zlookuptable['-2.55'] = 0.0054;
    $zlookuptable['-2.54'] = 0.0055;
    $zlookuptable['-2.53'] = 0.0057;
    $zlookuptable['-2.52'] = 0.0059;
    $zlookuptable['-2.51'] = 0.0060;
    $zlookuptable['-2.5'] = 0.0062;
    $zlookuptable['-2.49'] = 0.0064;
    $zlookuptable['-2.48'] = 0.0066;
    $zlookuptable['-2.47'] = 0.0068;
    $zlookuptable['-2.46'] = 0.0069;
    $zlookuptable['-2.45'] = 0.0071;
    $zlookuptable['-2.44'] = 0.0073;
    $zlookuptable['-2.43'] = 0.0075;
    $zlookuptable['-2.42'] = 0.0078;
    $zlookuptable['-2.41'] = 0.0080;
    $zlookuptable['-2.4'] = 0.0082;
    $zlookuptable['-2.39'] = 0.0084;
    $zlookuptable['-2.38'] = 0.0087;
    $zlookuptable['-2.37'] = 0.0089;
    $zlookuptable['-2.36'] = 0.0091;
    $zlookuptable['-2.35'] = 0.0094;
    $zlookuptable['-2.34'] = 0.0096;
    $zlookuptable['-2.33'] = 0.0099;
    $zlookuptable['-2.32'] = 0.0102;
    $zlookuptable['-2.31'] = 0.0104;
    $zlookuptable['-2.3'] = 0.0107;
    $zlookuptable['-2.29'] = 0.0110;
    $zlookuptable['-2.28'] = 0.0113;
    $zlookuptable['-2.27'] = 0.0116;
    $zlookuptable['-2.26'] = 0.0119;
    $zlookuptable['-2.25'] = 0.0122;
    $zlookuptable['-2.24'] = 0.0125;
    $zlookuptable['-2.23'] = 0.0129;
    $zlookuptable['-2.22'] = 0.0132;
    $zlookuptable['-2.21'] = 0.0136;
    $zlookuptable['-2.2'] = 0.0139;
    $zlookuptable['-2.19'] = 0.0143;
    $zlookuptable['-2.18'] = 0.0146;
    $zlookuptable['-2.17'] = 0.0150;
    $zlookuptable['-2.16'] = 0.0154;
    $zlookuptable['-2.15'] = 0.0158;
    $zlookuptable['-2.14'] = 0.0162;
    $zlookuptable['-2.13'] = 0.0166;
    $zlookuptable['-2.12'] = 0.0170;
    $zlookuptable['-2.11'] = 0.0174;
    $zlookuptable['-2.1'] = 0.0179;
    $zlookuptable['-2.09'] = 0.0183;
    $zlookuptable['-2.08'] = 0.0188;
    $zlookuptable['-2.07'] = 0.0192;
    $zlookuptable['-2.06'] = 0.0197;
    $zlookuptable['-2.05'] = 0.0202;
    $zlookuptable['-2.04'] = 0.0207;
    $zlookuptable['-2.03'] = 0.0212;
    $zlookuptable['-2.02'] = 0.0217;
    $zlookuptable['-2.01'] = 0.0222;
    $zlookuptable['-2.0'] = 0.0228;
    $zlookuptable['-2'] = 0.0228;
    $zlookuptable['-1.99'] = 0.0233;
    $zlookuptable['-1.98'] = 0.0239;
    $zlookuptable['-1.97'] = 0.0244;
    $zlookuptable['-1.96'] = 0.0250;
    $zlookuptable['-1.95'] = 0.0256;
    $zlookuptable['-1.94'] = 0.0262;
    $zlookuptable['-1.93'] = 0.0268;
    $zlookuptable['-1.92'] = 0.0275;
    $zlookuptable['-1.91'] = 0.0281;
    $zlookuptable['-1.9'] = 0.0287;
    $zlookuptable['-1.89'] = 0.0294;
    $zlookuptable['-1.88'] = 0.0301;
    $zlookuptable['-1.87'] = 0.0307;
    $zlookuptable['-1.86'] = 0.0314;
    $zlookuptable['-1.85'] = 0.0322;
    $zlookuptable['-1.84'] = 0.0329;
    $zlookuptable['-1.83'] = 0.0336;
    $zlookuptable['-1.82'] = 0.0344;
    $zlookuptable['-1.81'] = 0.0351;
    $zlookuptable['-1.8'] = 0.0359;
    $zlookuptable['-1.79'] = 0.0367;
    $zlookuptable['-1.78'] = 0.0375;
    $zlookuptable['-1.77'] = 0.0384;
    $zlookuptable['-1.76'] = 0.0392;
    $zlookuptable['-1.75'] = 0.0401;
    $zlookuptable['-1.74'] = 0.0409;
    $zlookuptable['-1.73'] = 0.0418;
    $zlookuptable['-1.72'] = 0.0427;
    $zlookuptable['-1.71'] = 0.0436;
    $zlookuptable['-1.7'] = 0.0446;
    $zlookuptable['-1.69'] = 0.0455;
    $zlookuptable['-1.68'] = 0.0465;
    $zlookuptable['-1.67'] = 0.0475;
    $zlookuptable['-1.66'] = 0.0485;
    $zlookuptable['-1.65'] = 0.0495;
    $zlookuptable['-1.64'] = 0.0505;
    $zlookuptable['-1.63'] = 0.0516;
    $zlookuptable['-1.62'] = 0.0526;
    $zlookuptable['-1.61'] = 0.0537;
    $zlookuptable['-1.6'] = 0.0548;
    $zlookuptable['-1.59'] = 0.0559;
    $zlookuptable['-1.58'] = 0.0571;
    $zlookuptable['-1.57'] = 0.0582;
    $zlookuptable['-1.56'] = 0.0594;
    $zlookuptable['-1.55'] = 0.0606;
    $zlookuptable['-1.54'] = 0.0618;
    $zlookuptable['-1.53'] = 0.0630;
    $zlookuptable['-1.52'] = 0.0643;
    $zlookuptable['-1.51'] = 0.0655;
    $zlookuptable['-1.5'] = 0.0668;
    $zlookuptable['-1.49'] = 0.0681;
    $zlookuptable['-1.48'] = 0.0694;
    $zlookuptable['-1.47'] = 0.0708;
    $zlookuptable['-1.46'] = 0.0721;
    $zlookuptable['-1.45'] = 0.0735;
    $zlookuptable['-1.44'] = 0.0749;
    $zlookuptable['-1.43'] = 0.0764;
    $zlookuptable['-1.42'] = 0.0778;
    $zlookuptable['-1.41'] = 0.0793;
    $zlookuptable['-1.4'] = 0.0808;
    $zlookuptable['-1.39'] = 0.0823;
    $zlookuptable['-1.38'] = 0.0838;
    $zlookuptable['-1.37'] = 0.0853;
    $zlookuptable['-1.36'] = 0.0869;
    $zlookuptable['-1.35'] = 0.0885;
    $zlookuptable['-1.34'] = 0.0901;
    $zlookuptable['-1.33'] = 0.0918;
    $zlookuptable['-1.32'] = 0.0934;
    $zlookuptable['-1.31'] = 0.0951;
    $zlookuptable['-1.3'] = 0.0968;
    $zlookuptable['-1.29'] = 0.0985;
    $zlookuptable['-1.28'] = 0.1003;
    $zlookuptable['-1.27'] = 0.1020;
    $zlookuptable['-1.26'] = 0.1038;
    $zlookuptable['-1.25'] = 0.1056;
    $zlookuptable['-1.24'] = 0.1075;
    $zlookuptable['-1.23'] = 0.1093;
    $zlookuptable['-1.22'] = 0.1112;
    $zlookuptable['-1.21'] = 0.1131;
    $zlookuptable['-1.2'] = 0.1151;
    $zlookuptable['-1.19'] = 0.1170;
    $zlookuptable['-1.18'] = 0.1190;
    $zlookuptable['-1.17'] = 0.1210;
    $zlookuptable['-1.16'] = 0.1230;
    $zlookuptable['-1.15'] = 0.1251;
    $zlookuptable['-1.14'] = 0.1271;
    $zlookuptable['-1.13'] = 0.1292;
    $zlookuptable['-1.12'] = 0.1314;
    $zlookuptable['-1.11'] = 0.1335;
    $zlookuptable['-1.1'] = 0.1357;
    $zlookuptable['-1.09'] = 0.1379;
    $zlookuptable['-1.08'] = 0.1401;
    $zlookuptable['-1.07'] = 0.1423;
    $zlookuptable['-1.06'] = 0.1446;
    $zlookuptable['-1.05'] = 0.1469;
    $zlookuptable['-1.04'] = 0.1492;
    $zlookuptable['-1.03'] = 0.1515;
    $zlookuptable['-1.02'] = 0.1539;
    $zlookuptable['-1.01'] = 0.1562;
    $zlookuptable['-1.0'] = 0.1587;
    $zlookuptable['-1'] = 0.1587;
    $zlookuptable['-0.99'] = 0.1611;
    $zlookuptable['-0.98'] = 0.1635;
    $zlookuptable['-0.97'] = 0.1660;
    $zlookuptable['-0.96'] = 0.1685;
    $zlookuptable['-0.95'] = 0.1711;
    $zlookuptable['-0.94'] = 0.1736;
    $zlookuptable['-0.93'] = 0.1762;
    $zlookuptable['-0.92'] = 0.1788;
    $zlookuptable['-0.91'] = 0.1814;
    $zlookuptable['-0.9'] = 0.1841;
    $zlookuptable['-0.89'] = 0.1867;
    $zlookuptable['-0.88'] = 0.1894;
    $zlookuptable['-0.87'] = 0.1922;
    $zlookuptable['-0.86'] = 0.1949;
    $zlookuptable['-0.85'] = 0.1977;
    $zlookuptable['-0.84'] = 0.2005;
    $zlookuptable['-0.83'] = 0.2033;
    $zlookuptable['-0.82'] = 0.2061;
    $zlookuptable['-0.81'] = 0.2090;
    $zlookuptable['-0.8'] = 0.2119;
    $zlookuptable['-0.79'] = 0.2148;
    $zlookuptable['-0.78'] = 0.2177;
    $zlookuptable['-0.77'] = 0.2206;
    $zlookuptable['-0.76'] = 0.2236;
    $zlookuptable['-0.75'] = 0.2266;
    $zlookuptable['-0.74'] = 0.2296;
    $zlookuptable['-0.73'] = 0.2327;
    $zlookuptable['-0.72'] = 0.2358;
    $zlookuptable['-0.71'] = 0.2389;
    $zlookuptable['-0.7'] = 0.2420;
    $zlookuptable['-0.69'] = 0.2451;
    $zlookuptable['-0.68'] = 0.2483;
    $zlookuptable['-0.67'] = 0.2514;
    $zlookuptable['-0.66'] = 0.2546;
    $zlookuptable['-0.65'] = 0.2578;
    $zlookuptable['-0.64'] = 0.2611;
    $zlookuptable['-0.63'] = 0.2643;
    $zlookuptable['-0.62'] = 0.2676;
    $zlookuptable['-0.61'] = 0.2709;
    $zlookuptable['-0.6'] = 0.2749;
    $zlookuptable['-0.59'] = 0.2776;
    $zlookuptable['-0.58'] = 0.2810;
    $zlookuptable['-0.57'] = 0.2843;
    $zlookuptable['-0.56'] = 0.2877;
    $zlookuptable['-0.55'] = 0.2912;
    $zlookuptable['-0.54'] = 0.2946;
    $zlookuptable['-0.53'] = 0.2981;
    $zlookuptable['-0.52'] = 0.3015;
    $zlookuptable['-0.51'] = 0.3050;
    $zlookuptable['-0.5'] = 0.3085;
    $zlookuptable['-0.49'] = 0.3121;
    $zlookuptable['-0.48'] = 0.3156;
    $zlookuptable['-0.47'] = 0.3192;
    $zlookuptable['-0.46'] = 0.3228;
    $zlookuptable['-0.45'] = 0.3264;
    $zlookuptable['-0.44'] = 0.33;
    $zlookuptable['-0.43'] = 0.3336;
    $zlookuptable['-0.42'] = 0.3372;
    $zlookuptable['-0.41'] = 0.3409;
    $zlookuptable['-0.4'] = 0.3446;
    $zlookuptable['-0.39'] = 0.3483;
    $zlookuptable['-0.38'] = 0.3520;
    $zlookuptable['-0.37'] = 0.3557;
    $zlookuptable['-0.36'] = 0.3594;
    $zlookuptable['-0.35'] = 0.3632;
    $zlookuptable['-0.34'] = 0.3669;
    $zlookuptable['-0.33'] = 0.3707;
    $zlookuptable['-0.32'] = 0.3745;
    $zlookuptable['-0.31'] = 0.3783;
    $zlookuptable['-0.3'] = 0.3821;
    $zlookuptable['-0.29'] = 0.3859;
    $zlookuptable['-0.28'] = 0.3897;
    $zlookuptable['-0.27'] = 0.3936;
    $zlookuptable['-0.26'] = 0.3974;
    $zlookuptable['-0.25'] = 0.4013;
    $zlookuptable['-0.24'] = 0.4052;
    $zlookuptable['-0.23'] = 0.4090;
    $zlookuptable['-0.22'] = 0.4129;
    $zlookuptable['-0.21'] = 0.4168;
    $zlookuptable['-0.2'] = 0.4207;
    $zlookuptable['-0.19'] = 0.4247;
    $zlookuptable['-0.18'] = 0.4286;
    $zlookuptable['-0.17'] = 0.4325;
    $zlookuptable['-0.16'] = 0.4364;
    $zlookuptable['-0.15'] = 0.4404;
    $zlookuptable['-0.14'] = 0.4443;
    $zlookuptable['-0.13'] = 0.4483;
    $zlookuptable['-0.12'] = 0.4522;
    $zlookuptable['-0.11'] = 0.4562;
    $zlookuptable['-0.1'] = 0.4602;
    $zlookuptable['-0.09'] = 0.4641;
    $zlookuptable['-0.08'] = 0.4681;
    $zlookuptable['-0.07'] = 0.4721;
    $zlookuptable['-0.06'] = 0.4761;
    $zlookuptable['-0.05'] = 0.4801;
    $zlookuptable['-0.04'] = 0.4840;
    $zlookuptable['-0.03'] = 0.4880;
    $zlookuptable['-0.02'] = 0.4920;
    $zlookuptable['-0.01'] = 0.4960;
    $zlookuptable['-0'] = 0.50;
    $zlookuptable['0'] = 0.50;
    $zlookuptable['0.0'] = 0.50;
    $zlookuptable['0.01'] = 0.5040;
    $zlookuptable['0.02'] = 0.5080;
    $zlookuptable['0.03'] = 0.5120;
    $zlookuptable['0.04'] = 0.5160;
    $zlookuptable['0.05'] = 0.5199;
    $zlookuptable['0.06'] = 0.5239;
    $zlookuptable['0.07'] = 0.5279;
    $zlookuptable['0.08'] = 0.5319;
    $zlookuptable['0.09'] = 0.5359;
    $zlookuptable['0.1'] = 0.5398;
    $zlookuptable['0.11'] = 0.5438;
    $zlookuptable['0.12'] = 0.5478;
    $zlookuptable['0.13'] = 0.5517;
    $zlookuptable['0.14'] = 0.5557;
    $zlookuptable['0.15'] = 0.5596;
    $zlookuptable['0.16'] = 0.5636;
    $zlookuptable['0.17'] = 0.5675;
    $zlookuptable['0.18'] = 0.5714;
    $zlookuptable['0.19'] = 0.5753;
    $zlookuptable['0.2'] = 0.5793;
    $zlookuptable['0.21'] = 0.5832;
    $zlookuptable['0.22'] = 0.5871;
    $zlookuptable['0.23'] = 0.5910;
    $zlookuptable['0.24'] = 0.5948;
    $zlookuptable['0.25'] = 0.5987;
    $zlookuptable['0.26'] = 0.6026;
    $zlookuptable['0.27'] = 0.6064;
    $zlookuptable['0.28'] = 0.6103;
    $zlookuptable['0.29'] = 0.6141;
    $zlookuptable['0.3'] = 0.6179;
    $zlookuptable['0.31'] = 0.6217;
    $zlookuptable['0.32'] = 0.6255;
    $zlookuptable['0.33'] = 0.6293;
    $zlookuptable['0.34'] = 0.6331;
    $zlookuptable['0.35'] = 0.6368;
    $zlookuptable['0.36'] = 0.6406;
    $zlookuptable['0.37'] = 0.6443;
    $zlookuptable['0.38'] = 0.6480;
    $zlookuptable['0.39'] = 0.6517;
    $zlookuptable['0.4'] = 0.6554;
    $zlookuptable['0.41'] = 0.6591;
    $zlookuptable['0.42'] = 0.6628;
    $zlookuptable['0.43'] = 0.6664;
    $zlookuptable['0.44'] = 0.67;
    $zlookuptable['0.45'] = 0.6736;
    $zlookuptable['0.46'] = 0.6772;
    $zlookuptable['0.47'] = 0.6808;
    $zlookuptable['0.48'] = 0.6844;
    $zlookuptable['0.49'] = 0.6879;
    $zlookuptable['0.5'] = 0.6915;
    $zlookuptable['0.51'] = 0.6950;
    $zlookuptable['0.52'] = 0.6985;
    $zlookuptable['0.53'] = 0.7019;
    $zlookuptable['0.54'] = 0.7054;
    $zlookuptable['0.55'] = 0.7088;
    $zlookuptable['0.56'] = 0.7123;
    $zlookuptable['0.57'] = 0.7157;
    $zlookuptable['0.58'] = 0.7190;
    $zlookuptable['0.59'] = 0.7224;
    $zlookuptable['0.6'] = 0.7257;
    $zlookuptable['0.61'] = 0.7291;
    $zlookuptable['0.62'] = 0.7324;
    $zlookuptable['0.63'] = 0.7357;
    $zlookuptable['0.64'] = 0.7389;
    $zlookuptable['0.65'] = 0.7422;
    $zlookuptable['0.66'] = 0.7454;
    $zlookuptable['0.67'] = 0.7486;
    $zlookuptable['0.68'] = 0.7517;
    $zlookuptable['0.69'] = 0.7549;
    $zlookuptable['0.7'] = 0.7580;
    $zlookuptable['0.71'] = 0.7611;
    $zlookuptable['0.72'] = 0.7642;
    $zlookuptable['0.73'] = 0.7673;
    $zlookuptable['0.74'] = 0.7704;
    $zlookuptable['0.75'] = 0.7734;
    $zlookuptable['0.76'] = 0.7764;
    $zlookuptable['0.77'] = 0.7794;
    $zlookuptable['0.78'] = 0.7823;
    $zlookuptable['0.79'] = 0.7852;
    $zlookuptable['0.8'] = 0.7881;
    $zlookuptable['0.81'] = 0.7910;
    $zlookuptable['0.82'] = 0.7939;
    $zlookuptable['0.83'] = 0.7967;
    $zlookuptable['0.84'] = 0.7995;
    $zlookuptable['0.85'] = 0.8023;
    $zlookuptable['0.86'] = 0.8051;
    $zlookuptable['0.87'] = 0.8078;
    $zlookuptable['0.88'] = 0.8106;
    $zlookuptable['0.89'] = 0.8133;
    $zlookuptable['0.9'] = 0.8159;
    $zlookuptable['0.91'] = 0.8186;
    $zlookuptable['0.92'] = 0.8212;
    $zlookuptable['0.93'] = 0.8238;
    $zlookuptable['0.94'] = 0.8264;
    $zlookuptable['0.95'] = 0.8289;
    $zlookuptable['0.96'] = 0.8315;
    $zlookuptable['0.97'] = 0.8340;
    $zlookuptable['0.98'] = 0.8365;
    $zlookuptable['0.99'] = 0.8389;
    $zlookuptable['1.0'] = 0.8413;
    $zlookuptable['1'] = 0.8413;
    $zlookuptable['1.01'] = 0.8438;
    $zlookuptable['1.02'] = 0.8461;
    $zlookuptable['1.03'] = 0.8485;
    $zlookuptable['1.04'] = 0.8508;
    $zlookuptable['1.05'] = 0.8531;
    $zlookuptable['1.06'] = 0.8554;
    $zlookuptable['1.07'] = 0.8577;
    $zlookuptable['1.08'] = 0.8599;
    $zlookuptable['1.09'] = 0.8621;
    $zlookuptable['1.1'] = 0.8643;
    $zlookuptable['1.11'] = 0.8665;
    $zlookuptable['1.12'] = 0.8686;
    $zlookuptable['1.13'] = 0.8708;
    $zlookuptable['1.14'] = 0.8729;
    $zlookuptable['1.15'] = 0.8749;
    $zlookuptable['1.16'] = 0.8770;
    $zlookuptable['1.17'] = 0.8790;
    $zlookuptable['1.18'] = 0.8810;
    $zlookuptable['1.19'] = 0.8830;
    $zlookuptable['1.2'] = 0.8849;
    $zlookuptable['1.21'] = 0.8869;
    $zlookuptable['1.22'] = 0.8888;
    $zlookuptable['1.23'] = 0.8907;
    $zlookuptable['1.24'] = 0.8925;
    $zlookuptable['1.25'] = 0.8944;
    $zlookuptable['1.26'] = 0.8962;
    $zlookuptable['1.27'] = 0.8980;
    $zlookuptable['1.28'] = 0.8997;
    $zlookuptable['1.29'] = 0.9015;
    $zlookuptable['1.3'] = 0.9032;
    $zlookuptable['1.31'] = 0.9049;
    $zlookuptable['1.32'] = 0.9066;
    $zlookuptable['1.33'] = 0.9082;
    $zlookuptable['1.34'] = 0.9099;
    $zlookuptable['1.35'] = 0.9115;
    $zlookuptable['1.36'] = 0.9131;
    $zlookuptable['1.37'] = 0.9147;
    $zlookuptable['1.38'] = 0.9162;
    $zlookuptable['1.39'] = 0.9177;
    $zlookuptable['1.4'] = 0.9192;
    $zlookuptable['1.41'] = 0.9207;
    $zlookuptable['1.42'] = 0.9222;
    $zlookuptable['1.43'] = 0.9236;
    $zlookuptable['1.44'] = 0.9251;
    $zlookuptable['1.45'] = 0.9265;
    $zlookuptable['1.46'] = 0.9279;
    $zlookuptable['1.47'] = 0.9292;
    $zlookuptable['1.48'] = 0.9306;
    $zlookuptable['1.49'] = 0.9319;
    $zlookuptable['1.5'] = 0.9332;
    $zlookuptable['1.51'] = 0.9345;
    $zlookuptable['1.52'] = 0.9357;
    $zlookuptable['1.53'] = 0.9370;
    $zlookuptable['1.54'] = 0.9382;
    $zlookuptable['1.55'] = 0.9394;
    $zlookuptable['1.56'] = 0.9406;
    $zlookuptable['1.57'] = 0.9418;
    $zlookuptable['1.58'] = 0.9429;
    $zlookuptable['1.59'] = 0.9441;
    $zlookuptable['1.6'] = 0.9452;
    $zlookuptable['1.61'] = 0.9463;
    $zlookuptable['1.62'] = 0.9474;
    $zlookuptable['1.63'] = 0.9484;
    $zlookuptable['1.64'] = 0.9495;
    $zlookuptable['1.65'] = 0.9505;
    $zlookuptable['1.66'] = 0.9515;
    $zlookuptable['1.67'] = 0.9525;
    $zlookuptable['1.68'] = 0.9535;
    $zlookuptable['1.69'] = 0.9545;
    $zlookuptable['1.7'] = 0.9554;
    $zlookuptable['1.71'] = 0.9564;
    $zlookuptable['1.72'] = 0.9573;
    $zlookuptable['1.73'] = 0.9582;
    $zlookuptable['1.74'] = 0.9591;
    $zlookuptable['1.75'] = 0.9599;
    $zlookuptable['1.76'] = 0.9608;
    $zlookuptable['1.77'] = 0.9616;
    $zlookuptable['1.78'] = 0.9625;
    $zlookuptable['1.79'] = 0.9633;
    $zlookuptable['1.8'] = 0.9641;
    $zlookuptable['1.81'] = 0.9649;
    $zlookuptable['1.82'] = 0.9656;
    $zlookuptable['1.83'] = 0.9664;
    $zlookuptable['1.84'] = 0.9671;
    $zlookuptable['1.85'] = 0.9678;
    $zlookuptable['1.86'] = 0.9686;
    $zlookuptable['1.87'] = 0.9693;
    $zlookuptable['1.88'] = 0.9699;
    $zlookuptable['1.89'] = 0.9706;
    $zlookuptable['1.9'] = 0.9713;
    $zlookuptable['1.91'] = 0.9719;
    $zlookuptable['1.92'] = 0.9726;
    $zlookuptable['1.93'] = 0.9732;
    $zlookuptable['1.94'] = 0.9738;
    $zlookuptable['1.95'] = 0.9744;
    $zlookuptable['1.96'] = 0.9750;
    $zlookuptable['1.97'] = 0.9756;
    $zlookuptable['1.98'] = 0.9761;
    $zlookuptable['1.99'] = 0.9767;
    $zlookuptable['2.0'] = 0.9772;
    $zlookuptable['2'] = 0.9772;
    $zlookuptable['2.01'] = 0.9778;
    $zlookuptable['2.02'] = 0.9783;
    $zlookuptable['2.03'] = 0.9788;
    $zlookuptable['2.04'] = 0.9793;
    $zlookuptable['2.05'] = 0.9798;
    $zlookuptable['2.06'] = 0.9803;
    $zlookuptable['2.07'] = 0.9808;
    $zlookuptable['2.08'] = 0.9812;
    $zlookuptable['2.09'] = 0.9817;
    $zlookuptable['2.1'] = 0.9821;
    $zlookuptable['2.11'] = 0.9826;
    $zlookuptable['2.12'] = 0.9830;
    $zlookuptable['2.13'] = 0.9834;
    $zlookuptable['2.14'] = 0.9838;
    $zlookuptable['2.15'] = 0.9842;
    $zlookuptable['2.16'] = 0.9846;
    $zlookuptable['2.17'] = 0.9850;
    $zlookuptable['2.18'] = 0.9854;
    $zlookuptable['2.19'] = 0.9857;
    $zlookuptable['2.2'] = 0.9861;
    $zlookuptable['2.21'] = 0.9864;
    $zlookuptable['2.22'] = 0.9868;
    $zlookuptable['2.23'] = 0.9871;
    $zlookuptable['2.24'] = 0.9875;
    $zlookuptable['2.25'] = 0.9878;
    $zlookuptable['2.26'] = 0.9881;
    $zlookuptable['2.27'] = 0.9884;
    $zlookuptable['2.28'] = 0.9887;
    $zlookuptable['2.29'] = 0.9890;
    $zlookuptable['2.3'] = 0.9893;
    $zlookuptable['2.31'] = 0.9896;
    $zlookuptable['2.32'] = 0.9898;
    $zlookuptable['2.33'] = 0.9901;
    $zlookuptable['2.34'] = 0.9904;
    $zlookuptable['2.35'] = 0.9906;
    $zlookuptable['2.36'] = 0.9909;
    $zlookuptable['2.37'] = 0.9911;
    $zlookuptable['2.38'] = 0.9913;
    $zlookuptable['2.39'] = 0.9916;
    $zlookuptable['2.4'] = 0.9918;
    $zlookuptable['2.41'] = 0.9920;
    $zlookuptable['2.42'] = 0.9922;
    $zlookuptable['2.43'] = 0.9925;
    $zlookuptable['2.44'] = 0.9927;
    $zlookuptable['2.45'] = 0.9929;
    $zlookuptable['2.46'] = 0.9931;
    $zlookuptable['2.47'] = 0.9932;
    $zlookuptable['2.48'] = 0.9934;
    $zlookuptable['2.49'] = 0.9936;
    $zlookuptable['2.5'] = 0.9938;
    $zlookuptable['2.51'] = 0.9940;
    $zlookuptable['2.52'] = 0.9941;
    $zlookuptable['2.53'] = 0.9943;
    $zlookuptable['2.54'] = 0.9945;
    $zlookuptable['2.55'] = 0.9946;
    $zlookuptable['2.56'] = 0.9948;
    $zlookuptable['2.57'] = 0.9949;
    $zlookuptable['2.58'] = 0.9951;
    $zlookuptable['2.59'] = 0.9952;
    $zlookuptable['2.6'] = 0.9953;
    $zlookuptable['2.61'] = 0.9955;
    $zlookuptable['2.62'] = 0.9956;
    $zlookuptable['2.63'] = 0.9957;
    $zlookuptable['2.64'] = 0.9959;
    $zlookuptable['2.65'] = 0.9960;
    $zlookuptable['2.66'] = 0.9961;
    $zlookuptable['2.67'] = 0.9962;
    $zlookuptable['2.68'] = 0.9963;
    $zlookuptable['2.69'] = 0.9964;
    $zlookuptable['2.7'] = 0.9965;
    $zlookuptable['2.71'] = 0.9966;
    $zlookuptable['2.72'] = 0.9967;
    $zlookuptable['2.73'] = 0.9968;
    $zlookuptable['2.74'] = 0.9969;
    $zlookuptable['2.75'] = 0.9970;
    $zlookuptable['2.76'] = 0.9971;
    $zlookuptable['2.77'] = 0.9972;
    $zlookuptable['2.78'] = 0.9973;
    $zlookuptable['2.79'] = 0.9974;
    $zlookuptable['2.8'] = 0.9974;
    $zlookuptable['2.81'] = 0.9975;
    $zlookuptable['2.82'] = 0.9976;
    $zlookuptable['2.83'] = 0.9977;
    $zlookuptable['2.84'] = 0.9977;
    $zlookuptable['2.85'] = 0.9978;
    $zlookuptable['2.86'] = 0.9979;
    $zlookuptable['2.87'] = 0.9979;
    $zlookuptable['2.88'] = 0.9980;
    $zlookuptable['2.89'] = 0.9981;
    $zlookuptable['2.9'] = 0.9981;
    $zlookuptable['2.91'] = 0.9982;
    $zlookuptable['2.92'] = 0.9982;
    $zlookuptable['2.93'] = 0.9983;
    $zlookuptable['2.94'] = 0.9984;
    $zlookuptable['2.95'] = 0.9984;
    $zlookuptable['2.96'] = 0.9985;
    $zlookuptable['2.97'] = 0.9985;
    $zlookuptable['2.98'] = 0.9986;
    $zlookuptable['2.99'] = 0.9986;
    $zlookuptable['3.0'] = 0.9987;
    $zlookuptable['3'] = 0.9987;
    return $zlookuptable;
}
