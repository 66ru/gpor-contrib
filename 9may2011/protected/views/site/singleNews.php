        <div id="wrapper-place">
		<!--[if lt IE 7]>
		<div class="ie_max-width_left_frame"></div>
		<div class="ie_max-width_right_frame"></div>
		<![endif]-->
            <div id="main" class="ie_layout">
                <div class="grid-2-cols context">
                    <div class="grid-2-cols__col1">
                        <div class="grid-2-cols__col1__pad">

                            <!--этот оберточный див нужен только в Inner.html-->
                            <div class="grid-inner-left-pad">

                                <div class="orange-brd"></div>

<!-- стандартная новость с 66 с коментариями-->
<div class="news_single-view">
    <div class="news_single_heading">
    <h1 class="news_single_heading_title readable "><?php echo $news['title'] ?></h1>
    <div class="news_single_heading-drop-down">
        <nowrap>
            <i class="news_single_subj-name"><?php if(isset($news['addTags']['veteran'])) echo $news['addTags']['veteran'] ?></i>
        </nowrap>
    </div>

    </div>
     <div class="news_single_content content context">
        <p class="news_single_content-expert"><?php if(isset($news['_parsedImage'])) echo '<img alt="'.$news['title'].'" src="'.$news['_parsedImage'].'" />'; ?><?php if(isset($news['annotation'])) echo $news['annotation']; ?></p>
        <?php echo $news['_parsedContent'] ?>
     </div>
</div>

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
                <form action="http://<?php if(Yii::app()->user->getId()) echo Yii::app()->params['outerHostName'].'/user/'.Yii::app()->user->getId().'/blog/add/'; else echo Yii::app()->params['outerHostName'].'/login' ?>" method="post"><table class=""><tr><td><input type="hidden" name="newslink" value="http://<?php echo Yii::app()->params['hostName'].'/news/'.$news['id']; ?>"/><input type="hidden" name="newstitle" value="<?php echo $news['title']; ?>"/><input type="hidden" name="newsannotation" value="<?php echo $news['annotation']; ?>"/><span class="b-placing_into_blog66__container"><input class="b-placing_into_blog66" type="submit" alt="в блог на <?php echo Yii::app()->params['siteName']; ?>" title="в блог на <?php echo Yii::app()->params['siteName']; ?>" value="<?php if(isset($news['toBlogCount'])) echo $news['toBlogCount']; else echo 0;?>"></span></table></form>
		    </div>
            <div class="socialnetworks-posting-item" style="margin-top: 1px;">
                <div id="mail_like"></div>
            </div>

            <div class="socialnetworks-posting-item" style="margin-top: 1px;">
                <div id="lj_like" src="/img/sb/lj.gif" href="http://www.livejournal.com/update.bml?subject='+encodeURIComponent(" simpletitle="<?php echo $news['title']; ?>" outerhostname="http://<?php echo Yii::app()->params['hostName'].'/news/'.$news['id']; ?>"></div>
            </div>
	    </div>
    </noindex>
</div>

                            </div>
                        </div>
                    </div>
                    <div class="grid-2-cols__col2">

                        <div class="grid-2-cols__col2__pad">

                            <table class="banner-grid">
                                <tr>
                                    <td><a href=""><img src="/img/pobeda/ban1.jpg" alt=""></a></td>
                                    <td class="sep">&nbsp;</td>
                                    <td><a href=""><img src="/img/pobeda/ban2.jpg" alt=""></a></td>
                                </tr>
                            </table>

                            <h3 class="news__list__title">Другие истории наших ветеранов</h3>
                            <ul class="news__list">
                                <li class="news__item">
                                    <div class="news__item-pic">
                                        <a href=""><img src="/img/pobeda/1.jpg" alt="" /></a>
                                    </div>
                                    <div class="news__item-text">
                                        <h3 class="news__item_title"><a href="">Когда началась война, мне шел восьмой год</a></h3>
                                        <p class="news__item_content">Федоров Михаил Степанович</p>
                                    </div>
                                </li>
                                <li class="news__item">
                                    <div class="news__item-pic">
                                        <a href=""><img src="/img/pobeda/2.jpg" alt="" /></a>
                                    </div>
                                    <div class="news__item-text">
                                        <h3 class="news__item_title"><a href="">Со дня объявления войны ,был в ПВО Москвы,  как командир пулеметного расчета</a></h3>
                                        <p class="news__item_content">Селихов Борис Васильевич</p>
                                    </div>
                                </li>
                                <li class="news__item">
                                    <div class="news__item-pic">
                                        <a href=""><img src="/img/pobeda/1.jpg" alt="" /></a>
                                    </div>
                                    <div class="news__item-text">
                                        <h3 class="news__item_title"><a href="">Когда началась война, мне шел восьмой год</a></h3>
                                        <p class="news__item_content">Федоров Михаил Степанович</p>
                                    </div>
                                </li>
                                <li class="news__item">
                                    <div class="news__item-pic">
                                        <a href=""><img src="/img/pobeda/2.jpg" alt="" /></a>
                                    </div>
                                    <div class="news__item-text">
                                        <h3 class="news__item_title"><a href="">Со дня объявления войны ,был в ПВО Москвы,  как командир пулеметного расчета</a>&nbsp;<span class="inline-block"><i class="has-photo">Фото</i></span>&nbsp;<noindex><a href="" class="comments-count">19<i class="invisible"> комментариев</i></a></noindex>
                                        <p class="news__item_content">Селихов Борис Васильевич</p>
                                    </div>
                                </li>
                                <li class="news__item">
                                    <div class="news__item-pic">
                                        <a href=""><img src="/img/pobeda/1.jpg" alt="" /></a>
                                    </div>
                                    <div class="news__item-text">
                                        <h3 class="news__item_title"><a href="">Когда началась война, мне шел восьмой год</a></h3>
                                        <p class="news__item_content">Федоров Михаил Степанович</p>
                                    </div>
                                </li>
                                <li class="news__item">
                                    <div class="news__item-pic">
                                        <a href=""><img src="/img/pobeda/2.jpg" alt="" /></a>
                                    </div>
                                    <div class="news__item-text">
                                        <h3 class="news__item_title"><a href="">Со дня объявления войны ,был в ПВО Москвы,  как командир пулеметного расчета</a></h3>
                                        <p class="news__item_content">Селихов Борис Васильевич</p>
                                    </div>
                                </li>
                            </ul>


                            <div class="orange-brd"></div>


                            <!-- стандартный пейджер с 66-->
                            <div class="news_section-news-list__pagination">
                                <noindex>
                                    <span class="yiiPagerHeader">Страницы</span>
                                    <ul class="yiiPager" id="yw0">
                                        <li class="previous"><span class="ctrl left">← Ctrl </span><a href="">предыдущая</a></li>
                                        <li class="next"><a href="">следующая</a><span class="ctrl right">Ctrl →</span></li>
                                    </ul>
                                    <ul class="yiiPagerPages">
                                        <li class="page selected rc3"><a href="">1</a></li>
                                        <li class="page"><a href="">2</a></li>
                                        <li class="page"><a href="">3</a></li>
                                        <li class="page"><a href="">4</a></li>
                                        <li class="page"><a href="">5</a></li>
                                        <li class="page"><a href="">6</a></li>
                                        <li class="page"><a href="">7</a></li>
                                        <li class="page"><a href="">8</a></li>
                                        <li class="page"><a href="">9</a></li>
                                        <li class="page"><a href="">…</a></li>
                                    </ul>
                                </noindex>
                            </div>




                        </div>


                    </div>
                </div>
            </div>
        </div>


   