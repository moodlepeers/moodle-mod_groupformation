<?php foreach($this->_ as $entry) { ?>

<div class="grid bottom_stripe">
    <div class="col_s_50"><?php echo get_string('name_by_group', 'groupformation');?>
        <b><?php echo $entry['groupname']; ?></b></div>
    <div class="col_s_25"><?php echo get_string('quality', 'groupformation');?>
        <b><?php echo ($entry['groupquallity']!=0)?$entry['groupquallity']:"-"; ?></b>
    <span class="toolt" tooltip="<?php echo get_string('quality_info', 'groupformation');?>"></span></div>
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
    <div class="col_s_100 gf_group_links">

        <?php foreach ($entry['group_members'] as $user) { ?>
            <a href="<?php echo $user['link']; ?>"><?php echo $user['name']; ?></a>
        <?php } ?>

    </div>
</div>
<?php } ?>