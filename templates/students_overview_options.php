<div class="gf_pad_header_small"> <?php echo get_string('options', 'groupformation');?> </div>
<div class="gf_pad_content" style="">
    <div class="grid">
        <div class="col_m_100">
<!--            <form action="--><?php //echo htmlspecialchars ( $_SERVER ["PHP_SELF"] ) ; ?><!--" method="post" autocomplete="off">-->
<!--                <input type="hidden" name="questions" value="1"/>-->
                <input type="hidden" name="id" value="<?php echo $this->_['cmid'] ; ?>"/>
                <p><?php echo $this->_['buttons_infos'];  ?></p>
                <?php foreach($this->_['buttons'] as $button) { ?>
                    <button type="<?php echo $button['type']; ?>" name="<?php echo $button['name']; ?>" value="<?php echo $button['value']; ?>" class="gf_button gf_button_pill gf_button_small" <?php echo $button['state']; ?>><?php echo $button['text']; ?></button>
                <?php } ?>
<!--            </form>-->
        </div>
    </div>
</div>