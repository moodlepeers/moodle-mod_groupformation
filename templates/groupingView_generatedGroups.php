


<?php foreach($this->_ as $entry) { ?>

<div class="grid bottom_stripe">
    <div class="col_s_50">Name: <b><?php echo $entry['groupname']; ?></b></div>
    <div class="col_s_25">Gruppenqualit&auml;t: <b><?php echo $entry['groupquallity']; ?></b>
    <span class="toolt" tooltip="Der Gruppen-Performance-Index (GPI) gibt die Qualität der gebildeten Gruppe wieder und ist ein Wert zwischen 0 und 1. Je größer der Wert, deste besser ist die gebildete Gruppe."></span></div>
    <div class="col_s_25 bp_align_right-middle">
	    <a href="<?php echo $entry['grouplink'][0]; ?>">
	      <?php if (!$entry['grouplink'][1]=='disabled'){ ?> 
		    <span class="gf_button gf_button_pill gf_button_tiny">
		    zur Moodle Gruppenansicht
		    </span>
		  <?php } else {?>
		  	<button class="gf_button gf_button_pill gf_button_tiny" disabled>
		    zur Moodle Gruppenansicht
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

