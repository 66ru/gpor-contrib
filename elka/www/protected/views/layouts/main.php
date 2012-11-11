<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">

 <head>
	<title><?php echo $this->pageTitle; ?></title>
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
	<meta name="description" content="" />
	<meta property="og:title" content="<?php echo $this->pageTitle; ?>"/>
	<meta property="og:type" content="page"/>
	<meta property="og:image" content="/img/logo.png"/>
	<meta property="og:site_name" content="<?php echo Yii::app()->params['siteName']; ?>"/>
	<link href="http://t.66.ru/_66new/v2/misc/favicon.ico" type="image/x-icon" rel="icon" />
	<link href="http://t.66.ru/_66new/v2/misc/favicon.ico" type="image/x-icon" rel="shortcut icon" />

	
	<link rel="stylesheet" type="text/css" href="/css/reset.css" />
	<link href="/css/common.css" rel="stylesheet"  type="text/css" />
	<link href="/css/decorations.css" rel="stylesheet"  type="text/css" />
	<link href="/css/float.css" rel="stylesheet"  type="text/css" />
	<link href="/css/frame.css" rel="stylesheet"  type="text/css" />
	<link href="/css/grid.css" rel="stylesheet"  type="text/css" />
	<link href="/css/float.css" rel="stylesheet"  type="text/css" />
	<link href="/css/elka2013.css" rel="stylesheet"  type="text/css" />
	
	
	<!--[if lt IE 8]><link href="/css/float.ie.if_lt_IE_8.css" rel="stylesheet"  type="text/css" /><![endif]-->
	<!--[if lt IE 8]><link href="/css/common.ie.if_lt_IE_8.css" rel="stylesheet"  type="text/css" /><![endif]-->
	<!--[if lt IE 8]><link href="/css/decorations.ie.if_lt_IE_8.css" rel="stylesheet"  type="text/css" /><![endif]-->

	<script src="/js/jquery-1.7.1.min.js"  type="text/javascript"></script>

	<script src="/js/jquery.parallax.min.js"></script>
	<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery('.parallax-layer').parallax({
				mouseport: jQuery(".parallax-port"),
				yparallax: 0,
				xorigin: 1,
				decay: 0.66,
			});
		});
	
	</script>	
		
		

 </head>

 <body>
  <!--[if IE 8]><div id="ie_frame_reflow"><![endif]-->
  <div id="opera_frame_reflow"></div>
  <!--[if IE 8]></div><![endif]-->
  <div id="frame" class="context"  style="width: 1100px; margin: 0 auto;">
   <!--[if IE]><div id="ie_all"><![endif]-->
   <!--[if lt IE 8]><div id="ie_lt-8"><![endif]-->
   <!--[if IE 8]><div id="ie_8"><![endif]-->
  

	<div id="body-wrap" class="ie_layout">
		<div id="body" class="ie_layout">
			
			
			<div class="page-grid-wrap context">
			
				
					<div class="context elka13bg">
						<div class="grid-15 context">
							<div class="context">
								<div class="col-1 span-11">
									<div class="elka13Head">
										<div class="elka13Logo"></div>
										<div class="elka13Menu">
                                            <?php
                                            $this->widget('ElkaMenuWidget', array());
                                            ?>
										</div>
									</div>
									<div class="elka13MainPic">

										<div class="parallax-port">
											<ins class="elka13MainPicRight"></ins>
											<div class="parallax-layer" style="background: url(/img/3.png) center 0 no-repeat; width:900px; height:240px;"></div>
											<!--div class="parallax-layer" style="background: url(/img/4.png) center 0 no-repeat; width:920px; height:359px; top: -80px;"></div-->
											<div class="parallax-layer" style="background: url(/img/6.png) center 0 no-repeat; width:940px; height:240px;"></div>
											
											<div class="parallax-layer" style="background: url(/img/5.png) center 0 no-repeat; width:960px; height:240px;">
												<ins class="elka13MainPicLapkiLeft"></ins>
												<ins class="elka13MainPicLapkiRight"></ins>
												<div class="b-btn b-btn_color_orange b-btn_size_big b-btn_text-size_big">
													<ins class="b-btn__crn-left"></ins>
													<ins class="b-btn__crn-right"></ins>
													<a class="b-btn__link" href="<?php echo CHtml::normalizeUrl(array('/site/join')); ?>"></a>
													<span class="b-btn__text">стань дедом морозом</span>
												</div>																						
												
											</div>
										</div>										
										
										
									</div>
								</div>
								<div class="col-11 span-5">
									<div class="elka13HeadRight">
										<div class="countPresent countPresentReady">
											<b>48</b><ins class="countPresentReadyIco"></ins>
											<div class="countPresentSign">Подарков в офисе</div>
										</div>
										<div class="countPresent countPresentFindDed">
											<b>48</b><ins class="countPresentFindDedIco"></ins>
											<div class="countPresentSign">Писем ждут Деда Мороза</div>
										</div>										
										<p>Вы можете стать участником нашей акции и подарить детям-сиротам желанный новогодний подарок.</p>
										<p class="red">Нажмите кнопку и следуйте инструкцям.</p>
									</div>
								</div>
							</div>
						</div>
					</div>		
					
					
					<div class="context elka13BottomHeader">
						<div class="grid-15 context">
							<div class="context">
								<div class="col-1 span-10">
									<div class="elka13Donate">
										<div class="elka13Donate-label">Помочь материально:</div>
										<div class="elka13Donate-moneybag elka13Donate-moneybag-webmoney">41001344006673</div>
										<div class="elka13Donate-moneybag elka13Donate-moneybag-yandexmoney">41001344006673</div>
									</div>
								</div>
								<div class="col-11 span-5">
									<div class="socialBtnContainer">
										<a class="socialBtn socialBtnFb" href=""><ins></ins></a>
										<a class="socialBtn socialBtnVk" href=""><ins></ins></a>
										<a class="socialBtn socialBtnTw" href=""><ins></ins></a>
										<a class="socialBtn socialBtnOk" href=""><ins></ins></a>
									</div>
								</div>
							</div>								
						</div>
					</div>
					
					<div class="b-separator b-separator_size_10"></div>
					
					<div class="context">
						<div class="grid-15 context">
							<div class="context">
								<div class="col-1 span-10">
                                    <?php echo $content; ?>
								</div>
								<div class="col-11 span-5">
									
									<div class="b-container b-container_color_green">
										<div class="b-container__inner">
											
											<div class="b-separator b-separator_size_10"></div>
											
											<h2 class="b-header b-header_size_h2">Поддержка проекта</h2>
											
											<div class="b-announce b-announce_frame_white">
												<a style="background: url(http://s.66.ru/new66/a1/e3/6e/7e/a1e36e7e_resizedScaled_180to120.jpg) center center no-repeat; height: 60px;" class="b-announce__pic" href="/konkurs/2/list/3/"></a>
											</div>										
											<div class="b-separator b-separator_size_10"></div>
											<div class="b-announce b-announce_frame_white">
												<a style="background: url(http://s.66.ru/new66/a1/e3/6e/7e/a1e36e7e_resizedScaled_180to120.jpg) center center no-repeat; height: 60px;" class="b-announce__pic" href="/konkurs/2/list/3/"></a>
											</div>											
											<div class="b-separator b-separator_size_10"></div>
											<div class="b-announce b-announce_frame_white">
												<a style="background: url(http://s.66.ru/new66/a1/e3/6e/7e/a1e36e7e_resizedScaled_180to120.jpg) center center no-repeat; height: 60px;" class="b-announce__pic" href="/konkurs/2/list/3/"></a>
											</div>																						
											
											<div class="b-separator b-separator_size_20"></div>
											
										</div>
									</div>
									
									<div class="b-container b-container_color_grey">
										<div class="b-container__inner">
											<div class="b-separator b-separator_size_10"></div>
											<h2 class="b-header b-header_size_h2">Участники проекта</h2>								
											
											<div class="elka2013Users">
												<a class="b-link-user b-link-user_social_vk" href="">Kate K</a>&nbsp; &nbsp;
												<a class="b-link-user b-link-user_status_online b-link-user_gender_male" href="">Ирина Красных</a>&nbsp; &nbsp;
												<a class="b-link-user b-link-user_status_online b-link-user_gender_female " href="">BORODA ©</a>&nbsp; &nbsp;
												<a class="b-link-user  b-link-user_gender_male" href="">Елена Белая</a>&nbsp; &nbsp;
												<a class="b-link-user b-link-user_social_vk" href="">ka4aka</a>&nbsp; &nbsp;
												<a class="b-link-user b-link-user_status_online b-link-user_gender_male" href="">Дударев</a>&nbsp; &nbsp;
												<a class="b-link-user b-link-user_status_online b-link-user_gender_female " href="">La Koshka</a>&nbsp; &nbsp;
												<a class="b-link-user  b-link-user_gender_male" href="">CђronoS</a>&nbsp; &nbsp;											
											</div>
											
											<div class="b-separator b-separator_size_20"></div>
										</div>
									</div>
								
								
									
									
									
								</div>
							</div>								
						</div>
					</div>					
					
				
			</div>
		</div>
	</div>

   <div id="foot-wrap" class="ie_layout">
    <div id="foot" class="ie_layout">
		<div class="footer-content">
		Проект портала <a href="">66.ru</a></div>
		</div>
   </div>
   <!--[if IE 8]></div><![endif]-->
   <!--[if lt IE 8]></div><![endif]-->
   <!--[if IE]></div><![endif]-->
  </div>
 </body>
</html>