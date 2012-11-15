<?php
class GenerateNewsCommand extends CronCommand
{
    protected function getFileName () {
        return FILES_PATH . DS . 'elka_news.json';
    }

	public function run()
	{
        $apiUrl = Yii::app()->params['apiUrl'];
        $apiKey = Yii::app()->params['apiKey'];
        $xmlRpc = new XmlRpc($apiUrl, $apiKey);
        $xmlRpc->setApiCommand('news.listNews');

        $news = array();

        $params = array(
            'News',
            array(
                array('type' => 'number', 'value' => Yii::app()->params['gporNewsSectionId'], 'field' => 'sectionId'),
            ),
            array('id','simpletitle','title','postTime','annotation', 'commentsCount', 'titlelink', 'fulltitlelink', 'link', 'imageurl', 'sectionId', 'containPhoto', 'containVideo', 'containAudio', 'infograph', 'havePoll','newMainImageURL', 'online'),
            array(
                'limit' => 10
            ),
        );
        if ($xmlRpc->send($params)) {
            $res = $xmlRpc->getResponseValue();
            $maxPostTime = strtotime(date('Y-01-01 00:00:00', time()));
            foreach ($res as $item) {
                if ($item['postTime'] < $maxPostTime )
                    continue;
                $news[] = $item;
            }
        }

        file_put_contents($this->getFileName(), CJSON::encode($news));

    }
}
?>
