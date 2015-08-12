<div class="gf_settings_pad">
    <div class="gf_pad_header">Gruppenbildung - <?php echo $this->_['student_overview_title']; ?>
    </div>
    <div class="gf_pad_content">
        <p><?php echo $this->_['student_overview_groupformation_info']; ?></p>
        <p style="margin-top: 2em;"><b><?php echo $this->_['student_overview_groupformation_status']; ?></b></p>
    </div>

    <?php echo $this->_['student_overview_survey_state_temp']; ?>

    <div class="gf_pad_content" style="padding-top:0;">
        <div class="grid">
            <div class="col_m_100">
                <form action="<?php echo htmlspecialchars ( $_SERVER ["PHP_SELF"] ) ; ?>" method="post" autocomplete="off">
                    <input type="hidden" name="questions" value="1"/>
                    <input type="hidden" name="id" value="<?php echo $this->_['cmid'] ; ?>"/>
                    <?php foreach($this->_['buttons_infos'] as $row) { ?>
                        <p><?php echo $row; ?></p>
                    <?php } ?>
                    <?php foreach($this->_['buttons'] as $button) { ?>
                        <button type="<?php echo $button['type']; ?>" name="<?php echo $button['name']; ?>" value="<?php echo $button['value']; ?>" class="gf_button gf_button_pill gf_button_small" <?php echo $button['state']; ?>><?php echo $button['text']; ?></button>
                    <?php } ?>
                </form>
            </div>
        </div>
    </div>

</div>