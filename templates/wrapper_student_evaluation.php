<div class="gf_settings_pad">
    <div class="gf_pad_header"><?php echo get_string('evaluation', 'groupformation');?></div>
    <div class="gf_pad_content">
        <?php if ($this->_['eval_text']):?>
        <?php echo get_string('no_evaluation_ready','groupformation'); ?>
        <?php endif; ?>
        <div id="json-content" style="display:none;"><?php echo $this->_['json_content'];?>
        </div>
    </div>
</div>