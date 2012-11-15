<div class="b-container">
    <div class="b-separator b-separator_size_5"></div>
    <?php if ($news) {
        ?>
    <h2 class="b-header b-header_size_h2 b-header_margin_left-10">Новости</h2>
    <div class="b-container context">
        <?php
        foreach ($news as $oneNews) {
            ?>
        <div class="b-announce b-announce_layout_pic-left">
            <a style="background: #ccc url(<?php echo $oneNews['_parsedSmallImage']; ?>) center center no-repeat; height: 78px;" class="b-announce__pic" href="<?php echo $oneNews['link']; ?>"></a>
            <div class="b-announce__text">
                <a target="_blank" class="b-announce__text__title" href="<?php echo $oneNews['link']; ?>"><?php echo $oneNews['title']; ?></a>
                <span class="b-announce__text__date"><?php echo DateUtils::_date($oneNews['postTime']); ?>
                    <?php if ($oneNews['commentsCount']) { echo '&nbsp;<a class="b-link b-link_layout_comments" title="комментарии" href="'.$oneNews['link'].'#comments">'.$oneNews['commentsCount'].'</a>'; } ?>
                </span>
                <p><?php echo $oneNews['annotation']; ?></p>
            </div>
        </div>
            <?php
        }
        ?>

        <?php echo Yii::app()->params['underNewsText']; ?>

        </div>
        <?php
    }
    ?>
</div>
