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
 * The main groupformation configuration form
 *
 * @package mod_groupformation
 * @copyright 2014 Nora Wester
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// defined('MOODLE_INTERNAL') || die(); -> template
if (! defined ( 'MOODLE_INTERNAL' )) {
	die ( 'Direct access to this script is forbidden.' ); // / It must be included from a Moodle page
}

require_once ($CFG->dirroot . '/course/moodleform_mod.php');
require_once ($CFG->dirroot . '/mod/groupformation/lib.php'); // not in the template
require_once ($CFG->dirroot . '/mod/groupformation/locallib.php');
class mod_groupformation_mod_form extends moodleform_mod {
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see moodleform::definition()
	 */
	function definition() {
		global $PAGE;
		
		// Import jQuery and js file
		addjQuery ( $PAGE, 'settings_functions.js' );
		
		// global $CFG, $DB, $OUTPUT;
		$mform = & $this->_form;
		
		// Adding the "general" fieldset, where all the common settings are showed.
		$mform->addElement ( 'header', 'general', get_string ( 'general', 'form' ) );
		
		// Adding the standard "name" field.
		$mform->addElement ( 'text', 'name', get_string ( 'groupformationname', 'groupformation' ), array (
				'size' => '64' 
		) );
		if (! empty ( $CFG->formatstringstriptags )) {
			$mform->setType ( 'name', PARAM_TEXT );
		} else {
			$mform->setType ( 'name', PARAM_CLEAN );
		}
		$mform->addRule ( 'name', null, 'required', null, 'client' );
		$mform->addRule ( 'name', get_string ( 'maximumchars', '', 255 ), 'maxlength', 255, 'client' );
		
		// Adding the standard "intro" and "introformat" fields.
		$this->add_intro_editor ();
		
		// Adding the availability settings
		$mform->addElement ( 'header', 'timinghdr', get_string ( 'availability' ) );
		$mform->addElement ( 'date_time_selector', 'timeopen', get_string ( 'feedbackopen', 'feedback' ), array (
				'optional' => true 
		) );
		$mform->addElement ( 'date_time_selector', 'timeclose', get_string ( 'feedbackclose', 'feedback' ), array (
				'optional' => true 
		) );
		
		// Adding the rest of groupformation settings, spreeading all them into this fieldset
		$mform->addElement ( 'header', 'groupformationsettings', get_string ( 'groupformationsettings', 'groupformation' ) );
		
		$this->generateHTMLforNonJS ( $mform );
		
		$this->generateHTMLforJS ( $mform );
		
		// Add standard grading elements.
		$this->standard_grading_coursemodule_elements ();
		
		// Add standard elements, common to all modules.
		$this->standard_coursemodule_elements ();
		
		// Add standard buttons, common to all modules.
		$this->add_action_buttons ();
	}
	
