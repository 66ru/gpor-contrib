<?php
require_once(LIB_PATH.DS.'xmlrpc-3.0.0.beta'.DS.'xmlrpc.inc');

abstract class NewsHelper
{
    const PER_PAGE = 10;

    static function getNews($id = null, $noInId = null)
    {
		$cacheKey = md5(serialize(array(
			'page' => $_GET['page'],
			'id' => $id,
			'noInId' => $noInId,
		)));
		$cachedNews = Yii::app()->cache->get($cacheKey.'_news');
		$cachedNewsCount = Yii::app()->cache->get($cacheKey.'_newsCount');
		if ($cachedNews && $cachedNewsCount)
			return(
				array(
					'totalCount' => $cachedNewsCount,
					'news' => $cachedNews,
				)
			);

        $p1= 'News';

        $newsSection = new stdClass();
        $newsSection->type = 'integer';
        $newsSection->field = 'sectionId';
        $newsSection->value = Yii::app()->params['newsSection'];

        $newsId = false;
		if($id)
        {
            $newsId = new stdClass();
            $newsId->value = array($id);
            $newsId->type = 'array';
            $newsId->field = 'inId';
        }

        if($noInId)
        {
            $newsId = new stdClass();
            $newsId->value = array($noInId);
            $newsId->type = 'array';
            $newsId->field = 'notId';
        }

        $p2 = array();

        if (!empty($newsId))
			$p2[] = $newsId;

		$p2[]= $newsSection;

        $p3 = array(
            'id',
            'simpletitle',
            'content',
            'authorId',
            'comment',
            'annotation',
            'image',
            'toBlogCount',
            'containPhoto',
            'containAudio',
            'containVideo',
            'infograph',
            'commentsCount',
        );

        $p4 = new stdClass();

        if(!$id)
        {
            $p4->limit = self::PER_PAGE;

            if(isset($_GET['page']))
                $p4->offset = ((int) $_GET['page'] - 1) * self::PER_PAGE;
        }
        else
        {
            $p4->limit = 1;            
        }
        $p4 = php_xmlrpc_encode($p4);

        $resp = XMLRPCHelper::sendMessage('news.listNews', $p1, $p2, $p3, $p4);
        $resp2 = XMLRPCHelper::sendMessage('news.countNews', $p1, $p2);

		if(!empty($resp))
        {
            foreach($resp as $newsid=>&$news)
            {
                $news['addTags'] = self::tagParser($news['comment']);
                $news['title'] = $news['simpletitle'];
            }
        }

		if (!empty($resp))
			Yii::app()->cache->set($cacheKey.'_news', $resp, 50*60);
		if (!empty($resp2['newsCount']))
			Yii::app()->cache->set($cacheKey.'_newsCount', $resp2['newsCount'], 50*60);

		return(
            array(
                'totalCount' => $resp2['newsCount'],
                'news' => $resp,
            )
        );
    }
    static function tagParser($text)
    {
        $addTags = '';
        $tagsList = array('veteran');

        foreach($tagsList as $tag)
        {
            $matches = '';
            preg_match('/\['.$tag.'\](.*?)\[\/'.$tag.'\]/usix',$text,$matches);
            if($matches)
                $addTags[$tag] = $matches[1];
        }

        return $addTags;
    }
}
?>