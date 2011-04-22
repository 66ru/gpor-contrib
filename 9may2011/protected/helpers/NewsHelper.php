<?php
require_once(LIB_PATH.DS.'xmlrpc-3.0.0.beta'.DS.'xmlrpc.inc');

abstract class NewsHelper
{
    static function getNews($id = null)
    {
        
        $client = new xmlrpc_client(Yii::app()->params['apiUrl']);
        $client->return_type = 'phpvals';

        $message = new xmlrpcmsg("news.listNews");
        $p0 = new xmlrpcval(Yii::app()->params['apiKey'], 'string');
        $message->addparam($p0);

        $p1= 'News';
        $p1 = php_xmlrpc_encode($p1);
        $message->addparam($p1);

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

        $p2 = array($newsId, $newsSection);
        $p2 = php_xmlrpc_encode($p2);
        $message->addparam($p2);

        $p3 = array('id','title','content','authorId','comment','annotation','image','toBlogCount');
        $p3 = php_xmlrpc_encode($p3);
        $message->addparam($p3);

        $p4 = new stdClass();
        $p4->limit = 20;
        $p4 = php_xmlrpc_encode($p4);
        $message->addparam($p4);

        $resp = $client->send($message, 0, 'http11');

        if(!empty($resp->val))
        {
            foreach($resp->val as $k=>$v)
            {
                $resp->val[$k]['addTags'] = self::tagParser($v['comment']);
            }
        }

        //var_dump($resp->val);

        return($resp->val);

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