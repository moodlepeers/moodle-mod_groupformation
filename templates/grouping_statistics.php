
<p><?php echo get_string('kohort_index', 'groupformation');?> <b>
<?php echo (is_null($this->_['performance']))?"-":$this->_['performance'];?>
</b>
<span class="toolt" tooltip="<?php echo get_string('kohort_index_info', 'groupformation');?>"></span></p>
<p><?php echo get_string('number_of_groups', 'groupformation');?> <b><?php echo $this->_['numbOfGroups'];?></b></p>
<p><?php echo get_string('max_group_size', 'groupformation');?> <b><?php echo $this->_['maxSize'];?></b></p>