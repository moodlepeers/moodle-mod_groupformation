<div class="grid row_highlight">
    <div class="col_m_100"><?php echo get_string('are', 'groupformation');?>
        <b><?php echo $this->_['statistics_enrolled']; ?></b>
        <?php echo ($this->_['statistics_enrolled']==1) ? get_string('students_available_single', 'groupformation') : get_string('students_available_multiple', 'groupformation'); ?></div>
</div>
<div class="grid row_highlight">
    <div class="col_m_100"><b><?php echo $this->_['statistics_processed']; ?></b>
        <?php echo ($this->_['statistics_processed']==1) ? get_string('students_answered_single', 'groupformation') : get_string('students_answered_multiple', 'groupformation'); ?></div>
</div>
<div class="grid row_highlight">
    <div class="col_m_100"><b><?php echo $this->_['statistics_submited']; ?></b>
        <?php echo ($this->_['statistics_submited']==1) ? get_string('students_commited_single', 'groupformation') : get_string('students_commited_multiple', 'groupformation'); ?></div>
</div>
<div class="grid row_highlight">
	<div class="col_m_100"><b><?php echo $this->_['statistics_submited_incomplete']; ?></b>
        <?php echo get_string('commited_not_completed', 'groupformation');?></div>
</div>
<div class="grid row_highlight">
    <div class="col_m_100"><?php echo get_string('are_now', 'groupformation');?> <b>
            <?php echo $this->_['statistics_submited_complete']; ?></b>
        <?php echo get_string('completed_questionaire', 'groupformation');?></div>
</div>

