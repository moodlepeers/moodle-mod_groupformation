
<div class="grid">
    <div class="col_m_87-5">Es gibt <b><?php echo $this->_['student_count'].(($this->_['student_count']==1)?" Student":" Studenten"); ?></b> zur Gruppenbildung.</div>
</div>
<?php foreach($this->_['buttons'] as $button) { ?>

<button type="<?php echo $button['type']; ?>" name="<?php echo $button['name']; ?>" value="<?php echo $button['value']; ?>" class="gf_button gf_button_pill gf_button_small" <?php echo $button['state']; ?>><?php echo $button['text']; ?></button>

<?php } ?>

<!--<p>Statusanzeige "Gruppenbildung l&auml;uft..." mit %Zahl oder voraussichtlicher Endzeit</p>-->