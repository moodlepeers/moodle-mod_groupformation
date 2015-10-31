<div class="gf_settings_pad">
	<div class="gf_pad_header"><?php echo get_string('import', 'groupformation');?></div>
	<div class="gf_pad_content">
		<?php if ($this->_['successful']):?>
		<p><?php echo get_string('successful_import','groupformation')?></p>
		<p>
			<a href="<?php echo $this->_['import_export_url']; ?>"> 
				<span class="gf_button gf_button_pill gf_button_small">
		    		<?php echo get_string('tab_overview','groupformation');?>
		   		</span>
		   	</a>
		</p>
		<?php else:?>
		<p><?php echo get_string('failed_import','groupformation');?></p>
		<p>
			<a href="<?php echo $this->_['import_form']; ?>"> 
				<span class="gf_button gf_button_pill gf_button_small">
			    	<?php echo get_string('back');?>
			   	</span>
		   	</a>
		</p>
		<?php endif;?>
	</div>
</div>