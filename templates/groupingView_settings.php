<?php foreach($this->_['buttons'] as $button) { ?>

<button type="<?php echo $button['type']; ?>" name="<?php echo $button['name']; ?>" value="<?php echo $button['value']; ?>" class="gf_button gf_button_pill gf_button_small" <?php echo $button['state']; ?>><?php echo $button['text']; ?></button>

<?php } ?>

<!--<p>Statusanzeige "Gruppenbildung l&auml;uft..." mit %Zahl oder voraussichtlicher Endzeit</p>-->