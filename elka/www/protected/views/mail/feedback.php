<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
	<style type="text/css">
		* { font-size: 14px; font-family: arial, sans-serif; line-height: 1.4em; }
		html, body { height: 100%; }
		a { color: #037DD3; }
		h1 { font-size: 28px; margin-bottom: 8px; font-weight: normal; }
		h2 { font-size: 21px; margin-bottom: 8px; font-weight: normal; }
		h3 { font-size: 18px; margin-bottom: 8px; font-weight: normal; }
		h4 { font-size: 14px; margin-bottom: 8px; }
		p { font-size: 14px; margin-bottom: 10px; }
		u { text-decoration: none; }
		.objectlink{ color:#7c7c7c; font-size:10px; }
		.redlink{ color:#ff0000; }
	</style>
</head>
<body>

<p>Новое письмо пришло на Ёлку желаний</p>

<p><strong>Тема</strong>: <?php echo $theme; ?></p>
<p><strong>Имя</strong>: <?php echo $name; ?></p>
<p><strong>Телефон</strong>: <?php echo $phone; ?></p>
<p><strong>Комментарий</strong>:<br><?php echo nl2br($comment); ?></p>


</body>
</html>
