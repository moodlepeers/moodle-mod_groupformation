<div class="gf_settings_pad">
	<div class="gf_pad_header"><?php echo get_string('import', 'groupformation');?></div>
	<div class="gf_pad_content">
		<p><?php echo get_string('import_form_description','groupformation');?>
		<p><?php echo ($this->_['file_error'])?'<div class="beta_version_warning"><p>'.get_string('file_error','groupformation').'</p></div>':'' ?></p>