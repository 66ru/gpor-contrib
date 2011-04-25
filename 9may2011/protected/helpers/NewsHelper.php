<?php
require_once(LIB_PATH.DS.'xmlrpc-3.0.0.beta'.DS.'xmlrpc.inc');

abstract class NewsHelper
{
    const PER_PAGE = 10;

    static function getNews($id = null, $noInId = null)
    {
        
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

        $p2 = array($newsId, $newsSection);
        $p2 = php_xmlrpc_encode($p2);
        $message->addparam($p2);
        $message2->addparam($p2);

        $p3 = array(
            'id',
            'title',
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

        $resp = $client->send($message, 0, 'http11');
        $resp2 = $client->send($message2, 0, 'http11');

        if(!empty($resp->val))
        {
            foreach($resp->val as $k=>$v)
            {
                $resp->val[$k]['addTags'] = self::tagParser($v['comment']);
            }
        }

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