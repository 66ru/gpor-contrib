<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
          "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
 <head>
  <title></title>
  <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
  <link rel="pingback" href="http://api.new66.gpor.ru/" />
  <link href="/favicon.ico" type="image/x-icon" rel="icon" />
  <link href="/favicon.ico" type="image/x-icon" rel="shortcut icon" />




<script type="text/javascript" src="/js/common/jquery-1.5.min.js"></script>
<script type="text/javascript" src="/js/common/jquery.popup.js"></script>
<script type="text/javascript" src="/js/common/rocon.js"></script>
<script type="text/javascript" src="/js/selections.js"></script>
<script type="text/javascript" src="/js/commands.js"></script>
<script type="text/javascript" src="/js/buildForm.js"></script>
<script type="text/javascript" src="/js/config.js"></script>

<script type="text/javascript">
    $(document).ready(function () {
       $(this).commentForm();
       $("#linkOpenFormGreeting").click(function(){
            $("#leaveGreetingFormRow").toggle();
           return false;
       });
    });
</script>


<link rel="stylesheet" type="text/css" href="/css/common/styles.css">
<link type="text/css" rel="stylesheet" href="/css/common/!reset.css" />
<link type="text/css" rel="stylesheet" href="/css/common/commentsForm.css" />




<link href="/css/common/packed.css" rel="stylesheet" type="text/css" />

<!--[if lt IE 8]>
<link href="http://new66.gpor.ru/static/common/css/buttons.ie.if_lt_IE_8.css" rel="stylesheet" type="text/css" />
<![endif]-->
<!--[if lt IE 8]>
<link href="http://new66.gpor.ru/static/common/css/common.ie.if_lt_IE_8.css" rel="stylesheet" type="text/css" />
<![endif]-->
<!--[if lt IE 8]>
<link href="http://new66.gpor.ru/static/common/css/decorations.ie.if_lt_IE_8.css" rel="stylesheet" type="text/css" />
<![endif]-->
<!--[if lt IE 8]>
<link href="http://new66.gpor.ru/static/common/css/g-png.ie6.if_lt_IE_8.css" rel="stylesheet" type="text/css" />
<![endif]-->
<!--[if lt IE 8]>
<link href="http://new66.gpor.ru/static/common/css/head.ie.if_lt_IE_8.css" rel="stylesheet" type="text/css" />
<![endif]-->



<link href="/css/common/pobedaStyle.css" rel="stylesheet" type="text/css" />


</head>

<body class=" page-fixed-right">
    <!--[if IE 8]><div id="ie_frame_reflow"><![endif]-->
    <div id="opera_frame_reflow"></div>
    <!--[if IE 8]></div><![endif]-->
    <div id="frame" class="context">
    <!--[if IE]><div id="ie_all"><![endif]-->
    <!--[if lt IE 7]><div id="ie_lt-7"><![endif]-->
    <!--[if IE 7]><div id="ie_7"><![endif]-->
    <!--[if lt IE 8]><div id="ie_lt-8"><![endif]-->
    <!--[if IE 8]><div id="ie_8"><![endif]-->
    <!--[if IE 8]><style>.out-head-wrap-content {top : -6px;}</style><![endif]-->
    <!--[if IE 8]><style>.out-head-wrap-content {top : -6px;}</style><![endif]-->

   <div id="out-head-wrap">
	    <div id="out-head-wrap-content" class="out-head-wrap-content"></div>
		<div id="head-wrap" class="ie_layout">
            <!--[if lt IE 7]>
            <div class="ie_max-width_left_frame"></div>
            <div class="ie_max-width_right_frame"></div>
            <![endif]-->
		<div id="head" class="ie_layout">
             <div class="head-slogan">Современный портал Екатеринбурга <a title="Современный портал Екатеринбурга" target="_blank" href="66.ru"><img class="head-slogan-logo66" src="/img/pobeda/head-66logo.gif" alt="66.ru" /></a></i> представляет</div>
		</div>
	   </div>
	</div>






	<div id="body-wrap" class="ie_layout">
        <div id="teaser-place">
		<!--[if lt IE 7]>
		<div class="ie_max-width_left_frame"></div>
		<div class="ie_max-width_right_frame"></div>
		<![endif]-->
            <div id="body" class="ie_layout">
                <div class="grid-2-cols context">
                    <div class="grid-2-cols__col1">
                        <img class="znachok__pic" src="/img/pobeda/znachok.gif" alt="" />
                    </div>
                    <div class="grid-2-cols__col2">
                        <div class="teaser-place__congratulations">
                            <p>Нашим проектом мы хотим поблагодарить ветеранов Великой<br />отечественной войны, рассказать и сохранить историю каждого<br />подвига совершённого нашим великим народом.</p>
                            <h2>Вечная слава победителям!</h2>
                            <h1><img src="/img/pobeda/winh1.png" alt="66 Лет Победы" /></h1>
                            <h3>В Великой Отечественной войне 1941-1945</h3>
                        </div>
                    </div>
                </div>
            </div>
		</div>


        <div id="wrapper-place">
		<!--[if lt IE 7]>
		<div class="ie_max-width_left_frame"></div>
		<div class="ie_max-width_right_frame"></div>
		<![endif]-->
            <div id="main" class="ie_layout">

                <?php echo $content; ?>

            </div>
        </div>


	</div>
    <div id="foot-wrap" class="ie_layout">
    <!--[if lt IE 7]>
        <div class="ie_max-width_left_frame"></div>
        <div class="ie_max-width_right_frame"></div>
    <![endif]-->

        <div id="foot" class="ie_layout">
		    <div class="content-block">
    		    <div class="v5-logo">
                     <a href="http://www.66.ru/">
                      <img title="Современный портал Екатеринбурга" alt="66.ru" src="http://t.66.ru/common/img/v5-foot-logo.png">
                      <span>
                       Портал «66.ru» &mdash; Современный портал Екатеринбурга.
                      </span>
                     </a>
                     <span class="v5-footer-links"></span>
                     <ul class="v5-menu"><!--
                      --><li>
                          <a href="http://www.66.ru/about/">О проекте</a>
                         </li><!--
                      --><li>
                          <a href="http://www.66.ru/advert/">Размещение рекламы</a>
                         </li><!--
                      --><li>
                          <a href="http://www.66.ru/agreement/">Пользовательское соглашение</a>

                         </li><!--
                      --><li class="v5-last">
                          <a href="http://support.66.ru">Техподдержка</a>
                         </li><!--
                               --></ul>
                </div>
                <div class="v5-counters"></div>
    		</div>
    	</div>
    </div>
   <!--[if IE 8]></div><![endif]-->
   <!--[if lt IE 8]></div><![endif]-->
   <!--[if IE 7]></div><![endif]-->
   <!--[if lt IE 7]></div><![endif]-->
   <!--[if IE]></div><![endif]-->

 </body>

</html>


