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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.
/**
 *
 * @package mod_groupformation
 * @author Nora Wester
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// TODO noch nicht getestet
// defined('MOODLE_INTERNAL') || die(); -> template
// namespace mod_groupformation\classes\lecturer_settings;
if (! defined ( 'MOODLE_INTERNAL' )) {
	die ( 'Direct access to this script is forbidden.' ); // / It must be included from a Moodle page
}

// require_once 'storage_manager.php';
require_once ($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/util/xml_writer.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/util/template_builder.php');
class mod_groupformation_student_import_export_controller {
	private $store;
	private $groupformationid;
	private $cmid;
	private $view = NULL;
	
	/**
	 * Constructs instance of import export controller
	 *
	 * @param integer $groupformationid        	
	 */
	public function __construct($groupformationid, $cmid) {
		$this->groupformationid = $groupformationid;
		$this->cmid = $cmid;
		
		$this->store = new mod_groupformation_storage_manager ( $groupformationid );
		
		$this->view = new mod_groupformation_template_builder ();
		$this->view->setTemplate ( 'wrapper_student_import_export' );
	}
	
	/**
	 * Generates answers and creates a file for download
	 * 
	 * @param integer $userid
	 * @return string
	 */
	private function generate_answers($userid) {
		$xmlwriter = new mod_groupformation_xml_writer ();
		
		// generate content for answer file for export
		$content = $xmlwriter->write ( $userid, $this->groupformationid );
		
		$filename = 'exportable_answers.xml';
		
		$context = context_module::instance ( $this->cmid );
		
		$fileinfo = array (
				'contextid' => $context->id,
				'component' => 'mod_groupformation',
				'filearea' => 'groupformation_answers',
				'itemid' => $userid,
				'filepath' => '/',
				'filename' => $filename 
		);
		
		$file_storage = get_file_storage ();
		
		if ($file_storage->file_exists ( $fileinfo ['contextid'], $fileinfo ['component'], $fileinfo ['filearea'], $fileinfo ['itemid'], $fileinfo ['filepath'], $fileinfo ['filename'] )) {
			$file = $file_storage->get_file ( $fileinfo ['contextid'], $fileinfo ['component'], $fileinfo ['filearea'], $fileinfo ['itemid'], $fileinfo ['filepath'], $fileinfo ['filename'] );
			$file->delete ();
		}
		
		$file = $file_storage->create_file_from_string ( $fileinfo, $content );
		
		$url = moodle_url::make_pluginfile_url ( $file->get_contextid (), $file->get_component (), $file->get_filearea (), $file->get_itemid (), $file->get_filepath (), $file->get_filename () );
		
		$urlstring = $url->out ();
		
		return $urlstring;
	}
	
	/**
	 * Outputs import and export options
	 *
	 * @param integer $userid        	
	 */
	public function render($userid) {
		global $DB;
		
		$export_description = get_string ( 'export_description_no', 'groupformation' );
		$export_button = true;
		$export_url = '';
		
		if ($this->store->already_answered ()) {
			
			$url = $this->generate_answers ( $userid );
			
			$export_description = get_string ( 'export_description_yes', 'groupformation' );
			$export_button = false;
			$export_url = $url;
		}
		
		$this->view->assign ( 'export_description', $export_description );
		$this->view->assign ( 'export_button', $export_button );
		$this->view->assign ( 'export_url', $export_url );
		
		$this->view->assign ( 'import_description', get_string ( 'import_description', 'groupformation' ) );
		$this->view->assign ( 'import_form', 'TODO import form with file submission' );
		return $this->view->loadTemplate ();
	}
}