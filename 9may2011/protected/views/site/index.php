<div class="grid-2-cols context">
    <div class="grid-2-cols__col1">
        <div class="grid-2-cols__col1__pad">
            <h3 class="news__list__title">Истории наших ветеранов</h3>
            <? $this->widget('NewsListWidget', array('news'=>$news)) ?>
            <div class="orange-brd"></div>
            <? $this->widget('LinkPager', array('pages'=>$pages)) ?>
        </div>
    </div>
    <div class="grid-2-cols__col2">
        <div class="grid-2-cols__col2__pad">
            <table class="banner-grid">
                <tr>
                    <td><a href="http://www.pobediteli.ru"><img src="/img/pobeda/ban1.jpg" alt="Победители. Солдаты великой войны"></a></td>
                    <td class="sep">&nbsp;</td>
                    <td><a href="http://www.ekburg.ru"><img src="/img/pobeda/ban2.jpg" alt="Официальный портал Екатеринбурга"></a></td>
                </tr>
            </table>

            <!-- стандартный блок соцкнопок с новостей 66 -->
            <div class="news_single-view-social">
                <script src="http://connect.facebook.net/ru_RU/all.js#xfbml=1"></script>
                <script src="http://platform.twitter.com/widgets.js" type="text/javascript"></script>
                <script src="http://userapi.com/js/api/openapi.js?18" type="text/javascript"></script>
                <script charset="windows-1251" src="http://vkontakte.ru/js/api/share.js?10" type="text/javascript"></script>
                <script type="text/javascript">VK.init({apiId: <?php echo (Yii::app()->params['vkontakteApiId']); ?>, onlyWidgets: true});</script>
                <noindex>
                    <div class="socialnetworks-posting context">
                        <div class="socialnetworks-posting-item j-facebook">

                        </div>
                        <div class="socialnetworks-posting-item" style="width: 100px;">
                            <div id="tw_like" data="<?php echo $news['title']; ?>"></div>
                        </div>
                        <div class="socialnetworks-posting-item">
                            <div id="vk_like"></div>
                        </div>
                        <div class="socialnetworks-posting-item">
                            <form action="http://<?php if(Yii::app()->user->getId()) echo Yii::app()->params['outerHostName'].'/user/'.Yii::app()->user->getId().'/blog/add/'; else echo Yii::app()->params['outerHostName'].'/login' ?>" method="post"><table class=""><tr><td><input type="hidden" name="newslink" value="<?CHtml::normalizeUrl(array('/site/news','id'=>$news['id']))?>"/><input type="hidden" name="newstitle" value="<?php echo $news['title']; ?>"/><input type="hidden" name="newsannotation" value="<?php echo $news['annotation']; ?>"/><span class="b-placing_into_blog66__container"><input class="b-placing_into_blog66" type="submit" alt="в блог на <?php echo Yii::app()->params['siteName']; ?>" title="в блог на <?php echo Yii::app()->params['siteName']; ?>" value="<?php if(isset($news['toBlogCount'])) echo $news['toBlogCount']; else echo 0;?>"></span></table></form>
                        </div>
                        <div class="socialnetworks-posting-item" style="margin-top: 1px;">
                            <div id="mail_like"></div>
                        </div>

                        <div class="socialnetworks-posting-item" style="margin-top: 1px;">
                            <div id="lj_like" src="/img/sb/lj.gif" href="http://www.livejournal.com/update.bml?subject='+encodeURIComponent(" simpletitle="<?php echo $news['title']; ?>" outerhostname="<?CHtml::normalizeUrl(array('/site/news','id'=>$news['id']))?>"></div>
                        </div>
                    </div>
                </noindex>
            </div>

            <div class="orange-brd"></div>
            <? $this->widget('PlainCommentsWidget') ?>
        </div>
    </div>
</div>