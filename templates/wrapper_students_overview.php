<div class="gf_settings_pad">
    <div class="gf_pad_header"><?php echo get_string('group_building', 'groupformation');?> - <?php echo $this->_['student_overview_title']; ?>
    </div>
    <div class="gf_pad_content">
        <p><?php echo $this->_['student_overview_groupformation_info']; ?></p>
        <?php foreach($this->_['student_overview_groupformation_status'] as $row) { ?>
            <p><b><?php echo $row; ?></b></p>
        <?php } ?>
    </div>

    <?php echo $this->_['student_overview_survey_state_temp']; ?>

    <?php echo $this->_['student_overview_survey_options']; ?>


</div>