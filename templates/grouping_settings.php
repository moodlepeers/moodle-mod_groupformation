
<div class="grid">
    <div class="col_m_87-5"><?php echo get_string('are', 'groupformation');?> <b><?php echo $this->_['student_count']; ?></b> <?php echo ($this->_['student_count']==1)? get_string('students_grouping_single', 'groupformation'):get_string('students_grouping_single', 'groupformation');?></div>
</div>

<?php foreach($this->_['buttons'] as $button) { ?>

<button type="<?php echo $button['type']; ?>" name="<?php echo $button['name']; ?>" value="<?php echo $button['value']; ?>" class="gf_button gf_button_pill gf_button_small" <?php echo $button['state']; ?>><?php echo $button['text']; ?></button>

<?php } ?>

<div>
	<div style="<?php if($this->_['status'][1] == 0) { echo 'opacity:0.5;';} else {echo 'opacity:1.0;';} ?>margin-left: 4px; <?php if($this->_['status'][1] == 1) { echo 'color: red;';}?>"> <i><?php echo $this->_['status'][0];?> </i></div>
</div>

<!--<p>Statusanzeige "Gruppenbildung l&auml;uft..." mit %Zahl oder voraussichtlicher Endzeit</p>-->