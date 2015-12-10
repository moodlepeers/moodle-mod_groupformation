<div class="gf_pad_header_small">
    <?php echo $this->_['questionnaire_answer_stats']; ?>
</div>
<div class="gf_pad_content">
    <?php foreach($this->_['survey_states'] as $row) { ?>
        <div class="grid row_highlight">
            <div class="col_m_100"><?php echo $row; ?></div>
        </div>
    <?php } ?>
</div>