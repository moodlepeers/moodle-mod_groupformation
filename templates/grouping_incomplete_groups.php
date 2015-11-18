
<?php foreach($this->_ as $entry) { ?>
<div class="grid row_highlight">
    <div class="col_m_75"><?php echo $entry['groupname']; ?> - <?php echo get_string('number_member', 'groupformation')?> <b><?php echo $entry['groupsize']; ?></b> </div>
    
    
    
    <div class="col_m_25 bp_align_right-middle">
	    <a href="<?php echo $entry['grouplink'][0]; ?>">
	      <?php if (!$entry['grouplink'][1]=='disabled'){ ?> 
		    <span class="gf_button gf_button_pill gf_button_tiny">
		    <?php echo get_string('to_groupview', 'groupformation');?>
		    </span>
		  <?php } else {?>
		  	<button class="gf_button gf_button_pill gf_button_tiny" disabled>
		    <?php echo get_string('to_groupview', 'groupformation');?>
		    </button>
		  <?php } ?>
	    </a>
    </div>
    
</div>
<?php } ?>