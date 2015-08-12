<div class="grid">
	<div class="col_m_100 bp_align_left-middle">
        <span><?php echo $this->_['info_teacher']; ?></span></br>
        <span><i></i></span>
    </div>
    <div class="col_m_66 bp_align_left-middle">
        <span><b>Startzeit: </b><?php echo $this->_['analysis_time_start']; ?></span></br>
        <span><b>Endzeit: </b><?php echo $this->_['analysis_time_end']; ?></span></br></br>
        <span><i><?php echo $this->_['analysis_status_info']; ?></i></span>
        <span><i></i></span>
    </div>

    <div class="col_m_33 bp_align_right-middle">
        <!--<span class="toolt" tooltip="Aktivität stoppen, um Gruppen zu bilden." style="margin-right:0.7em;"></span>-->
        <button type="<?php echo $this->_['button']['type']; ?>" name="<?php echo $this->_['button']['name']; ?>" value="<?php echo $this->_['button']['value']; ?>" class="gf_button gf_button_pill gf_button_small"<?php echo $this->_['button']['state']; ?> ><?php echo $this->_['button']['text']; ?></button>
    </div>
</div>
