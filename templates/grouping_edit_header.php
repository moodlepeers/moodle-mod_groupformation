<?php ?>


<div class="gf_pad_select_membs">
    <div class="grid">
        <div class="col_m_75">
            <div id="selected_membs" >
                <div id="ux_hint_1"><i><?php echo get_string('select_info', 'groupformation'); ?></i></div>
                <ul class="selected_memb_list">

                </ul>
            </div>
            <div class="selected_memb_info"><span id="memb_counter">0 </span><?php echo get_string('students_selected', 'groupformation'); ?></div>
            <div class="selected_memb_info"><span id="unselect_all"><?php echo get_string('unselect_all', 'groupformation'); ?></span></div>
        </div>
        <div class="col_m_25 bp_align_right-middle">
            <?php foreach ($this->_['buttons'] as $button) { ?>

                <button id="<?php echo $button['id']; ?>" type="<?php echo $button['type']; ?>" name="<?php echo $button['name']; ?>"
                        value="<?php echo $button['value']; ?>"
                        class="gf_button gf_button_pill gf_button_small" <?php echo $button['state']; ?>><?php echo $button['text']; ?></button>

            <?php } ?>

        </div>
    </div>
</div>