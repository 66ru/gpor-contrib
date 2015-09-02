<?php
$apiUrl       = 'http://api.66.localhost/';
$apiKey       = '386749b96b1ceb8001c15cc25e8d358b';
$options = array (
	'eventTypes' => array (
		// IdShType => type_id
		/*
<ROWDATA>
<ROW IdShType="фв" Name="Фестивали и выставки" Descr="" Hidden=""/>
<ROW IdShType="кц" Name="Концерты и Шоу" Descr="" Hidden=""/>
<ROW IdShType="дт" Name="Детские" Descr="" Hidden=""/>
<ROW IdShType="те" Name="Театр" Descr="" Hidden="0"/>
<ROW IdShType="см" Name="Семинары" Descr="" Hidden=""/>
<ROW IdShType="сп" Name="Спорт" Descr="" Hidden=""/>
<ROW IdShType="гс" Name="Гастроли" Descr="" Hidden=""/>
</ROWDATA>
		'фв' => '',
		'кц' => '',
		'дт' => '',
		'те' => '',
		'см' => '',
		'сп' => '',
		'гс' => '',
		*/
	),
	'eventTags' => array (
		// IdShType => tag_id
		/*
		'фв' => '',
		'кц' => '',
		'дт' => '',
		'те' => '',
		'см' => '',
		'сп' => '',
		'гс' => '',
		*/
	),
	'placeTypes' => array (
		/*
<select class="grid-span-7" name="AfishaEventPlace[type]" id="AfishaEventPlace_type">
<option value="empty">Не выбрано</option>
<option value="cinema">Кино</option>
<option value="theatre">Театр</option>
<option value="concert">Концерт</option>
<option value="club" selected="selected">Клуб</option>
<option value="art">Искусство</option>
<option value="sport">Спорт</option>
<option value="circus">Цирк</option>
</select>

<ROW Id="2" Name="Билетные кассы" ShortName="бк" Kind="0"/>
<ROW Id="12" Name="Выставки и музеи" ShortName="вм" Kind="1"/>
<ROW Id="10" Name="Кафе и рестораны" ShortName="кр" Kind="1"/>
<ROW Id="9" Name="Кинотеатры" ShortName="кт" Kind="1"/>
<ROW Id="7" Name="Клубы" ShortName="кл" Kind="1"/>
<ROW Id="5" Name="Концертные залы" ShortName="кз" Kind="1"/>
<ROW Id="11" Name="Парки" ShortName="пр" Kind="1"/>
<ROW Id="6" Name="Развлекательные центры" ShortName="рц" Kind="1"/>
<ROW Id="8" Name="Спортивные арены" ShortName="са" Kind="1"/>
<ROW Id="3" Name="Театры" ShortName="тр" Kind="1"/>
<ROW Id="1" Name="Центральный офис" ShortName="цо" Kind="0"/>
<ROW Id="4" Name="Цирк" ShortName="цк" Kind="1"/>
		*/
		12 => 'art', 
		9 => 'cinema', 
		7 => 'art', 
		7 => 'club', 
		5 => 'concert', 
		8 => 'sport', 
		3 => 'art', 
		4 => 'circus', 
	),
);