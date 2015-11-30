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
 * @package mod_groupformation
 * @copyright 2014 Nora Wester
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');

$id = required_param('id', PARAM_INT);           // Course ID.

// In the template it isn't in a if statement.
// Ensure that the course specified is valid.
if (!$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST)) {
    print_error('Course ID is incorrect');
}

require_course_login($course);

// TODO @Rene: Auskommentiertes Code bitte löschen oder kommentieren
//	add_to_log($course->id, 'groupformation', 'view all', 'index.php?id='.$course->id, '');

$params = array(
    'context' => context_course::instance($course->id));

$event = \mod_groupformation\event\course_module_instance_list_viewed::create($params);
$event->add_record_snapshot('course', $course);
$event->trigger();

$strname = get_string('groupformationplural', 'groupformation');
//	$coursecontext = context_course::instance($course->id); // TODO @Rene: Auskommentiertes Code bitte löschen oder kommentieren

$PAGE->set_url('/mod/groupformation/index.php', array('id' => $id));
$PAGE->navbar->add($strname);
$PAGE->set_title("$course->shortname: $strname");
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('incourse');
//	$PAGE->set_context($coursecontext);     // TODO @Rene: Auskommentiertes Code bitte löschen oder kommentieren

echo $OUTPUT->header();
echo $OUTPUT->heading($strname);

if (!$groupformations = get_all_instances_in_course('groupformation', $course)) {
    notice(get_string('nogroupformation', 'groupformation'),
           new moodle_url('/course/view.php', array('id' => $course->id)));
}

$usesections = course_format_uses_sections($course->format);

$table = new html_table();
$table->attributes['class'] = 'generaltable mod_index';

// TODO @Rene: Auskommentiertes Code bitte löschen oder kommentieren
// 	if ($course->format == 'weeks') {
// 		$table->head = array(get_string('week'), get_string('name'));
// 		$table->align = array('center', 'left');
// 	} else if ($course->format == 'topics') {
// 		$table->head = array(get_string('topic'), get_string('name'));
// 		$table->align = array('center', 'left', 'left', 'left');
// 	} else {
// 		$table->head = array(get_string('name'));
// 		$table->align = array('left', 'left', 'left');
// 	}

if ($usesections) {
    $strsectionname = get_string('sectionname', 'format_' . $course->format);
    $table->head = array($strsectionname, $strname);
    $table->align = array('center', 'left');
} else {
    $table->head = array($strname);
    $table->align = array('left');
}

$modinfo = get_fast_modinfo($course);
$currentsection = '';
foreach ($modinfo->instances['groupformation'] as $cm) {
    $row = array();
    if ($usesections) {
        if ($cm->sectionnum !== $currentsection) {
            if ($cm->sectionnum) {
                $row[] = get_section_name($course, $cm->sectionnum);
            }
            if ($currentsection !== '') {
                $table->data[] = 'hr';
            }
            $currentsection = $cm->sectionnum;
        }
    }

    $class = $cm->visible ? null : array('class' => 'dimmed');

    $row[] = html_writer::link(new moodle_url('view.php', array('id' => $cm->id)), $cm->get_formatted_name(), $class);
    $table->data[] = $row;

}

// TODO @Rene: Auskommentiertes Code bitte löschen oder kommentieren
// 	foreach ($groupformations as $groupformation) {
// 		if (!$groupformation->visible) {
// 			$link = html_writer::link(
// 					new moodle_url('/mod/groupformation.php', array('id' => $groupformation->coursemodule)),
// 					format_string($groupformation->name, true),
// 					array('class' => 'dimmed'));
// 		} else {
// 			$link = html_writer::link(
// 					new moodle_url('/mod/groupformation.php', array('id' => $groupformation->coursemodule)),
// 					format_string($groupformation->name, true));
// 		}

// 		if ($course->format == 'weeks' or $course->format == 'topics') {
// 			$table->data[] = array($groupformation->section, $link);
// 		} else {
// 			$table->data[] = array($link);
// 		}
// 	}

// 	echo $OUTPUT->heading(get_string('modulnameplurals', 'groupformation'), 2);

echo html_writer::table($table);
echo $OUTPUT->footer();
