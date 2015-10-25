<div class="gf_settings_pad">
	<div class="gf_pad_header"><?php echo get_string('export', 'groupformation');?></div>
	<div class="gf_pad_content">
		<p><?php echo $this->_['export_description']; ?></p>
		<p>
			<?php if ($this->_['export_button']):?>
				<button class="gf_button gf_button_pill gf_button_small" disabled>
		    		<?php echo get_string('export', 'groupformation');?>
		   		</button>
			<?php else:?>
		    	<a href="<?php echo $this->_['export_url']; ?>" target="_blank">
				<span class="gf_button gf_button_pill gf_button_small">
		    		<?php echo get_string('export', 'groupformation');?>
		   		</span></a>
			<?php endif;?>
		</p>
	</div>
	<div class="gf_pad_header"><?php echo get_string('import', 'groupformation');?></div>
	<div class="gf_pad_content">
		<p><?php echo $this->_['import_description']; ?></p>
		<p><?php echo $this->_['import_form'];?></p>
	</div>
</div>