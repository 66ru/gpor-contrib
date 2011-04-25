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
                    <td><a href=""><img src="/img/pobeda/ban1.jpg" alt=""></a></td>
                    <td class="sep">&nbsp;</td>
                    <td><a href=""><img src="/img/pobeda/ban2.jpg" alt=""></a></td>
                </tr>
            </table>
            <div class="orange-brd"></div>
            <? $this->widget('PlainCommentsWidget') ?>
        </div>
    </div>
</div>