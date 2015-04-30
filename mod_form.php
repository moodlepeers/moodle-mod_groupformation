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

	//defined('MOODLE_INTERNAL') || die();  -> template

	if (!defined('MOODLE_INTERNAL')) {
		die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
	}

	require_once($CFG->dirroot.'/course/moodleform_mod.php');
	require_once($CFG->dirroot.'/mod/groupformation/lib.php');  // not in the template
	require_once($CFG->dirroot.'/mod/groupformation/locallib.php');
	
	class mod_groupformation_mod_form extends moodleform_mod {
		
		/**
		 * (non-PHPdoc)
		 * @see moodleform::definition()
		 */
		function definition() {
			global $PAGE;
				
			// global $CFG, $DB, $OUTPUT;  
			$mform =& $this->_form;
		
			// Adding the "general" fieldset, where all the common settings are showed.
			$mform->addElement('header', 'general', get_string('general', 'form'));
			
			// TODO @EG hier ist Jquery eingebunden worden ohne Fehler!
			addjQuery($PAGE);
			
			// Adding the standard "name" field.
			$mform->addElement('text', 'name', get_string('groupformationname', 'groupformation'), array('size' => '64'));
			if (!empty($CFG->formatstringstriptags)) {
				$mform->setType('name', PARAM_TEXT);
			} else {
				$mform->setType('name', PARAM_CLEAN);
			}
			$mform->addRule('name', null, 'required', null, 'client');
			$mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
			$mform->addHelpButton('name', 'groupformationname', 'groupformation');
			
			
			// Adding the standard "intro" and "introformat" fields.
			$this->add_intro_editor();
			
			// Adding the availability settings
			$mform->addElement('header', 'timinghdr', get_string('availability'));
			$mform->addElement('date_time_selector', 'timeopen', get_string('feedbackopen', 'feedback'),
        				array('optional' => true));
			$mform->addElement('date_time_selector', 'timeclose', get_string('feedbackclose', 'feedback'),
	            array('optional' => true));
	        
	        // Adding the rest of groupformation settings, spreeading all them into this fieldset
			$mform->addElement('header', 'groupformationsettings', get_string('groupformationsettings', 'groupformation'));

			// Adding field Szenario choice
	        $mform->addElement('select', 'szenario', get_string('scenario', 'groupformation'), 
	       			array(
	       					get_string('choose_scenario','groupformation'),
	       					get_string('project', 'groupformation'),
	       					get_string('homework', 'groupformation'),
	       					get_string('presentation', 'groupformation')
	       			), null);
	        $mform->addRule('szenario', get_string('scenario_error', 'groupformation'), 'required', null, 'client');
	        
	        // Adding fields for Knowledge questions
	        $mform->addElement('checkbox', 'knowledge', get_string('knowledge', 'groupformation'));
	        $mform->addElement('textarea', 'knowledgelines', get_string('knowledge', 'groupformation'), 'wrap="virtual" rows="10" cols="50"');
	        $mform->disabledIf('knowledgelines', 'knowledge', 'notchecked');
	        
	        // Adding fields for topic choices
	        $mform->addElement('checkbox', 'topics', get_string('topics', 'groupformation'));
	        $mform->addElement('textarea', 'topiclines', get_string('topics', 'groupformation'), 'wrap="virtual" rows="10" cols="50"');
	        $mform->disabledIf('topiclines', 'topics', 'notchecked');
	        
	        // Adding fields for max members or max groups
	        $radioarray=array();
	        $radioarray[] =& $mform->createElement('radio', 'groupoption', '', get_string('maxmembers', 'groupformation'),0, null);
	        $radioarray[] =& $mform->createElement('radio', 'groupoption', '', get_string('maxgroups', 'groupformation'), 1, null);
	        $mform->addGroup($radioarray, 'radioar', get_string('groupoptions', 'groupformation'), array(' '), false);
			$mform->addRule('radioar', get_string('maxmembers_error', 'groupformation'), 'required', null, 'client');
	        $options = array();
			$options[0] = get_string('choose_number', 'groupformation');
	        for ($i = 1; $i <= 20; $i ++) {
	            $options[$i] = $i;
	        }
	        $mform->addElement('select', 'maxmembers', get_string('maxmembers', 'groupformation'), $options, null);
	        $mform->addElement('select', 'maxgroups', get_string('maxgroups', 'groupformation'), $options, null);

	        // Adding field for evaluation method
	        $mform->addElement('select', 'evaluationmethod', get_string('evaluationmethod', 'groupformation'),
	        		array(
	        				get_string('choose_evaluationmethod', 'groupformation'),
	        				get_string('grades', 'groupformation'),
	        				get_string('points', 'groupformation'),
	        				get_string('justpass', 'groupformation'),
	        				get_string('noevaluation', 'groupformation'),
	        		), null);
	        $mform->addRule('evaluationmethod', get_string('szenario_error', 'groupformation'), 'required', null, 'client');
	         
	        $this->generateHTMLforJS($mform);
	        
			// Add standard grading elements.
			// TODO @all Brauchen wir die Moodlebewertungsoptionen überhaupt? Ist ja keine Aufgabe mit Abgabe sondern die 
			// Gruppenformation. Die Abfrage nach der Bewertungsmethode wird oben gemacht und ist ja eigentlich moodle 
			// unspezifisch, oder? Habs vorerst mal auskommentiert.
//muss drin bleiben, da es sonst (zumindestens bei mir) eine Fehlermeldung gibt        
 			$this->standard_grading_coursemodule_elements();
			
			// Add standard elements, common to all modules.
			$this->standard_coursemodule_elements();
			
			// Add standard buttons, common to all modules.
			$this->add_action_buttons();
		}
	
		/**
		 * (non-PHPdoc)
		 * @see moodleform_mod::validation()
		 */
		function validation($data, $files){
			$errors= array();
			// Check if szenario is selected
			if ($data['szenario']==0){
				$errors['szenario']=get_string('szenario_error', 'groupformation');
			}
			
			// Check if maxmembers or maxgroups is selected and number is chosen
			if ($data['groupoption']==0){
				if ($data['maxmembers']==0){
					$errors['maxmembers']=get_string('maxmembers_error', 'groupformation');
				}
			}elseif ($data['groupoption']==1){
				if ($data['maxgroups']==0){
					$errors['maxgroups']=get_string('maxgroups_error', 'groupformation');
				}
			}
			
			// Check if evaluation method is selected
			if ($data['evaluationmethod']==0){
				$errors['evaluationmethod']=get_string('evaluationmethod_error', 'groupformation');
			}
			return $errors;
		}
		
		function generateHTMLforJS(&$mform){
			$mform->addElement('html', '
	        		<div class="grid">
                    <div class="col_100">
                        <h4 class="required">'.get_string('scenario_description','groupformation').'</h4>
                    </div>
			
                    <div class="szenarioradios">
                        <div class="grid">
			
                            <div class="col_33">
			
                                <input type="radio" name="js_szenario" id="project" value="project"  />
                                <label class="col_100 pad20" for="project" ><h3>'.get_string('scenario_projectteams','groupformation').'</h3>
                                    <p><small>'.get_string('scenario_projectteams_description','groupformation').'</small></p>
                                </label>
                            </div>
			
                            <div class="col_33">
			
                                <input type="radio" name="js_szenario" id="homework" value="homework" />
                                <label class="col_100 pad20" for="homework" ><h3>'.get_string('scenario_homeworkgroups','groupformation').'</h3>
                                    <p><small>'.get_string('scenario_homeworkgroups_description','groupformation').'</small></p>
                                </label>
                            </div>
			
                            <div class="col_33">
			
                                <input type="radio" name="js_szenario" id="presentation" value="presentation" />
                                <label class="col_100 pad20" for="presentation"><h3>'.get_string('scenario_presentationgroups','groupformation').'</h3>
                                    <p><small>'.get_string('scenario_presentationgroups_description','groupformation').'</small></p>
                                </label>
                            </div>
			
                        </div> <!-- /grid  -->
                    </div>
			
                </div> <!-- /grid  -->

	        		');
			
			//add Checkbox Preknowledge
			$mform->addElement('html', '
					<div class="col_100">
                        <h4 class="optional"><label for="wantKnowledge">
                          <input type="checkbox" name="chbKnowledge" value="wantKnowledge">
                          '.get_string('knowledge_description','groupformation').'</h4>
                        </label> 
                    </div>');

			
			//add dynamic Inputfields Preknowledge and Preview
			$mform->addElement('html', '
					<div class="grid">
                    <div class="col_100">
                       
                        <div class="knowledgeWrapper">
                        
                        <p>'.get_string('knowledge_description_extended','groupformation').'</p>
                                    
                            <div class="grid">
                            <div id="prk">    
                            <div class="multi_field_wrapper persist-area">
                                <div class="col_50">
                                <div id="" class="btn_wrap">
                                    <label>
                                        <button type="button" class="add_field"></button>'.get_string('add_line','groupformation').'</label> 
                                </div>
                                   
                                                                    
<!--                      Die Input Felder-->
                                    
                                        <div class="multi_fields">
                                            <div class="multi_field" id="inputprk0">
                                                <input class="respwidth" type="text" name="knowledge[]">
                                                <button type="button" class="remove_field"></button>
                                            </div>
                                            <div class="multi_field" id="inputprk1">
                                                <input class="respwidth" type="text" name="knowledge[]">
                                                <button type="button" class="remove_field"></button>
                                            </div>
                                            <div class="multi_field" id="inputprk2">
                                                <input class="respwidth" type="text" name="knowledge[]">
                                                <button type="button" class="remove_field"></button>
                                            </div>
                                        </div>
                                    </div> <!-- /col_50 -->   
                                    
<!--                      Die Vorschau      -->
                                    <div class="col_50">
                                        
                                        <h3>'.get_string('preview','groupformation').'</h3>
                    
                                            <div class="col_100">'.
//                                                 '<h4 class="view_on_mobile">'.get_string('knowledge_question','groupformation').'</h4>'.
					
                                                '<table class="responsive-table">
                                                    <colgroup>
                                                        <col class="firstCol">
                                                        <col width="36%">
                                                    </colgroup>

                                                    <thead>
                                                      <tr>
                                                          <th scope="col">'.get_string('knowledge_question','groupformation').'</th>
                                                        <th scope="col"><div class="legend">'.get_string('knowledge_scale','groupformation').'</div></th>
                                                      </tr>
                                                    </thead>
                                                    <tbody id="preknowledges">
                                                      <tr class="knowlRow" id="prkRow0">
                                                        <th scope="row">Beispiel 1</th>
                                                        <td data-title="'.get_string('knowledge_scale','groupformation').'" class="range"><span >0</span><input type="range" min="0" max="100" value="0" /><span>100</span></td>
                                                      </tr>
                                                    <tr class="knowlRow" id="prkRow1">
                                                        <th scope="row">Beispiel 1</th>
                                                        <td data-title="'.get_string('knowledge_scale','groupformation').'" class="range"><span >0</span><input type="range" min="0" max="100" value="0" /><span>100</span></td>
                                                      </tr>
                                                    <tr class="knowlRow" id="prkRow2">
                                                        <th scope="row">Beispiel 1</th>
                                                        <td data-title="'.get_string('knowledge_scale','groupformation').'" class="range"><span >0</span><input type="range" min="0" max="100" value="0" /><span>100</span></td>
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
                ');
			

			//add checkbox Topics
			$mform->addElement('html', '
                <div class="grid">
                    
                    <div class="col_100">
                        <h4 class="optional"><label for="wantTopics">
                          <input type="checkbox" name="chbTopics" value="wantTopics">
                          '.get_string('topics_description','groupformation').'</h4>
                        </label> 
                    </div>');
			
			//add dynamic Inputfields Topics with Preview
			$mform->addElement('html', '                    
                    <div class="col_100">
                        <div class="topicsWrapper">
                        <p>'.get_string('topics_description_extended','groupformation').'</p>
                                    
                            <div class="grid">
                            <div id="tpk">    
                            <div class="multi_field_wrapper persist-area">
                                <div class="col_50">
                                <div id="" class="btn_wrap">
                                    <label>
                                        <button type="button" class="add_field"></button>'.get_string('add_line','groupformation').'</label> 
                                </div>
                                   
                                                                    
<!--                      Die Input Felder-->
                                    
                                        <div class="multi_fields">
                                            <div class="multi_field" id="inputtopic0">
                                                <input class="respwidth" type="text" name="topic[]">
                                                <button type="button" class="remove_field"></button>
                                            </div>
                                            <div class="multi_field" id="inputtopic1">
                                                <input class="respwidth" type="text" name="topic[]">
                                                <button type="button" class="remove_field"></button>
                                            </div>
                                            <div class="multi_field" id="inputtopic1">
                                                <input class="respwidth" type="text" name="topic[]">
                                                <button type="button" class="remove_field"></button>
                                            </div>
                                        </div>
                                    </div> <!-- /col_50 -->   
                                    
<!--                      Die Vorschau      -->
                                    <div class="col_50">
                                        
                                        <h3>'.get_string('preview','groupformation').'</h3>
                    
                                        <div class="col_100">'.    
//                                         '<h4 class="view_on_mobile">'.get_string('topics_question','groupformation').'</h4>'.
					
                                           '<p id="topicshead">'.get_string('knowledge_question','groupformation').'</p>
                                            <ul id="sortable_topics">
                                              <li class="topicLi" id="topicLi0"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>Thema 1</li>
                                              <li class="topicLi" id="topicLi1"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>Thema 2</li>
                                              <li class="topicLi" id="topicLi2"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>Thema 3</li>
                                            </ul>
                                        </div>
                                    </div> <!-- /col_50 --> 
                                </div>  <!-- /multi_field_wrapper-->
                                </div> <!-- Anchor-->
                                
                            </div> <!-- /.grid -->
                        </div> <!-- /.topicWrapper -->
                    </div>   <!--/col_100 -->
                </div>  <!-- /grid --> 
					');
                

			//add Groupsize Options
			$mform->addElement('html', '
                <div class="grid option_row">

                    <div class="col_33 ">
                        <h4 class="optional">Gruppen Einstellungen<span class="toolt" tooltip="Diese Option kann bei Gruppenbildung optimiert werden, nachdem die Frageb&ouml;gen ausgef&uuml;hlt wurden"></span></h4>
                    </div>

                    <div class="col_33" ><label><input type="radio" name="group_opt" id="group_opt_size" value="group_size" checked="checked" />
                                Max. Gruppengr&ouml;&szlig;e</label><input type="number" class="group_opt" id="group_size" min="0" max="100" value="0" /></div>
                    <div class="col_33"><label><input type="radio" name="group_opt" id="group_opt_numb" value="numb_of_groups"/>
                                Max. Gruppenanzahl</label><input type="number" class="group_opt" id="numb_of_groups"  min="0" max="100" value="0" disabled="disabled" /></div>
                </div> <!-- /grid -->
                ');
					

			// add Evaluation Options
			$mform->addElement('html', '
                
                <div class="grid option_row">

                    <div class="col_33">
                        <h4 class="required">Wie bewerten Sie die Arbeit?</h4>
                    </div>
                    <div class="col_66">
                        <select name="valuation" id="valuation">
                            <option value="grades">Noten</option>
                            <option value="points">Punkte</option>
                            <option value="justpass">Nur bestehen</option>
                            <option value="novaluation">keine Bewertung</option>
                        </select>
                    </div>

                </div> <!-- /grid -->
                ');
                
			
			
			
// 			$mform->addElement('html', '
// 					<div class="col_100">
//                         <h4 class="optional"><input name="js_knowledge" type="checkbox" value="1" id="id_js_knowledge">
//                     	'.get_string('knowledge_description','groupformation').'
// 						</h4>
// 					</div>');
// 			// Adding dynamic inputfields
// 			$mform->addElement('html', '
// 	        		<div class="knowledgeWrapper">
	    
//                         <p>'.get_string('knowledge_description_extended','groupformation').'</p>
	    
//                             <div class="grid">
//                             <div id="prk">
//                             <div class="multi_field_wrapper persist-area">
//                                 <div class="col_50">
//                                 <div id="" class="btn_wrap">
//                                     <label>
//                                         <button type="button" class="add_field" title="'.get_string('add_line','groupformation').'"></button>'.get_string('add_line','groupformation').'</label>
//                                 </div>
			
	    
// <!--                      Die Input Felder-->
	    
//                                         <div class="multi_fields">
//                                             <div class="multi_field" id="inputprk0">
//                                                 <input class="respwidth" type="text" name="knowledge[]" id="js_id_knowledge">
//                                                 <button type="button" class="remove_field" title="'.get_string('remove_line','groupformation').'"></button>
//                                             </div>
//                                             <div class="multi_field" id="inputprk1">
//                                                 <input class="respwidth" type="text" name="knowledge[]">
//                                                 <button type="button" class="remove_field" title="'.get_string('remove_line','groupformation').'"></button>
//                                             </div>
//                                             <div class="multi_field" id="inputprk2">
//                                                 <input class="respwidth" type="text" name="knowledge[]">
//                                                 <button type="button" class="remove_field" title="'.get_string('remove_line','groupformation').'"></button>
//                                             </div>
//                                         </div>
//                                     </div> <!-- /col_50 -->
	    
// <!--                      Die Vorschau      -->
//                                     <div class="col_50">
	    
//                                         <h3>'.get_string('preview','groupformation').'</h3>
	    
//                                             <div class="col_100">'.
// //                                                 '<h4 class="view_on_mobile">'.get_string('knowledge_question','groupformation').'</h4>'.
// 												'<table class="responsive-table">
//                                                     <colgroup width="" span="">
//                                                         <col class="firstCol">
//                                                         <col width="36%">
//                                                     </colgroup>
	    
//                                                     <thead>
//                                                       <tr>
//                                                           <th scope="col" class="">'.get_string('knowledge_question','groupformation').'</th>
//                                                         <th scope="col"><div class="legend">'.get_string('knowledge_scale','groupformation').'</div></th>
//                                                       </tr>
//                                                     </thead>
//                                                     <tbody id="preknowledges">
//                                                       <tr class="knowlRow" id="prkRow0">
//                                                         <th scope="row"><span id="prkRow0_span">Beispiel</span></th>
//                                                         <td data-title="'.get_string('knowledge_scale','groupformation').'" class="range"><span >0</span><input type="range" min="0" max="100" value="0" /><span>100</span></td>
//                                                       </tr>
//                                                     <tr class="knowlRow" id="prkRow1">
//                                                         <th scope="row"><span id="prkRow1_span">Beispiel</span></th>
//                                                         <td data-title="'.get_string('knowledge_scale','groupformation').'" class="range"><span >0</span><input type="range" min="0" max="100" value="0" /><span>100</span></td>
//                                                       </tr>
//                                                     <tr class="knowlRow" id="prkRow2">
//                                                         <th scope="row"><span id="prkRow2_span">Beispiel</span></th>
//                                                         <td data-title="'.get_string('knowledge_scale','groupformation').'" class="range"><span >0</span><input type="range" min="0" max="100" value="0" /><span>100</span></td>
//                                                       </tr>
	    
//                                                     </tbody>
//                                                   </table>
//                                             </div>
	    
//                                     </div> <!-- /col_50 -->
//                                 </div>  <!-- /multi_field_wrapper-->
//                                 </div> <!-- Anchor-->
	    
//                             </div> <!-- /.grid -->
//                         </div> <!-- /.knowledge -->
			
// 	        		');
			 
			 
				
		}
	}

