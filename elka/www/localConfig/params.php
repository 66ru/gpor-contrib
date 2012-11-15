<?php
defined('DS') or define('DS', DIRECTORY_SEPARATOR);
return array(
	'title' => ' &mdash; Елка желаний',
	'siteName' => 'Елка желаний',
	'appName' => 'elka',

	'yiiDebug' => true, // YII debug

	'domain' => 'testru.ru',

	/* email */
	'adminEmail' => 'stenlex@gmail.com', // this is used in error pages and in rss (webMaster)
	'senderEmail' => 'mailer@elka.ru',

	'phpPath' => ' ', // Path to php

	'filePath' => '/home/stepanoff/web/gpor-contrib/elka/files', // Путь до файлов с фидами

    'adminLogin' => 'admin',
    'adminPassword' => 'ltlvjhjp',

    'apiUrl' => 'http://api.new66.gpor.ru',
    'apiKey' => '386749b96b1ceb8001c15cc25e8d358b',
    'gporNewsSectionId' => '110',


    'announceText' => '<p>Портал <a href="http://66.ru" target="_blank">66.ru</a> присоединился к благотворительному проекту «Ёлка желаний», организованный добровольческим движением «<a href="http://dd66.ru" target="_blank">Дорогами добра</a>».</p>
     <p>Вы можете стать участником акции и подарить<br>детям-сиротам желанный новогодний подарок.</p>
     <p class="red">Нажмите кнопку и выберите подарок.</p>
    ',
    'socialButtonsText' => '
										<!--a class="socialBtn socialBtnFb" href=""><ins></ins></a-->
										<a class="socialBtn socialBtnVk" href="http://vk.com/elkazhelaniy2013" target="_blank"><ins></ins></a>
										<a class="socialBtn socialBtnTw" href="https://twitter.com/dorogami_dobra" target="_blank"><ins></ins></a>
										<!--a class="socialBtn socialBtnOk" href=""><ins></ins></a-->
										',
    'donateText' => '
										<div class="elka13Donate-label">Так же вы можете <a href="http://dd66.ru/donate" target="_blank" style="color: white;">помочь материально</a> движению &laquo;Дорогами добра&raquo; напрямую</div>
										<div class="elka13Donate-moneybag elka13Donate-moneybag-webmoney">41001344006673</div>
										<div class="elka13Donate-moneybag elka13Donate-moneybag-yandexmoney">41001344006673</div>
										',
    'partnersText' => '
                                            <h2 class="b-header b-header_size_h2">Организатор проекта</h2>
                                            <div class="b-announce b-announce_frame_white">
												<a target="_blank" style="background: url(/img/dd_ng_logo.png) center center no-repeat; height: 151px;" class="b-announce__pic" href="http://dd66.ru/elka-zhelanii-2013"></a>
                                            </div>
											',
    'footerText' => 'По вопросам обращайтесь по телефону: (343) 311-71-20',
    'underNewsText' => ' ',

);
