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
		
        $client = new xmlrpc_client(Yii::app()->params['apiUrl']);
        $client->return_type = 'phpvals';

        $message = new xmlrpcmsg("news.listNews");
        $message2 = new xmlrpcmsg("news.countNews");
        $p0 = new xmlrpcval(Yii::app()->params['apiKey'], 'string');
        $message->addparam($p0);
        $message2->addparam($p0);

        $p1= 'News';
        $p1 = php_xmlrpc_encode($p1);
        $message->addparam($p1);
        $message2->addparam($p1);

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

        if (!empty($newsId))
			$p2[] = $newsId;

		$p2[]= $newsSection;
        $p2 = php_xmlrpc_encode($p2);
        $message->addparam($p2);
        $message2->addparam($p2);

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
        $p3 = php_xmlrpc_encode($p3);
        $message->addparam($p3);

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
        $message->addparam($p4);

		list($resp, $resp2) = $client->send(array($message,$message2), 0, 'http11');

        if(!empty($resp->val))
        {
            foreach($resp->val as $newsid=>&$news)
            {
                $news['addTags'] = self::tagParser($news['comment']);
                $news['title'] = $news['simpletitle'];
            }
        }

		Yii::app()->cache->set($cacheKey.'_news', $resp->val, 50*60);
		Yii::app()->cache->set($cacheKey.'_newsCount', $resp2->val['newsCount'], 50*60);

		return(
            array(
                'totalCount' => $resp2->val['newsCount'],
                'news' => $resp->val,
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