<ul class="news__list">
<?php
    foreach($news as $k=>$v)
    {
        print('
        <li class="news__item">
        <div class="news__item-pic">
        '.(isset($v['_parsedSmallImage'])?
            '<a href="'.CHtml::normalizeUrl(array('/site/news','id'=>$v['id'])).'"><img src="'.$v['_parsedSmallImage'].'" alt="'.$v['title'].'" /></a>'
            :'').'
        </div>
        <div class="news__item-text">
            <h3 class="news__item_title"><a href="'.CHtml::normalizeUrl(array('/site/news','id'=>$v['id'])).'">'.$v['title'].'</a></h3>');
        if(isset($v['containPhoto']) || isset($v['containAudio']) || isset($v['containVideo']) || isset($v['infograph']))
        {
            print('<div class="inline-block">');

            if(isset($v['containPhoto']))
                print('<i class="has-photo">Фото</i>&nbsp;');
            if(isset($v['containAudio']))
                print('<i class="has-audio">Аудио</i>&nbsp;');
            if(isset($v['containVideo']))
                print('<i class="has-video">Видео</i>&nbsp;');
            if(isset($v['infograph']))
                print('<i class="has-infograph">Инфографика</i>&nbsp;');

            print('</div>');
        }
        if(isset($v['commentsCount']))
            print('&nbsp;&nbsp;<noindex><a class="comments-count" href="'.CHtml::normalizeUrl(array('/site/news','id'=>$v['id'])).'#comments">'.$v['commentsCount'].'<i class="invisible"> комментари'.StringHelper::plural($v['commentsCount'],'й', 'я', 'ев').'</i></a></noindex>');

        if(isset($v['addTags']['veteran']))
                print '<p class="news__item_content">'.$v['addTags']['veteran'].'</p>';

        print('</div></li>');
    }
?>
</ul>
