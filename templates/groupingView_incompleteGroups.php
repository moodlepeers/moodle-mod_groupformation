
<?php foreach($this->_ as $entry) { ?>
<div class="grid row_highlight">
    <div class="col_m_87-5"><?php echo $entry['groupname']; ?> - Anzahl Mitglieder: <b><?php echo $entry['groupsize']; ?></b> </div>
    <div class="col_m_12-5 bp_align_right-middle"><a href="<?php echo $entry['scrollTo_group']; ?>"><button class="gf_button gf_button_pill gf_button_tiny">scroll to</button></a></div>
</div>
<?php } ?>