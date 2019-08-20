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
 * Wrapper view template
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

?>

<div id="js-content" style="display:none;">

    <?php echo $this->_['teacherinfo'];?>

    <div class="gf_settings_pad">
        <div class="gf_pad_header">
            <?php echo get_string('scenario_description', 'groupformation');?>
            <span class="required"></span>
        </div>
        <div class="js_errors" id="szenario_error">
            <p></p>
        </div>
        <div id="szenarioradios">
            <div class="grid gf_grid_m_minus">
                <div class="col_m_33">
                    <input type="radio" name="js_scenario" id="project" value="project"  />
                    <label class="col_m_100 szenarioLabel" id="label_project" for="project" >
                        <div class="sz_header">
                            <?php echo get_string('scenario_projectteams', 'groupformation');?>
                        </div>
                        <p>
                            <small>
                                <b>
                                    <i>
                                        <?php echo get_string('scenario_usage_header', 'groupformation');?>
                                    </i>
                                </b>
                                <br>
                                <?php echo get_string('scenario_projectteams_short', 'groupformation');?>
                            </small>
                        </p>
                            <p>
                                <small>
                                    <?php echo get_string('scenario_projectteams_description',
                                            'groupformation');?>
                                </small>
                            </p>
                    </label>
                </div>
                <?php if (!$this->_['mathprepcourse']):?>
                <div class="col_m_33">
                    <input type="radio" name="js_scenario" id="homework" value="homework" />
                    <label class="col_m_100 szenarioLabel" id="label_homework" for="homework" >
                        <div class="sz_header">
                            <?php echo get_string('scenario_homeworkgroups', 'groupformation');?>
                        </div>
                        <p>
                            <small>
                                <b>
                                    <i>
                                        <?php echo get_string('scenario_usage_header', 'groupformation');?>
                                    </i>
                                </b>
                                <br>
                                <?php echo get_string('scenario_homeworkgroups_short', 'groupformation');?>
                            </small>
                        </p>
                        <p>
                            <small>
                                <?php echo get_string('scenario_homeworkgroups_description',
                                        'groupformation');?>
                            </small>
                        </p>
                    </label>
                </div>
                <div class="col_m_33">
                    <input type="radio" name="js_scenario" id="presentation" value="presentation" />
                    <label class="col_m_100 szenarioLabel" for="presentation">
                        <div class="sz_header">
                            <?php echo get_string('scenario_presentationgroups', 'groupformation');?>
                        </div>
                        <p>
                            <small>
                                <b>
                                    <i>
                                        <?php echo get_string('scenario_usage_header_presentation',
                                                'groupformation');?>
                                    </i>
                                </b>
                                <br>
                                <?php echo get_string('scenario_presentationgroups_short',
                                        'groupformation');?>
                            </small>
                        </p>
                        <p>
                            <small>
                                <?php echo get_string('scenario_presentationgroups_description',
                                        'groupformation');?>
                            </small>
                        </p>
                    </label>
                </div>
                <?php endif;?>
            </div>
        </div>
    </div>
    <div id="js_scenarioWrapper">
        <div class="gf_settings_pad">
        </div>

        <!-- Start:one of bin section -->
        <div class="gf_settings_pad">
            <div class="gf_pad_header">
                <label class="gf_label" for="id_js_oneofbin">
                    <input type="checkbox" id="id_js_oneofbin" name="chbOneOfBin" value="wantOneOfBin" />
                    <?php echo get_string('oneOfBinQuestion', 'groupformation'); ?>
                </label>
            </div>
            <div class="gf_pad_content" id="js_oneOfBinWrapper" style="display:none;">
                <p class="oob_in_preview" id="oneOfBinInfoText"><h5><?php echo get_string('choose_oob_answers', 'groupformation'); ?><span class="required"></span></h5>
                <input type="text" class="respwidth oob_in_preview" id="js_oob_question" placeholder="<?php echo get_string('add_oob_question', 'groupformation'); ?>" style="width: 80%" />
                </p>

                <div class="grid">
                    <div id="oob">
                        <div class="multi_field_wrapper persist-area">
                            <div class="col_m_50">
                                <h5>
                                    <?php echo get_string('answers', 'groupformation'); ?>
                                    <span class="required"></span> </h5>


                                <div class="multi_fields oob_in_preview">
                                    <div class="multi_field" id="inputoob0">
                                        <input class="respwidth js_oneofbinInput" type="text">
                                        <button type="button" class="remove_field gf_button gf_button_circle gf_button_small">

                                        </button>
                                    </div>
                                    <div class="multi_field" id="inputoob1">
                                        <input class="respwidth js_oneofbinInput" type="text">
                                        <button type="button" class="remove_field gf_button gf_button_circle gf_button_small">

                                        </button>
                                    </div>
                                    <div class="multi_field" id="inputoob2">
                                        <input class="respwidth js_oneofbinInput lastInput" type="text" placeholder="
                                            <?php echo get_string('add_line', 'groupformation');?>
                                        ">
                                        <button type="button"
                                                class="remove_field gf_button gf_button_circle gf_button_small"
                                                disabled="disabled">

                                        </button>
                                    </div>
                                </div>
                                <p><div id="oob_multiselect_box oob_in_preview">
                                        <h5><?php echo get_string('choose_type', 'groupformation'); ?></h5>
                                <p><?php echo get_string('decide_multiselect', 'groupformation'); ?></p>
                                <label class="gf_label" for="id_js_binquestionmultiselect">
                                    <input type="checkbox" id="id_js_binquestionmultiselect"  value="wantMultiselect" />
                                    <?php echo get_string('multiselect', 'groupformation'); ?>
                                </label>
                            </div>
                            </p><p>
                                <div id="gf_oneOfBinImportanceDiv  oob_in_preview">
                                    <h5>
                                        <?php echo get_string('importance', 'groupformation'); ?>
                                    <span class="required"></span></h5>
                                    <p><?php echo get_string('choose_oob_importance', 'groupformation'); ?></p>
                                    <p id="gf_one_of_bin_Importance"><?php echo get_string('oob_selected_value', 'groupformation'); ?></p>
                                <div>

                                        <span>
                                            0
                                        </span>
                                        <input type="range" id="id_js_oneofbinimportance" list="gfOneOfBinImpValues" min="0" max="10" value="0" />
                                        <span>
                                            10
                                        </span>
                                        <datalist id="gfOneOfBinImpValues">
                                            <option value="0" label="0%">
                                            <option value="1">
                                            <option value="2">
                                            <option value="3">
                                            <option value="4">
                                            <option value="5" label="50%">
                                            <option value="6">
                                            <option value="7">
                                            <option value="8">
                                            <option value="9">
                                            <option value="10" label="100%">
                                        </datalist>
                                    </div>
                                </div></p><p>
                                <div id="gf_oneOfBinRelation  oob_in_preview">
                                    <h5><?php echo get_string('relation', 'groupformation'); ?><span class="required"></span></h5>
                                    <p><?php echo get_string('choose_oob_relation', 'groupformation'); ?></p>
                                <p id="js_oobrelselval"><?php echo get_string('oob_selected_value', 'groupformation'); ?></p>
                                <select id="id_js_oneofbinrelation">
                                        <option value="homogenous">
                                            <?php echo get_string('homogenous', 'groupformation'); ?>
                                        </option>
                                        <option value="heterogenous">
                                            <?php echo get_string('heterogenous', 'groupformation'); ?>
                                        </option>

                                    </select>
                                </div></p>
                            </div>
                            <div class="col_m_50">

                                <h5>
                                    <?php echo get_string('preview', 'groupformation');?>
                                </h5>
                                <div class="col_m_100" id="oobPreview">
                                    <table class="responsive-table">
                                        <colgroup>
                                            <col class="firstCol">
                                            <col width="36%">
                                        </colgroup>

                                        <thead>
                                        <tr>
                                            <th scope="col">
                                                <?php echo get_string('choose_answer', 'groupformation'); ?>
                                            </th>
                                            <th></th>
                                        </tr>
                                        </thead>
                                        <tbody id="oneofbinpreview">
                                        <tr class="knowlRow">
                                            <th scope="row">
                                                <p id="oobquestionPreview">
                                                    <?php echo get_string('no_oob_question', 'groupformation');?>
                                                </p>
                                            </th>

                                            <td class="range">
                                                <select id="oobpreviewdd">
                                                    <option class="oobRow" id="oobRow0">
                                                        <?php echo get_string('knowledge_dummy',
                                                            'groupformation');?> 1
                                                    </option>
                                                    <option class="oobRow" id="oobRow1">
                                                        <?php echo get_string('knowledge_dummy',
                                                            'groupformation');?> 2
                                                    </option>
                                                    <option class="oobRow" id="oobRow2">
                                                        <?php echo get_string('knowledge_dummy',
                                                            'groupformation');?> 3
                                                    </option>
                                                </select>

                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="col_m_100" id="oobMultiPreview" style="display:none;">
                                    <table class="responsive-table">
                                        <colgroup>
                                            <col class="firstCol">
                                            <col width="36%">
                                        </colgroup>

                                        <thead>
                                        <tr>
                                            <th scope="col">
                                                <?php echo get_string('choose_answers', 'groupformation'); ?>
                                            </th>
                                            <th></th>
                                        </tr>
                                        </thead>
                                        <tbody id="oneofbinpreview">
                                        <tr class="knowlRow">
                                            <th id="oobquestionPreviewMulti" scope="row">
                                                <?php echo get_string('no_oob_question', 'groupformation');?>
                                            </th>

                                            <td class="range">
                                                <select multiple class="oobpreviewddMulti" id="oobpreviewddMulti">
                                                    <option class="oobRowMulti" id="oobRow0Multi">
                                                        <?php echo get_string('knowledge_dummy',
                                                            'groupformation');?> 1
                                                    </option>
                                                    <option class="oobRowMulti" id="oobRow1Multi">
                                                        <?php echo get_string('knowledge_dummy',
                                                            'groupformation');?> 2
                                                    </option>
                                                    <option class="oobRowMulti" id="oobRow2Multi">
                                                        <?php echo get_string('knowledge_dummy',
                                                            'groupformation');?> 3
                                                    </option>
                                                </select>

                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End: one of bin section -->

        <div class="gf_settings_pad">
            <div class="gf_pad_header">
                <label class="gf_label" for="id_js_knowledge">
                    <input type="checkbox" id="id_js_knowledge" name="chbKnowledge" value="wantKnowledge" />
                    <?php echo get_string('knowledge_description', 'groupformation');?>
                </label>
                <span class="optional">
                </span>
                <span class="toolt" tooltip="<?php echo get_string('knowledge_help', 'groupformation');?>">
                </span>
            </div>
            <div class="gf_pad_content" id="js_knowledgeWrapper">
                <p id="knowledgeInfo">

                </p>
                <p id="knowledgeInfoProject" style="display:none;">
                    <?php echo get_string('knowledge_info_project', 'groupformation');?>
                </p>
                <p id="knowledgeInfoHomework" style="display:none;">
                    <?php echo get_string('knowledge_info_homework', 'groupformation');?>
                </p>
                <p id="knowledgeInfoPresentation" style="display:none;">
                    <?php echo get_string('knowledge_info_presentation', 'groupformation');?>
                </p>
                <p id="stringAddInput" style="display:none;">
                    <?php echo get_string('add_line', 'groupformation');?>
                </p>
                <p id="language" style="display:none;">
                    <?php echo get_string('language', 'groupformation');?>
                </p>
                <div class="grid">
                    <div id="prk">
                        <div class="multi_field_wrapper persist-area">
                            <div class="col_m_50">
                                <h5>
                                    <?php echo get_string('input', 'groupformation');?>
                                </h5>
                                <div class="multi_fields">
                                    <div class="multi_field" id="inputprk0">
                                        <input class="respwidth js_preknowledgeInput" type="text">
                                        <button type="button" class="remove_field gf_button gf_button_circle gf_button_small">

                                        </button>
                                    </div>
                                    <div class="multi_field" id="inputprk1">
                                        <input class="respwidth js_preknowledgeInput" type="text">
                                        <button type="button" class="remove_field gf_button gf_button_circle gf_button_small">

                                        </button>
                                    </div>
                                    <div class="multi_field" id="inputprk2">
                                        <input class="respwidth js_preknowledgeInput lastInput" type="text" placeholder="
                                            <?php echo get_string('add_line', 'groupformation');?>
                                        ">
                                        <button type="button"
                                                class="remove_field gf_button gf_button_circle gf_button_small"
                                                disabled="disabled">

                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col_m_50">
                                <h5>
                                    <?php echo get_string('preview', 'groupformation');?>
                                </h5>
                                <div class="col_m_100">
                                    <table class="responsive-table">
                                        <colgroup>
                                            <col class="firstCol">
                                            <col width="36%">
                                        </colgroup>

                                        <thead>
                                            <tr>
                                                <th scope="col">
                                                    <?php echo get_string('knowledge_question',
                                                            'groupformation');?>
                                                </th>
                                                <th scope="col">
                                                    <div class="legend">
                                                        <?php echo get_string('knowledge_scale',
                                                                'groupformation');?>
                                                    </div>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody id="preknowledges">
                                            <tr class="knowlRow" id="prkRow0">
                                                <th scope="row">
                                                    <?php echo get_string('knowledge_dummy',
                                                            'groupformation');?>  1
                                                </th>

                                                <td data-title="<?php echo get_string('knowledge_scale',
                                                        'groupformation');?>" class="range">
                                                    <span >
                                                        0
                                                    </span>
                                                    <input type="range" min="0" max="100" value="0" />
                                                    <span>
                                                        100
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr class="knowlRow" id="prkRow1">
                                                <th scope="row">
                                                    <?php echo get_string('knowledge_dummy',
                                                            'groupformation');?>  2
                                                </th>
                                                <td data-title="<?php echo get_string('knowledge_scale',
                                                        'groupformation');?>" class="range">
                                                    <span >
                                                        0
                                                    </span>
                                                    <input type="range" min="0" max="100" value="0" />
                                                    <span>
                                                        100
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr class="knowlRow" id="prkRow2">
                                                <th scope="row">
                                                    <?php echo get_string('knowledge_dummy',
                                                            'groupformation');?>  3
                                                </th>
                                                <td data-title="<?php echo get_string('knowledge_scale',
                                                        'groupformation');?>"
                                                    class="range">
                                                    <span>
                                                        0
                                                    </span>
                                                    <input type="range" min="0" max="100" value="0" />
                                                    <span>
                                                        100
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div> <!-- /col_50 -->
                        </div>  <!-- /multi_field_wrapper-->
                    </div> <!-- Anchor-->
                </div> <!-- /.grid -->
            </div>
        </div>

        <?php if (!$this->_['mathprepcourse']): ?>
            <div class="gf_settings_pad">

                <div class="gf_pad_header">
                    <label class="gf_label" for="id_js_topics">
                        <input type="checkbox" id="id_js_topics" name="chbTopics" value="wantTopics">
                        <?php echo get_string('topics_description', 'groupformation');?>
                    </label>
                    <span id="topicsStateLabel" class="optional">

                    </span>
                    <span class="toolt" tooltip="<?php echo get_string('topics_help', 'groupformation');?>">
                    </span>
                </div>

                <div class="gf_pad_content" id="js_topicsWrapper">

                    <p><?php echo get_string('topics_description_extended', 'groupformation');?></p>

                    <div class="grid">
                        <div id="tpc">
                            <div class="multi_field_wrapper persist-area">
                                <div class="col_m_50">
                                    <h5>
                                        <?php echo get_string('input', 'groupformation');?>
                                    </h5>
                                    <div class="multi_fields">
                                        <div class="multi_field" id="inputtpc0">
                                            <input class="respwidth js_topicInput" type="text">
                                            <button type="button" class="remove_field gf_button gf_button_circle gf_button_small">

                                            </button>
                                        </div>
                                        <div class="multi_field" id="inputtpc1">
                                            <input class="respwidth js_topicInput" type="text">
                                            <button type="button" class="remove_field gf_button gf_button_circle gf_button_small">

                                            </button>
                                        </div>
                                        <div class="multi_field" id="inputtpc2">
                                            <input class="respwidth js_topicInput lastInput" type="text" placeholder="
                                                <?php echo get_string('add_line', 'groupformation');?>
                                            ">
                                            <button type="button"
                                                    class="remove_field gf_button gf_button_circle gf_button_small"
                                                    disabled="disabled">

                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col_m_50">
                                    <h5>
                                        <?php echo get_string('preview', 'groupformation');?>
                                    </h5>
                                    <div class="col_m_100">
                                        <p id="topicshead">
                                            <?php echo get_string('topics_question', 'groupformation');?>
                                        </p>
                                        <span id="topicsDummy" style="display:none;">
                                            <?php echo get_string('topics_dummy', 'groupformation');?>
                                            </span>
                                        <ul class="sortable_topics" id="previewTopics">
                                            <li class="topicLi" id="tpcRow0" class="">
                                                <span class="ui-icon ui-icon-arrowthick-2-n-s">

                                                </span>
                                                <?php echo get_string('topics_dummy', 'groupformation');?> 1
                                            </li>
                                            <li class="topicLi" id="tpcRow1" class="">
                                                <span class="ui-icon ui-icon-arrowthick-2-n-s">

                                                </span>
                                                <?php echo get_string('topics_dummy', 'groupformation');?> 2
                                            </li>
                                            <li class="topicLi" id="tpcRow2" class="">
                                                <span class="ui-icon ui-icon-arrowthick-2-n-s">

                                                </span>
                                                <?php echo get_string('topics_dummy', 'groupformation');?> 3
                                            </li>
                                        </ul>
                                    </div>
                                </div> <!-- /col_50 -->
                            </div>  <!-- /multi_field_wrapper-->
                        </div> <!-- Anchor-->

                    </div> <!-- /.grid -->
                </div> <!-- /.topicWrapper -->

            </div>
        <?php endif;?>

        <div class="gf_settings_pad">

            <div class="gf_pad_header">
                <?php echo get_string('groupoption_description', 'groupformation');?>
                <span class="required">

                </span>
                <span class="toolt" tooltip="
                    <?php echo get_string('groupoption_help', 'groupformation');?>
                ">
            </span>
            </div>
            <div class="js_errors" id="maxmembers_error">
                <p></p>
            </div>
            <div class="js_errors" id="maxgroups_error">
                <p></p>
            </div>
            <div class="settings_info" id="groupSettingsInfo">
                <p>
                    <?php echo get_string('groupSettingsInfo', 'groupformation');?>
                </p>
            </div>
            <div class="gf_pad_content">
                <p>
                    <span id="studentsInCourse">
                        <b>
                            <?php echo $this->_['count'];?>
                        </b>
                    </span>
                    <?php echo get_string('students_enrolled_info', 'groupformation');?>
                </p>
                <div class="grid">
                    <div class="col_m_50">
                        <label>
                            <input type="radio" name="group_opt" id="group_opt_size" value="group_size" checked="checked" />
                            <?php echo get_string('maxmembers', 'groupformation');?>
                        </label>
                        <input type="number" class="group_opt" id="group_size" min="0" value="0" />
                    </div>
                    <?php if (!$this->_['mathprepcourse']): ?>
                        <div class="col_m_50">
                            <label>
                                <input type="radio" name="group_opt" id="group_opt_numb" value="numb_of_groups"/>
                                <?php echo get_string('maxgroups', 'groupformation');?>
                            </label>
                            <input type="number"
                                   class="group_opt"
                                   id="numb_of_groups"
                                   min="0" max="100"
                                   value="0"
                                   disabled="disabled" />
                        </div>
                    <?php endif;?>
                </div>
            </div>

        </div>

        <div class="gf_settings_pad">
            <div class="gf_pad_header">
                <?php echo get_string('groupname', 'groupformation');?>
                <span class="optional">

                </span>
                <span class="toolt" tooltip="
                    <?php echo get_string('groupname_help', 'groupformation');?>
                ">
                </span>
            </div>
            <div class="gf_pad_content">
                <input type="text" class="respwidth" id="js_groupname" />
            </div>
        </div>

        <div class="gf_settings_pad">
            <div class="gf_pad_header">
                <?php echo get_string('evaluationmethod_description', 'groupformation');?>
                <span class="required">

                </span>
            </div>
            <div class="js_errors" id="evaluationmethod_error">
                <p></p>
            </div>
            <div class="js_errors" id="maxpoints_error">
                <p></p>
            </div>
            <div class="gf_pad_content">
                <select id="js_evaluationmethod">
                    <option value="chooseM">
                        <?php echo get_string('choose_evaluationmethod', 'groupformation');?>
                    </option>
                    <option value="grades">
                        <?php echo get_string('grades', 'groupformation');?></option>
                    <option value="points">
                        <?php echo get_string('points', 'groupformation');?></option>
                    <option value="justpass">
                        <?php echo get_string('justpass', 'groupformation');?></option>
                    <option value="novaluation">
                        <?php echo get_string('noevaluation', 'groupformation');?></option>
                </select>
                <span id="max_points_wrapper">
                    <input type="number" id="max_points"  min="0" max="100" value="100" />
                    <span class="toolt" tooltip="
                        <?php echo get_string('evaluation_point_info', 'groupformation');?>
                    ">
                    </span>
                </span>
            </div>
        </div>

        <?php if (!$this->_['mathprepcourse']): ?>
            <div class="gf_pad_header">
                <label class="gf_label" for="id_js_onlyactivestudents">
                    <input type="checkbox" id="id_js_onlyactivestudents" name="chbOnlyactivestudents" value="onlyactivestudents">
                        <?php echo get_string('onlyactivestudents_description', 'groupformation');?>
                </label>
                <span id="onlyactivestudentsStateLabel" class="optional">
                </span>
                <span class="toolt" tooltip="
                    <?php echo get_string('groupoption_onlyactivestudents', 'groupformation');?>
                ">
                </span>
            </div>
            <div class="gf_pad_header">
                <label class="gf_label" for="id_js_emailnotifications">
                    <input disabled type="checkbox">
                    <!-- id="id_js_emailnotifications" name="chbEmailnotifications" value="wantEmailnotifications"> !-->
                    <?php echo get_string('emailnotifications_description', 'groupformation');?>
                </label>
                <span id="emailnotificationsStateLabel" class="optional">

                </span>
            </div>
        <?php endif;?>
        <div class="gf_pad_header">
            <label class="gf_label" for="id_js_allanswersrequired">
                <input type="checkbox" id="id_js_allanswersrequired" name="chbAllanswersrequired" value="allanswersrequired">
                <?php echo get_string('allanswersrequired_description', 'groupformation');?>
            </label>
            <span id="allanswersrequiredStateLabel" class="optional">
            </span>
            <span class="toolt" tooltip="
                <?php echo get_string('groupoption_allanswersrequired', 'groupformation');?>">
            </span>
        </div>
    </div>
</div id="js-content">
