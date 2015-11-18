<div class="gf_settings_pad">
    <div class="gf_pad_header"><?php echo get_string('your_group', 'groupformation');?> - <?php echo $this->_['group_name']; ?>
    </div>
    <div class="gf_pad_content">
        <p><?php echo $this->_['group_info_contact']; ?></p>
        <p><?php echo $this->_['group_info']; ?></p>

        <?php foreach($this->_['members'] as $row) { ?>
            <p><b><?php echo $row; ?></b></p>
        <?php } ?>

    </div>
</div>