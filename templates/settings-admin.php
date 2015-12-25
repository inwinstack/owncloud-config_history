<?php
    script('config_history', 'settings');
    style('config_history', 'style');
?>
<div class="section" id="configuration_history_section">
    <h2><?php p($l->t('Configuration History'))?></h2>
    <table id="configuration_history" class="grid">
        <tbody id="history_list" ></tbody>
    </table>
    <div id="loading_configuration" class="loading-configuration"></div>
    <input id="morehistory" type="button" value="<?php p($l->t('More'));?>">
    <input id="lesshistory" type="button" value="<?php p($l->t('Less'));?>">
    <font id="nomoremsg" ><?php p($l->t('No more history.')); ?></font>
    <font id="nomsg" ><?php p($l->t('There is no history.')); ?></font>
</div>