	/**
	 *
	 * @param moodleform_mod $mform        	
	 */
	function changesPossible(&$mform) {
		global $DB;
		// Are changes possible?
		// check if somebody submitted an answer already
		$id = $this->_instance;
		if ($id != '') {
			$count = $DB->count_records ( 'groupformation_answer', array (
					'groupformation' => $id 
			) );
			if ($count > 0)
				return False;
		}
		return True;
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see moodleform_mod::validation()
	 */
	function validation($data, $files) {
		$errors = array ();
		// Check if szenario is selected
		if ($data ['szenario'] == 0) {
			$errors ['szenario'] = get_string ( 'scenario_error', 'groupformation' );
		}
		
		// check if groupname is too long
		if (strlen ( $data ['groupname'] ) > 100) {
			$errors ['groupname'] = get_string ( 'groupname_error', 'groupformation' );
		}
		
		// Check if maxmembers or maxgroups is selected and number is chosen
		if ($data ['groupoption'] == 0) {
			if (! (is_numeric ( $data ['maxmembers'] )) || ! (intval ( $data ['maxmembers'] ) > 0)) {
				$errors ['maxmembers'] = get_string ( 'maxmembers_error', 'groupformation' );
			}
		} elseif ($data ['groupoption'] == 1) {
			if (! (is_numeric ( $data ['maxgroups'] )) || ! (intval ( $data ['maxgroups'] ) > 0)) {
				$errors ['maxgroups'] = get_string ( 'maxgroups_error', 'groupformation' );
			}
		}
		
		// Check if evaluation method is selected
		if ($data ['evaluationmethod'] == 0) {
			$errors ['evaluationmethod'] = get_string ( 'evaluationmethod_error', 'groupformation' );
		}
		
		if ($data ['evaluationmethod'] == 2 && (! (is_numeric ( $data ['maxpoints'] )) || ! (intval ( $data ['maxpoints'] ) <= 100) || ! (intval ( $data ['maxpoints'] ) > 0))) {
			$errors ['maxpoints'] = get_string ( 'maxpoints_error', 'groupformation' );
		}
		return $errors;
	}
	
	/**
	 * generates HTML code for JS version
	 *
	 * @param unknown $mform        	
	 */
	function generateHTMLforJS(&$mform) {
		// open div tag for js related content
		$mform->addElement ( 'html', '<div id="js-content" style="display:none;">' );
		
		// add scenario related HTML
		$mform->addElement ( 'html', '
	        		<div class="grid">
                    <div class="col_100">
                        <h4 class="required">' . get_string ( 'scenario_description', 'groupformation' ) . '</h4>
                    </div>
				
					<div class="col_100">
                        <div class="errors">
                            <p id="szenario_error"></p>
                        </div>
                    </div>
			
                    <div id="szenarioradios">
                        <div class="grid">
			
                            <div class="col_33">
			
                                <input type="radio" name="js_szenario" id="project" value="project"  />
                                <label class="col_100 szenarioLabel" for="project" ><h3>' . get_string ( 'scenario_projectteams', 'groupformation' ) . '</h3>
                                    <p><small>' . get_string ( 'scenario_projectteams_description', 'groupformation' ) . '</small></p>
                                </label>
                            </div>
			
                            <div class="col_33">
			
                                <input type="radio" name="js_szenario" id="homework" value="homework" />
                                <label class="col_100 szenarioLabel" for="homework" ><h3>' . get_string ( 'scenario_homeworkgroups', 'groupformation' ) . '</h3>
                                    <p><small>' . get_string ( 'scenario_homeworkgroups_description', 'groupformation' ) . '</small></p>
                                </label>
                            </div>
			
                            <div class="col_33">
			
                                <input type="radio" name="js_szenario" id="presentation" value="presentation" />
                                <label class="col_100 szenarioLabel" for="presentation"><h3>' . get_string ( 'scenario_presentationgroups', 'groupformation' ) . '</h3>
                                    <p><small>' . get_string ( 'scenario_presentationgroups_description', 'groupformation' ) . '</small></p>
                                </label>
                            </div>
			
                        </div> <!-- /grid  -->
                    </div>
			
                </div> <!-- /grid  -->

	        		' );
		
		// wrapper of the szenario
		$mform->addElement ( 'html', '<div id="js_szenarioWrapper">' );
		
		// add checkbox preknowledge
		$mform->addElement ( 'html', '
					<div class="col_100">
                        <h4 class="optional"><label for="id_js_knowledge">
                          <input type="checkbox" id="id_js_knowledge" name="chbKnowledge" value="wantKnowledge">
                          ' . get_string ( 'knowledge_description', 'groupformation' ) . '</h4>
                        </label> 
                    </div>' );
		
		// add dynamic input fields preknowledge and Preview
		$mform->addElement ( 'html', '
					<div class="grid">
                    <div class="col_100">
                       
                        <div id="js_knowledgeWrapper">
                        
                       <!-- <p>' . get_string ( 'knowledge_description_extended', 'groupformation' ) . '</p> -->

						<p id="knowledfeInfo"></p>
				
						<p id="knowledfeInfoProject">' . get_string ( 'knowledge_info_project', 'groupformation' ) . '</p>
                        
                        <p id="knowledfeInfoHomework">' . get_string ( 'knowledge_info_homework', 'groupformation' ) . '</p>
                        
                        <p id="knowledfeInfoPresentation">' . get_string ( 'knowledge_info_presentation', 'groupformation' ) . '</p>
				
				
                            <div class="grid">
                            <div id="prk">    
                            <div class="multi_field_wrapper persist-area">
                                <div class="col_50">
                                <div id="" class="btn_wrap">
                                    <label>
                                        <button type="button" class="add_field"></button>' . get_string ( 'add_line', 'groupformation' ) . '</label> 
                                </div>
                                   
                                                                    
<!--                      Die Input Felder-->
                                    
                                        <div class="multi_fields">
                                            <div class="multi_field" id="inputprk0">
                                                <input class="respwidth js_preknowledgeInput" type="text">
                                                <button type="button" class="remove_field"></button>
                                            </div>
                                            <div class="multi_field" id="inputprk1">
                                                <input class="respwidth js_preknowledgeInput" type="text">
                                                <button type="button" class="remove_field"></button>
                                            </div>
                                            <div class="multi_field" id="inputprk2">
                                                <input class="respwidth js_preknowledgeInput" type="text">
                                                <button type="button" class="remove_field"></button>
                                            </div>
                                        </div>
                                    </div> <!-- /col_50 -->   
                                    
<!--                      Die Vorschau      -->
                                    <div class="col_50">
                                        
                                        <h3>' . get_string ( 'preview', 'groupformation' ) . '</h3>
                    
                                            <div class="col_100">' . 
		// '<h4 class="view_on_mobile">'.get_string('knowledge_question','groupformation').'</h4>'.
		
		'<table class="responsive-table">
                                                    <colgroup>
                                                        <col class="firstCol">
                                                        <col width="36%">
                                                    </colgroup>

                                                    <thead>
                                                      <tr>
                                                          <th scope="col">' . get_string ( 'knowledge_question', 'groupformation' ) . '</th>
                                                        <th scope="col"><div class="legend">' . get_string ( 'knowledge_scale', 'groupformation' ) . '</div></th>
                                                      </tr>
                                                    </thead>
                                                    <tbody id="preknowledges">
                                                      <tr class="knowlRow" id="prkRow0">
                                                        <th scope="row">Beispiel 1</th>
                                                        <td data-title="' . get_string ( 'knowledge_scale', 'groupformation' ) . '" class="range"><span >0</span><input type="range" min="0" max="100" value="0" /><span>100</span></td>
                                                      </tr>
                                                    <tr class="knowlRow" id="prkRow1">
                                                        <th scope="row">Beispiel 1</th>
                                                        <td data-title="' . get_string ( 'knowledge_scale', 'groupformation' ) . '" class="range"><span >0</span><input type="range" min="0" max="100" value="0" /><span>100</span></td>
                                                      </tr>
                                                    <tr class="knowlRow" id="prkRow2">
                                                        <th scope="row">Beispiel 1</th>
                                                        <td data-title="' . get_string ( 'knowledge_scale', 'groupformation' ) . '" class="range"><span >0</span><input type="range" min="0" max="100" value="0" /><span>100</span></td>
                                                      </tr>
                                                    </tbody>
                                                  </table>
                                            </div>

                                    </div> <!-- /col_50 --> 
                                </div>  <!-- /multi_field_wrapper-->
                                </div> <!-- Anchor-->
                                
                            </div> <!-- /.grid -->
                        </div> <!-- /.knowledgeWrapper -->
                    </div>   <!--/col_100 -->
                </div> <!-- /grid --> 
                ' );
		
		// add checkbox topics
		$mform->addElement ( 'html', '
                <div class="grid">
                    
                    <div class="col_100">
						<h4 id="headerTopics" class="optional"><label for="id_js_topics">
                          <input type="checkbox" id="id_js_topics" name="chbTopics" value="wantTopics">
                          ' . get_string ( 'topics_description', 'groupformation' ) . '</h4>
                        </label> 
                    </div>' );
		
		// add dynamic input fields topics with preview
		$mform->addElement ( 'html', '                    
                    <div class="col_100">
                        <div id="js_topicsWrapper">
				
                        <p>' . get_string ( 'topics_description_extended', 'groupformation' ) . '</p>
                                    
                            <div class="grid">
                            <div id="tpc">    
                            <div class="multi_field_wrapper persist-area">
                                <div class="col_50">
                                <div id="" class="btn_wrap">
                                    <label>
                                        <button type="button" class="add_field"></button>' . get_string ( 'add_line', 'groupformation' ) . '</label> 
                                </div>
                                   
                                                                    
<!--                      Die Input Felder-->
                                    
                                        <div class="multi_fields">
                                            <div class="multi_field" id="inputtpc0">
                                                <input class="respwidth js_topicInput" type="text">
                                                <button type="button" class="remove_field"></button>
                                            </div>
                                            <div class="multi_field" id="inputtpc1">
                                                <input class="respwidth js_topicInput" type="text">
                                                <button type="button" class="remove_field"></button>
                                            </div>
                                            <div class="multi_field" id="inputtpc2">
                                                <input class="respwidth js_topicInput" type="text">
                                                <button type="button" class="remove_field"></button>
                                            </div>
                                        </div>
                                    </div> <!-- /col_50 -->   
                                    
<!--                      Die Vorschau      -->
                                    <div class="col_50">
                                        
                                        <h3>' . get_string ( 'preview', 'groupformation' ) . '</h3>
                    
                                        <div class="col_100">' . 
		// '<h4 class="view_on_mobile">'.get_string('topics_question','groupformation').'</h4>'.
		
		'<p id="topicshead">' . get_string ( 'topics_question', 'groupformation' ) . '</p>
											<span id="topicsDummy" style="display:none;">' . get_string ( 'topics_dummy', 'groupformation' ) . '</span>
                                            <ul class="sortable_topics" id="previewTopics">
                                              <li class="topicLi" id="tpcRow0" class=""><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>' . get_string ( 'topics_dummy', 'groupformation' ) . 'Thema 1</li>
                                              <li class="topicLi" id="tpcRow1" class=""><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>' . get_string ( 'topics_dummy', 'groupformation' ) . 'Thema 2</li>
                                              <li class="topicLi" id="tpcRow2" class=""><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>' . get_string ( 'topics_dummy', 'groupformation' ) . 'Thema 3</li>
                                            </ul>
                                        </div>
                                    </div> <!-- /col_50 --> 
                                </div>  <!-- /multi_field_wrapper-->
                                </div> <!-- Anchor-->
                                
                            </div> <!-- /.grid -->
                        </div> <!-- /.topicWrapper -->
                    </div>   <!--/col_100 -->
                </div>  <!-- /grid --> 
					' );
		
		// add Groupsize Options
		$mform->addElement ( 'html', '
                <div class="grid option_row">

                    <div class="col_100 ">
                        <h4 class="required">' . get_string ( 'groupoption_description', 'groupformation' ) . '<span class="toolt" tooltip="' . get_string ( 'groupoption_help', 'groupformation' ) . '"></span></h4>
                    </div>
				
					<div class="col_100">
                        <div class="errors">
                            <p id="maxmembers_error"></p>
                        </div>
                    </div>
                    <div class="col_100">
                        <div class="errors">
                            <p id="maxgroups_error"></p>
                        </div>
                    </div>

                    <div class="col_50"><label><input type="radio" name="group_opt" id="group_opt_size" value="group_size" checked="checked" />
                                ' . get_string ( 'maxmembers', 'groupformation' ) . '</label>
								<input type="number" class="group_opt" id="group_size" min="0" max="100" value="0" /></div>
                    <div class="col_50"><label><input type="radio" name="group_opt" id="group_opt_numb" value="numb_of_groups"/>
                                ' . get_string ( 'maxgroups', 'groupformation' ) . '</label>
								<input type="number" class="group_opt" id="numb_of_groups"  min="0" max="100" value="0" disabled="disabled" /></div>
                </div> <!-- /grid -->' );
		
		$mform->addElement ( 'html', '
                <div class="grid option_row">

                    <div class="col_100 ">
                        <h4 class="optional">' . get_string ( 'groupname', 'groupformation' ) . '<span class="toolt" tooltip="' . get_string ( 'groupname_help', 'groupformation' ) . '"></span></h4>
                    </div>
				
				</div> <!-- /grid -->' );
		
		// add evaluation options
		$mform->addElement ( 'html', '
                
                <div class="grid option_row">

                    <div class="col_100">
                        <h4 class="required">' . get_string ( 'evaluationmethod_description', 'groupformation' ) . '</h4>
                    </div>
				
					<div class="col_100">
                        <div class="errors">
                            <p id="evaluationmethod_error"></p>
                        </div>
                    </div>

                    <div class="col_100">
                        <div class="errors">
                            <p id="maxpoints_error"></p>
                        </div>
                    </div>
				
				
                    <div class="col_66">
                        <select id="js_evaluationmethod">
							<option value="chooseM">' . get_string ( 'choose_evaluationmethod', 'groupformation' ) . '</option>
                            <option value="grades">' . get_string ( 'grades', 'groupformation' ) . '</option>
                            <option value="points">' . get_string ( 'points', 'groupformation' ) . '</option>
                            <option value="justpass">' . get_string ( 'justpass', 'groupformation' ) . '</option>
                            <option value="novaluation">' . get_string ( 'noevaluation', 'groupformation' ) . '</option>
                        </select>
						<span id="max_points_wrapper"><input type="number" id="max_points"  min="0" max="100" value="100" /><span class="toolt" tooltip="Bitte maximale Punktzahl eingeben."></span></span>
                    </div>

                </div> <!-- /grid -->
                ' ); // TODO @Rene: lang File f�r tooltip "Maximale Punktzahl" (siehe Zeile oben)
		     
		// close wrapper of the szenario
		$mform->addElement ( 'html', '</div>' );
		
		// close div tag for js related content
		$mform->addElement ( 'html', '</div id="js-content">' );
	}
	
	/**
	 * generates moodle form elements for non-JS version
	 *
	 * @param moodleform_mod $mform        	
	 */
	function generateHTMLforNonJS(&$mform) {
		$this->changesPossible ( $mform );
		
		// open div tag for non js related content
		$mform->addElement ( 'html', '<div id="non-js-content">' );
		
		// no changes possible hint
		$changemsg = '<div class="fitem" id="nochangespossible"';
		if (! $this->changesPossible ( $mform )) {
			$changemsg .= ' ><span value="1"';
		} else {
			$changemsg .= ' style="display:none;"><span value="0"';
		}
		$changemsg .= ' style="color:red;">' . get_string ( 'nochangespossible', 'groupformation' ) . '</span></div>';
		$mform->addElement ( 'html', $changemsg );
		
		// add field Szenario choice
		$mform->addElement ( 'select', 'szenario', get_string ( 'scenario', 'groupformation' ), array (
				get_string ( 'choose_scenario', 'groupformation' ),
				get_string ( 'scenario_projectteams', 'groupformation' ),
				get_string ( 'scenario_homeworkgroups', 'groupformation' ),
				get_string ( 'scenario_presentationgroups', 'groupformation' ) 
		), null );
		
		$mform->addRule ( 'szenario', get_string ( 'scenario_error', 'groupformation' ), 'required', null, 'client' );
		
		// add fields for Knowledge questions
		$mform->addElement ( 'checkbox', 'knowledge', get_string ( 'knowledge', 'groupformation' ) );
		$mform->addElement ( 'textarea', 'knowledgelines', get_string ( 'knowledge', 'groupformation' ), 'wrap="virtual" rows="10" cols="50"' );
		
		$mform->disabledIf ( 'knowledgelines', 'knowledge', 'notchecked' );
		
		// add fields for topic choices
		$mform->addElement ( 'checkbox', 'topics', get_string ( 'topics', 'groupformation' ) );
		$mform->addElement ( 'textarea', 'topiclines', get_string ( 'topics', 'groupformation' ), 'wrap="virtual" rows="10" cols="50"' );
		
		$mform->disabledIf ( 'topiclines', 'topics', 'notchecked' );
		
		// add fields for max members or max groups
		$radioarray = array ();
		$radioarray [] = & $mform->createElement ( 'radio', 'groupoption', '', get_string ( 'maxmembers', 'groupformation' ), 0, null );
		$radioarray [] = & $mform->createElement ( 'radio', 'groupoption', '', get_string ( 'maxgroups', 'groupformation' ), 1, null );
		$mform->addGroup ( $radioarray, 'radioar', get_string ( 'groupoptions', 'groupformation' ), array (
				' ' 
		), false );
		$mform->addRule ( 'radioar', get_string ( 'maxmembers_error', 'groupformation' ), 'required', null, 'client' );
		
		$mform->addElement ( 'text', 'maxmembers', get_string ( 'maxmembers', 'groupformation' ), null );
		$mform->addElement ( 'text', 'maxgroups', get_string ( 'maxgroups', 'groupformation' ), null );
		
		$mform->setType ( 'maxmembers', PARAM_NUMBER );
		$mform->setType ( 'maxgroups', PARAM_NUMBER );
		
		$mform->disabledIf ( 'maxmembers', 'groupoption', 'eq', '1' );
		$mform->disabledIf ( 'maxgroups', 'groupoption', 'eq', '0' );
		$mform->disabledIf ( 'maxmembers', 'groupoption', 'eq', '1' );
		
		// add group name field
		$mform->addElement ( 'text', 'groupname', get_string ( 'groupname', 'groupformation' ), array (
				'size' => '64' 
		) );
		if (! empty ( $CFG->formatstringstriptags )) {
			$mform->setType ( 'groupname', PARAM_TEXT );
		} else {
			$mform->setType ( 'groupname', PARAM_CLEAN );
		}
		$mform->addHelpButton ( 'groupname', 'groupname', 'groupformation' );
		
		// add field for evaluation method
		$mform->addElement ( 'select', 'evaluationmethod', get_string ( 'evaluationmethod_description', 'groupformation' ), array (
				get_string ( 'choose_evaluationmethod', 'groupformation' ),
				get_string ( 'grades', 'groupformation' ),
				get_string ( 'points', 'groupformation' ),
				get_string ( 'justpass', 'groupformation' ),
				get_string ( 'noevaluation', 'groupformation' ) 
		), null );
		
		$mform->addRule ( 'evaluationmethod', get_string ( 'evaluationmethod_error', 'groupformation' ), 'required', null, 'client' );
		
		$mform->addElement ( 'text', 'maxpoints', get_string ( 'maxpoints', 'groupformation' ) );
		
		$mform->disabledIf ( 'maxpoints', 'evaluationmethod', 'neq', '2' );
		$mform->setType ( 'maxpoints', PARAM_NUMBER );
		
		// close div tag for non-js related content
		$mform->addElement ( 'html', '</div id="non-js-content">' );
	}
}

