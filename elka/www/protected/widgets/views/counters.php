<div class="elka13HeadRight">
    <div class="countPresent countPresentReady">
        <b><?php echo $checked; ?></b><ins class="countPresentReadyIco"></ins>
        <div class="countPresentSign"><?php echo StringUtils::pluralEnd($checked, array('Подарок', 'Подарка', 'Подарков')); ?> в офисе</div>
    </div>
    <div class="countPresent countPresentFindDed">
        <b><?php echo $none; ?></b><ins class="countPresentFindDedIco"></ins>
        <div class="countPresentSign"><?php echo StringUtils::pluralEnd($none, array('Письмо', 'Письма', 'Писем')); ?> ждут Деда Мороза</div>
    </div>
    <?php echo Yii::app()->params['announceText']; ?>
</div>
