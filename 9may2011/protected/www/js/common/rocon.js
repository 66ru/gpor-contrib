var rocon=(function(){
/**
 * Общие методы и свойства для rocon
 * @author Sergey Chikuyonok (sc@design.ru)
 * @copyright Art.Lebedev Studio (http://www.artlebedev.ru)
 */

var re_rule = /\.rc(\d+)\b/,
	re_class = /\brc(\d+)\b/,
	re_shape_flag = /\brc-shape\b/,

	/** Префиск для создаваемых CSS-правил */
	rule_prefix = 'rocon__',

	/** Базовый класс для создаваемых элементов */
	base_class = 'rocon',

	/** Привязанные к определенным классам фоны */
	binded_props = [],

	/** Результат, возвращаемый в объект <code>rocon</code> */
	result = {
		/**
		 * Добавление/обновление уголков для динамически созданных элементов.
		 * Может принимать неограниченное количество элементов либо массивов
		 * элементов, у которых нужно обновить уголки
		 */
		update: function(){},
		bindProperties: function(){
			var id = 1;
			return function(rule, bg, border_width) {
				binded_props.push({
					'id': id++,
					'rule': rule,
					'bg': mapArray(expandProperty(bg), function(val){
						if (val.charAt(0) != '#')
							val = '#' + val;
						return convertColorToHex(val);
					}),
					'border_width': border_width || 0
				});
			}
		}(),

		process: function(context) {
			processRoundedElements(context);
		}
	},

	/** @type {CSSStyleSheet} Таблица стилей для уголков */
	corners_ss = null,

	/** Кэш для уголков */
	_corner_cache = {},

	/** Классы элементов, которым нужно добавить скругленные уголки */
	elem_classes = [],

	/** Список функций, которые нужно выполнить при загрузке DOM-дерева */
	dom_ready_list = [],

	/** Загрузился ли DOM? */
	is_ready = false,

	/** Привязано ли событие, ожидающее загрузку DOM? */
	readyBound = false,

	userAgent = navigator.userAgent.toLowerCase(),

	/**
	 * CSS-селекторы, которые уже были добавлены в стили.
	 * Используется для того, чтобы не создавать одинаковые правила
	 */
	processed_rules = {},

	/** Тип и версия браузера пользователя. Взято с jQuery */
	browser = {
		version: (userAgent.match( /.+(?:rv|it|ra|ie)[\/: ]([\d.]+)/ ) || [])[1],
		safari: /webkit/.test( userAgent ),
		opera: /opera/.test( userAgent ),
		msie: /msie/.test( userAgent ) && !/opera/.test( userAgent ),
		mozilla: /mozilla/.test( userAgent ) && !/(compatible|webkit)/.test( userAgent )
	};

/**
 * Выполняет все функции, добавленные на событие onDomContentLoaded.
 * Взято с jQuery
 */
function fireReady() {
	//Make sure that the DOM is not already loaded
	if (!is_ready) {
		// Remember that the DOM is ready
		is_ready = true;

		// If there are functions bound, to execute
		if ( dom_ready_list.length ) {

			for (var i = 0; i < dom_ready_list.length; i++) {
				dom_ready_list[i].call(document);
			}

//			walkArray(dom_ready_list, function(){
//				this.call(document);
//			});

			// Reset the list of functions
			dom_ready_list = null;
		}
	}
}

/**
 * Добавляет слушателя на событие onDomContentLoaded
 * @type {Function} fn Слушатель
 */
function addDomReady(fn) {
	dom_ready_list.push(fn);
}

/**
 * Проверка на наступление события onDomContentLoaded.
 * Взято с jQuery
 */
function bindReady(){

	/**
	 * Disabling default bindReady
	 * and putting own instead
     */

	$(document).bind('documentbodyloadend', fireReady);

	return;

	if ( readyBound ) return;
	readyBound = true;

	// Mozilla, Opera and webkit nightlies currently support this event
	if ( document.addEventListener ) {
		// Use the handy event callback
		document.addEventListener( "DOMContentLoaded", function(){
			document.removeEventListener( "DOMContentLoaded", arguments.callee, false );
			fireReady();
		}, false );

	// If IE event model is used
	} else if ( document.attachEvent ) {
		// ensure firing before onload,
		// maybe late but safe also for iframes
		document.attachEvent("onreadystatechange", function(){
			if ( document.readyState === "complete" ) {
				document.detachEvent( "onreadystatechange", arguments.callee );
				fireReady();
			}
		});

		// If IE and not an iframe
		// continually check to see if the document is ready
		if ( document.documentElement.doScroll && !window.frameElement ) (function(){
			if ( is_ready ) return;

			try {
				// If IE is used, use the trick by Diego Perini
				// http://javascript.nwbox.com/IEContentLoaded/
				document.documentElement.doScroll("left");
			} catch( error ) {
				setTimeout( arguments.callee, 0 );
				return;
			}

			// and execute any waiting functions
			fireReady();
		})();
	}
}

/**
 * Вспомогательная функция, которая пробегается по всем элементам массива
 * <code>ar</code> и выполняет на каждом элементе его элементе функцию
 * <code>fn</code>. <code>this</code> внутри этой функции указывает на
 * элемент массива
 * @param {Array} ar Массив, по которому нужно пробежаться
 * @param {Function} fn Функция, которую нужно выполнить на каждом элементе массива
 * @param {Boolean} forward Перебирать значения от начала массива (п умолчанию: с конца)
 */
function walkArray(ar, fn, forward) {
	if (forward) {
		for (var i = 0, len = ar.length; i < len; i++)
			if (fn.call(ar[i], i) === false)
				break;
	} else {
		for (var i = ar.length - 1, result; i >= 0; i--)
			if (fn.call(ar[i], i) === false)
				break;
	}
}

/**
 * Преобразует один массив элементов в другой с помощью функции callback.
 * Взято в jQuery
 * @param {Array} elems
 * @param {Function} callback
 * @return {Array}
 */
function mapArray(elems, callback) {
	var ret = [];

	// Go through the array, translating each of the items to their
	// new value (or values).
	for ( var i = 0, length = elems.length; i < length; i++ ) {
		var value = callback( elems[ i ], i );

		if ( value != null )
			ret[ ret.length ] = value;
	}

	return ret.concat.apply( [], ret );
}

/**
 * Функция добавления скругленных уголков элементу. Для каждого браузера
 * будет своя функция
 */
function addCorners(){
	return;
};

// TODO Добавить исключение при правильной работе border-radius

/**
 * Преобразует цвет из RGB-предствления в hex
 * @param {String} color
 * @return {String}
 */
function convertColorToHex(color) {
	var result;
	function s(num) {
		var n = parseInt(num, 10).toString(16);
		return (n.length == 1) ? n + n : n;
	}

	function p(num) {
		return s(Math.round(num * 2.55));
	}

	if (result = /rgb\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*\)/.exec(color))
		return '#' + s(result[1]) + s(result[2]) + s(result[3]);

	// Look for rgb(num%,num%,num%)
	if (result = /rgb\(\s*(\d+(?:\.\d+)?)\%\s*,\s*(\d+(?:\.\d+)?)\%\s*,\s*(\d+(?:\.\d+)?)\%\s*\)/.exec(color))
		return '#' + p(result[1]) + p(result[2]) + p(result[3]);

	// Look for #a0b1c2
	if (result = /#([a-f0-9]{2})([a-f0-9]{2})([a-f0-9]{2})/i.exec(color))
		return '#' + result[1] + result[2] + result[3];

	if (result = /#([a-f0-9])([a-f0-9])([a-f0-9])/i.exec(color))
		return '#' + result[1] + result[1] + result[2] + result[2] + result[3] + result[3];

	s = null;
	p = null;

	return color;
}

/**
 * Создает HTML-элемент <code>name</code> с классом <code>class_name</code>
 * @param {String} name Название элемента
 * @param {String} class_name Класс элемента
 * @return {Element}
 */
function createElement(name, class_name) {
	var elem = document.createElement(name);
	if (class_name) {
		elem.className = class_name;
	}
	return elem;
}

/**
 * Простая проверка наличия определенного класса у элемента
 * @param {HTMLElement} elem
 * @param {String} class_name
 * @return {Boolean}
 */
function hasClass(elem, class_name) {
	var re = new RegExp('\\b' + class_name + '\\b');
	return elem.nodeType == 1 && re.test(elem.className || '');
}

/**
 * Возвращает значение CSS-свойства <b>name</b> элемента <b>elem</b>
 * @author John Resig (http://ejohn.org)
 * @param {Element} elem Элемент, у которого нужно получить значение CSS-свойства
 * @param {String|Array} name Название CSS-свойства
 * @return {String|Object}
 */
function getStyle(elem, name) {
	var cs,
		result = {},
		camel = function(str, p1){return p1.toUpperCase();};

	walkArray(name instanceof Array ? name : [name], function(){
		var n = this,
			name_camel = n.replace(/\-(\w)/g, camel);

		// If the property exists in style[], then it's been set
		// recently (and is current)
		if (elem.style[name_camel]) {
			result[name_camel] = elem.style[name_camel];
		}
		//Otherwise, try to use IE's method
		else if (browser.msie) {
			result[name_camel] = elem.currentStyle[name_camel];
		}
		// Or the W3C's method, if it exists
		else if (document.defaultView && document.defaultView.getComputedStyle) {
			if (!cs)
				cs = document.defaultView.getComputedStyle(elem, "");
			result[name_camel] = cs && cs.getPropertyValue(n);
		}
	});

	return name instanceof Array ? result : result[name.replace(/\-(\w)/g, camel)];

}

/**
 * Разворачивает краткую запись четырехзначного свойства в полную:<br>
 * 	— a      -&gt; a,a,a,a<br>
 *	— a_b    -&gt; a,b,a,b<br>
 *	— a_b_с  -&gt; a,b,с,b<br>
 *
 * @param {String} prop Значение, которое нужно раскрыть
 * @return {Array} Массив с 4 значениями
 */
function expandProperty(prop) {
	var chunks = (prop || '').split('_');

	switch (chunks.length) {
		case 1:
			return [chunks[0], chunks[0], chunks[0], chunks[0]];
		case 2:
			return [chunks[0], chunks[1], chunks[0], chunks[1]];
		case 3:
			return [chunks[0], chunks[1], chunks[2], chunks[1]];
		case 4:
			return chunks;
	}

	return null;
}

/**
 * Возвращает цвет фона элемента
 * @type {Function}
 * @param {Element} elem Элемент, для которого нужно достать цвет фона
 * @param {Boolean} use_shape Для элемента создаются уголки в виде формы
 * @return {Array} Массив из 4 элементов фона
 */
var getBg = (function() {

	var session_elems = [],
		default_color = '#ffffff';

	/**
	 * Основной цикл с использованием кэширования
	 */
	function mainLoopCache(elem) {
		var c;
		do {
			if (elem.nodeType != 1)
				break;

			if (elem.rocon_bg) { // цвет был найден ранее
				return elem.rocon_bg;
			} else { // цвет еще не найден
				session_elems.push(elem);
				c = getStyle(elem, 'background-color');
				if (c != 'transparent')
					return convertColorToHex(c);
			}

		} while (elem = elem.parentNode);

		return default_color;
	}

	/**
	 * Основной цикл без кэширования
	 */
	function mainLoopNoCache(elem) {
		var c;
		do {
			if (elem.nodeType != 1)
				break;

			c = getStyle(elem, 'background-color');
			if (c != 'transparent')
				return convertColorToHex(c);

		} while (elem = elem.parentNode);

		return default_color;
	}

	return function(elem, use_shape){
		var cl = /* String */elem.className,
			bg = null;

		// сначала посмотрим, указан ли фон в классе элемента
		var bg_props = /\brcbg([a-f0-9_]+)\b/i.exec(cl);

		if (bg_props) {

			bg =  mapArray(expandProperty(bg_props[1]), function(el){
				return convertColorToHex('#' + el);
			});

			return bg;
		}

		// Теперь проверяем, есть ли привязанный через rocon.bindBg() к классу фон
		var elem_props = getBindedProperties(elem);
		if (elem_props) {
			return elem_props.bg;
		}

		if (!use_shape)
			elem = elem.parentNode;

		if (getBg.use_cache) {
			session_elems = [];
			bg = mainLoopCache(elem);
			// закэшируем цвет фона у всех элементов, по которым проходились
			walkArray(session_elems, function(){
				this.rocon_bg = bg;
				getBg.processed_elems.push(this);
			});

			session_elems = null;
		} else {
			bg = mainLoopNoCache(elem);
		}

		return expandProperty(bg);
	}
})();

getBg.use_cache = true;
getBg.processed_elems = [];

function getBindedProperties(elem) {
	var cl = elem.className, result = null;
	walkArray(binded_props, function(){
		if (
			// проверка наличия подстроки
			(typeof(this.rule) == 'string' && cl.indexOf(this.rule) != -1) ||
			// проверка по регулярке
			cl.search(this.rule) != -1
		) {
			result = this;
			return false;
		}
	}, true);

	return result;
}

/**
 * Добавляет CSS-правило в стиль
 * @param {String} selector CSS-селектор, для которого нужно добавить правила
 * @param {String} rules CSS-правила
 */
function addRule(selector, rules) {
	corners_ss.insertRule(selector + ' {' + rules + '}', corners_ss.cssRules.length);
}

/**
 * Функция поиска правил для скругленных уголков
 * @param {Function} addFunc Функция добавления уголков
 */
function findRules(addFunc) {
	/** @type {String[]}  */
	var match;

	walkArray(document.styleSheets, function(){
		walkArray(this.cssRules || this.rules, function(){
			if (match = re_rule.exec(this.selectorText))
				addFunc(this, parseInt(match[1], 10));
		});
	});
}

/**
 * Очищает элемент от предыдущих вставок скругленных уголков
 * @param {Element} elem
 * @param {String} [add_class] Классы, которые нужно добавить
 * @return {Element} Переданный элемент
 */
function cleanUp(elem, add_class) {
	var	cl = (elem.className || '').replace(new RegExp('\\s*' + base_class + '[\-_].+?\\b', 'ig'), '');
	if (add_class) {
		cl += ' ' + add_class;
	}

	elem.className = cl;
	return elem;
}


/**
 * Функция добавления правил для скругленных уголков
 */
function addRoundedProperties(/* CSSStyleRule */ rule, /* Number */ radius) {
	elem_classes.push(rule.selectorText.substr(1));
}

/**
 * Создает новую таблицу стилей на странице, куда будут добавляться правила
 * для описания скругленных уголков
 * @return {CSSStyleSheet}
 */
function createStylesheet() {
	if (!corners_ss) {
		if (document.createStyleSheet) {
			corners_ss = document.createStyleSheet();
		} else {
			var style = createElement('style');
			style.rel = 'rocon';
			document.getElementsByTagName('head')[0].appendChild(style);

			/*
			 * Просто получить самый последний стиль не получится: иногда стили
			 * добавляются внутрь <body> (так делает счетчик Яндекса, например),
			 * в этом случае мы не можем быть уверены, что только что
			 * добавленная таблица стилей — последняя. Поэтому пробегаетмся
			 * по всем таблицам в поисках нашей
			 */
			walkArray(document.styleSheets, function(){
				if (this.ownerNode.rel == 'rocon') {
					corners_ss = this;
					return false;
				}
			});
		}
	}

	return corners_ss;
}

/**
 * Возвращает массив элементов, которым нужно добавить скругленные уголки.
 * Элементом массива является объект со свойствами <code>node</code>
 * и <code>radius</code>
 * @param {Element} [context] Откуда брать элементы
 * @return {Array}
 */
function getElementsToProcess(context) {
	var elems = [], m;

	walkArray((context || document).getElementsByTagName('*'), function(){
		if (m = re_class.exec(this.className || '')) {
			elems.push({node: this, radius: parseInt(m[1], 10)});
		}
	});

	return elems;
}

/**
 * Обрабатывает все элементы на странице, которым нужно добавить скругленные
 * уголки
 */
function processRoundedElements(context){
	var elems = getElementsToProcess(context);
	if (elems.length) {
		createStylesheet();
		walkArray(elems, function(){
			addCorners(this.node, this.radius);
		});
	}
}
/**
 * Проверяет, был ли добавлен CSS-selector в таблицу стилей
 * @param {String} selector
 * @return {Boolean}
 */
function isProcessed(selector) {
	return processed_rules[selector] ? true : false;
}

/**
 * Возвращает параметры уголка элемента
 * @param {Element} elem Элемент, у которого нужно получить параметры уголка
 * @param {Number} [radius] Радиус скругления
 */
function getCornerParams(elem, radius) {
	var cl = elem.className || '';
	radius = radius || parseInt(cl.match(re_class)[1], 10);
	var use_shape = re_shape_flag.test(cl),
		props = getBindedProperties(elem);

	var border_color = '';
	var border_width = props ? props.border_width : (parseInt(getStyle(elem, 'border-left-width')) || 0);
	if (border_width) {
		// нужно отрисовать бордюр
		border_color = convertColorToHex(getStyle(elem, 'border-left-color') || '#000');
	}

	return {
		'radius': radius,
		'bg_color': getBg(elem, use_shape),

		// толщина бордюра не может быть больше радиуса скругления
		// (так по CSS3 спецификации)
		'border_width': (border_width > radius) ? radius : border_width,
		'real_border_width': border_width,
		'border_color': border_color,
		'use_shape': use_shape
	};
}

/**
 * Применяет уголки к элементам, переданным в массиве. В основном вызывается из
 * <code>rocon.update()</code>
 * @param {arguments} args Аргументы функции
 * @param {Function} fn Функция, которую нужно выполнить на каждом элементе
 */
function applyCornersToArgs(args, fn) {
	walkArray(args, function(){
		walkArray((this instanceof Array) ? this : [this], fn);
	});
}

/**
 * Делает копию объекта
 * @param {Object} obj
 * @return {Object}
 */
function copyObj(obj) {
	var result = {};
	for (var p in obj)
		if (obj.hasOwnProperty(p))
			result[p] = obj[p];

	return result;
}

/**
 * Корректирует CSS-свойства элемента для правильного рисования уголков в виде
 * формы
 * @param {HTMLElement} elem Элемент, который нужно подкорректировать
 * @param {String} class_name Имя создаваемого класса
 * @param {getCornerParams()} options параметры рисования уголка
 */
function adjustBox(elem, class_name, options) {
	var elem_styles = getStyle(elem, ['padding-top', 'padding-bottom', 'margin-top', 'margin-bottom']);
	function getProp(prop) {
		return parseInt(elem_styles[prop], 10) || 0;
	}

	/*
	 * Используем форму, поэтому у блока снижаем верхние и нижние
	 * бордюры, а также на величину радиуса снижаем верхний
	 * и нижний паддинг
	 */

	var padding_top = Math.max(getProp('paddingTop') - options.radius + options.border_width, 0),
		padding_bottom = Math.max(getProp('paddingBottom') - options.radius + options.border_width, 0),
		margin_top = getProp('marginTop') + options.radius,
		margin_bottom = getProp('marginBottom') + options.radius,
		border_width = options.real_border_width - options.border_width;

	addRule('.' + class_name,
			'border-top-width:' + border_width + 'px;' +
			'border-bottom-width:' + border_width + 'px;' +
			'padding-top:' + padding_top + 'px;' +
			'padding-bottom:' + padding_bottom + 'px;' +
			'margin-top:' + margin_top + 'px;' +
			'margin-bottom:' + margin_bottom + 'px' );
}

addDomReady(processRoundedElements);
// после того, как добавили уголки, необходимо очистить кэш фона,
// иначе будут проблемы с динамическим обновлением блоков
addDomReady(function(){
	walkArray(getBg.processed_elems, function(){
		this.removeAttribute('rocon_bg');
	});
	getBg.use_cache = false;
});

bindReady();/**
 * Добавление уголков для Safari
 * @author Sergey Chikuyonok (sc@design.ru)
 * @copyright Art.Lebedev Studio (http://www.artlebedev.ru)
 * @include "common.js"
 */

if (browser.safari) {
	addCorners = function(elem, radius) {
		var selector = '.rc' + radius;
		if (!isProcessed(selector)) {
			addRule(selector, '-webkit-border-radius:' + radius + 'px; -khtml-border-radius:' + radius);
			processed_rules[selector] = true;
		}
	}

	result.update = function() {
		applyCornersToArgs(arguments, function(){
			var m = re_class.exec(this.className || '');
			if (m)
				addCorners(this, parseInt(m[1]));
		});
	}
}/**
 * Добавление уголков для Firefox
 * @author Sergey Chikuyonok (sc@design.ru)
 * @copyright Art.Lebedev Studio (http://www.artlebedev.ru)
 * @include "common.js"
 */

if (browser.mozilla) {
	addCorners = function(elem, radius) {
		var selector = '.rc' + radius;
		if (!isProcessed(selector)) {
			addRule(selector, '-moz-border-radius:' + radius + 'px');
			processed_rules[selector] = true;
		}
	}

	result.update = function() {
		applyCornersToArgs(arguments, function(){
			var m = re_class.exec(this.className || '');
			if (m)
				addCorners(this, parseInt(m[1]));
		});
	}
}/**
 * Добавление уголков для Opera
 * @author Sergey Chikuyonok (sc@design.ru)
 * @copyright Art.Lebedev Studio (http://www.artlebedev.ru)
 * @include "common.js"
 * @include "/js-libs/canvas-doc.js"
 */

if (browser.opera) {
	/*
	 * Нужно дожаться, пока загрузится DOM-дерево, после чего получить все
	 * элементы, которым нужно скруглить уголки, и добавить соотвествующие
	 * стили и элементы
	 */

	createStylesheet();
	addRule('.' + base_class, 'position:absolute;background-repeat:no-repeat;z-index:1;display:none');
	addRule('.' + base_class + '-init', 'position:relative;');
	addRule('.' + base_class + '-init>.' + base_class, 'display:inline-block;');
	addRule('.' + base_class + '-tl', 'top:0;left:0;background-position:100% 100%;');
	addRule('.' + base_class + '-tr', 'top:0;right:0;background-position:0 100%;');
	addRule('.' + base_class + '-bl', 'bottom:0;left:0;background-position:100% 0;');
	addRule('.' + base_class + '-br', 'bottom:0;right:0;');

	/** @type {HTMLCanvasElement} Холст, на котором будут рисоваться уголки */
	var cv = createElement('canvas');

	/**
	 * Возвращает подготовленный контекст рисования на холсте
	 * @param {getCornerParams()} options Параметры рисования уголка
	 * @param {Boolean} is_shape Будем рисовать форму (true) или контр-форму (false)?
	 * @return {CanvasRenderingContext2D}
	 */
	function getDrawingContext(options) {
		options.border_width = (options.border_width > options.radius)
				? options.radius
				: options.border_width;

		if (options.border_width > 1)
			options.radius -= options.border_width / 2;


		var width = options.radius * 2 + options.border_width, height = width;
		if (options.use_shape) {
			width = 2000;
			if (options.border_width < options.real_border_width) {
				height += (options.real_border_width - options.border_width) * 2;
			}
		}


		if (options.border_width == 1) {
			width--;
			height--;
		}

		cv.width = options.width = width;
		cv.height = options.height = height;

		/** @type {CanvasRenderingContext2D} */
		var ctx = cv.getContext('2d');

		ctx.strokeStyle = options.border_color;
		ctx.lineWidth = options.border_width;
		ctx.lineJoin = 'miter';
		ctx.lineCap = 'square';
		ctx.fillStyle = options.bg_color[0];

		ctx.clearRect(0, 0, width, height);
		return ctx;
	}

	/**
	 * Делает обводку в виде звездочки
	 * @param {CanvasRenderingContext2D} ctx Контекст рисования
	 * @param {Number} options.radius Радиус скругления
	 * @param {String} options.color Цвет уголка в hex-формате
	 * @param {Number} options.border_width Толщина обводки
	 * @param {String} options.border_color Цвет обводки
	 */
	function strokeStar(ctx, options) {
		var deg90 = Math.PI / 2,
			b2 = (options.border_width > 1) ? options.border_width : 0,
			rb2 = options.radius * 2 + b2;

		ctx.beginPath();
		ctx.arc(0, 0, options.radius, deg90, 0, true);
		ctx.stroke();

		ctx.beginPath();
		ctx.arc(rb2, 0, options.radius, deg90 * 2, deg90, true);
		ctx.stroke();

		ctx.beginPath();
		ctx.arc(rb2, rb2, options.radius, -deg90, deg90 * 2, true);
		ctx.stroke();

		ctx.beginPath();
		ctx.arc(0, rb2, options.radius, 0, -deg90, true);
		ctx.stroke();
	}

	/**
	 * Рисует «звездочку» для создания формы уголков через canvas
	 * @param {Number} options.radius Радиус скругления
	 * @param {String} options.color Цвет уголка в hex-формате
	 * @param {Number} options.border_width Толщина обводки
	 * @param {String} options.border_color Цвет обводки
	 * @return {String} Картинка в формате data:URL
	 */
	function drawStarShape(options) {
		options = copyObj(options);

		var ctx = getDrawingContext(options),
			deg90 = Math.PI / 2,
			deg360 = Math.PI * 2,
			bw = options.border_width,
			b2 = (bw > 1) ? bw : 0,
			rb2 = options.radius * 2 + b2,
			diff = 0,
			draw_borders = (options.border_width < options.real_border_width);

		var drawCircle = function(x, y) {
			ctx.beginPath();
			ctx.arc(x, y, options.radius, 0, deg360, true);
			ctx.closePath();
			ctx.fill();
		}

		if (draw_borders) {
			// нужно дорисовать толщину бордера
			diff = options.real_border_width - options.border_width;
			ctx.save();
			ctx.translate(0, diff);
		}

		drawCircle(0, 0);
		drawCircle(rb2, 0);
		drawCircle(rb2, rb2);
		drawCircle(0, rb2);

		ctx.fillRect(rb2, 0, options.width, options.height);

		if (bw) {
			strokeStar(ctx, options);
			ctx.fillStyle = ctx.strokeStyle;
			ctx.fillRect(rb2, options.radius - (bw > 1 ? bw / 2 : bw), options.width, bw * 2);

			if (draw_borders) {
				ctx.restore();
				ctx.fillStyle = options.border_color;
				ctx.fillRect(0, 0, options.width, diff);
				ctx.fillRect(0, options.height - diff, options.width, diff);
				ctx.fillStyle = options.bg_color;
			}
		}

		return ctx.canvas.toDataURL();
	}

	/**
	 * Рисует «звездочку» через canvas
	 * @param {Number} options.radius Радиус скругления
	 * @param {String} options.color Цвет уголка в hex-формате
	 * @param {Number} options.border_width Толщина обводки
	 * @param {String} options.border_color Цвет обводки
	 * @return {String} Картинка в формате data:URL
	 */
	function drawStar(options) {
		var old_opt = options;
		options = copyObj(options);

		var ctx = getDrawingContext(options),
			radius = options.radius,
			b2 = (options.border_width > 1) ? options.border_width : 0,
			rb2 = radius * 2 + b2,
			r = old_opt.radius,
			deg90 = Math.PI / 2;

		ctx.save();
		ctx.beginPath();
		ctx.arc(0, 0, radius, deg90, 0, true);
		ctx.arc(rb2, 0, radius, deg90 * 2, deg90, true);
		ctx.arc(rb2, rb2, radius, -deg90, deg90 * 2, true);
		ctx.arc(0, rb2, radius, 0, -deg90, true);
		ctx.closePath();
		ctx.clip();


		ctx.fillStyle = options.bg_color[2];
		ctx.fillRect(0, 0, r, r)

		ctx.fillStyle = options.bg_color[3];
		ctx.fillRect(r, 0, r, r);

		ctx.fillStyle = options.bg_color[0];
		ctx.fillRect(r, r, r, r);

		ctx.fillStyle = options.bg_color[1];
		ctx.fillRect(0, r, r, r);
		ctx.restore();

		if (options.border_width)
			strokeStar(ctx, options);

		return ctx.canvas.toDataURL();
	}

	/**
	 * Возвращает ключ, по которому кэшируются отрисованные элементы
	 * @param {getCornerParams()} cparams Параметры скругления блока
	 * @param {HTMLElement} elem Элемент, для которого делаем скругление
	 * @return {String}
	 */
	function getCacheKey(cparams, elem) {
		var binded = getBindedProperties(elem);
		return [
			cparams.radius,
			cparams.bg_color.join('-'),
			cparams.real_border_width,
			cparams.border_color,
			cparams.use_shape,
			binded ? binded.id : 0
		].join(':');
	}

	/**
	 * Создает CSS-правила для уголков определенного радиуса и цвета
	 * @param {getCornerParams()} cparams Параметры скругления блока
	 * @param {HTMLElement} elem Элемент, для которого делаем скругление
	 * @return {String} Имя класса, которое нужно присвоить элементу
	 */
	function createCSSRulesOpera(cparams, elem) {
		var cache_key = getCacheKey(cparams, elem),
			radius = cparams.radius,
			bw = cparams.real_border_width || 0,
			diff = (cparams.use_shape) ? bw - cparams.border_width : 0;

		// смотрим, делали ли правило с такими же параметрами
		if (!_corner_cache[cache_key]) {
			// создаем новое правило
			var cur_class = rule_prefix + corners_ss.cssRules.length;
			_corner_cache[cache_key] = cur_class;

			addRule('.' + cur_class + '>.' + base_class,
				'background-image: url("' + ( cparams.use_shape ? drawStarShape(cparams) : drawStar(cparams) ) + '");' +
				'width: '+ radius +'px;' +
				'height: ' + (radius + diff) + 'px;'
			);

			var offset_x = -bw, offset_y = -bw;
			if (cparams.use_shape) {
				offset_y = -radius - diff;
				adjustBox(elem, cur_class, cparams);
				addRule(
					'.' + cur_class + '>.' + base_class + '-tl, .' + cur_class + '>.' + base_class + '-bl',
					'width:auto;left:0;right:'+ (radius - bw) +'px;background-position:-' + radius + 'px 100%;'
				);
				addRule('.' + cur_class + '>.' + base_class + '-bl', 'background-position:-' + radius + 'px 0;');
			}

			if (offset_x || offset_y) {
				addRule('.' + cur_class + '>.' + base_class + '-tl', 'top:'+ offset_y +'px; left:'+ offset_x +'px');
				addRule('.' + cur_class + '>.' + base_class + '-tr', 'top:'+ offset_y +'px; right:'+ offset_x +'px');
				addRule('.' + cur_class + '>.' + base_class + '-bl', 'bottom:'+ offset_y +'px; left:'+ offset_x +'px');
				addRule('.' + cur_class + '>.' + base_class + '-br', 'bottom:'+ offset_y +'px; right:'+ offset_x +'px');
			}
		}

		return _corner_cache[cache_key];
	}

	/**
	 * Добавляет уголки элементу
	 * @param {Element} elem
	 */
	addCorners = function(elem, radius){
		// если у элемента нет класса — значит, нет указания, какие уголки
		// нужно добавить
		if (!elem.className)
			return;

		// проверим, нужно ли добавлять элементы с уголками
		var dont_add = false;
		walkArray(elem.childNodes, function(){
			if (hasClass(this, base_class)) {
				dont_add = true;
				return false;
			}
		});

		var elem_class = createCSSRulesOpera(getCornerParams(elem, radius), elem);

		if (!dont_add)
			// добавляем уголки
			walkArray(['tl', 'tr', 'bl', 'br'], function(){
				elem.appendChild( createElement('span', base_class + ' ' + base_class +'-' + this) );
			});

		cleanUp(elem, elem_class + ' ' + base_class + '-init');
	};

	addDomReady(function(){
		/*
		 * Одна из причин, по которой я ненавижу Оперу — это
		 * необходимость до сих пор вставлять подобные костыли,
		 * чтобы что-то отобразились на странице
		 */
		document.documentElement.style.outline = 'none';
	});

	result.update = function() {
		applyCornersToArgs(arguments, function(){
			addCorners( cleanUp(this) );
		});
	}
}/**
 * Добавление уголков для IE
 * @author Sergey Chikuyonok (sc@design.ru)
 * @copyright Art.Lebedev Studio (http://www.artlebedev.ru)
 * @include "common.js"
 */

if (browser.msie) {
	/*
	 * Уголки для IE создаем через VML.
	 *
	 * У IE в этом скрипте есть одно очень узкое место: динамическое добавление
	 * CSS-правил в таблицу стилей (функция addRule()). Для увеличения
	 * производительности был применен следующий трюк: сначала, при первичной
	 * инициализации, весь CSS накапливается в переменной css_text, и после того,
	 * как все необходимые правила для существующих блоков были созданы,
	 * накопленный CSS применяется к созданной таблице стилей. После этого
	 * функция addRule() уже указывает на метод corners_ss.addRule()
	 */

	_corner_cache.ix = 0;
	_corner_cache.created = {};

	var css_text = '',
		corner_types = {
			tl: 0,
			tr: 1,
			br: 2,
			bl: 3
		};

	var vml_class = 'vml-' + base_class; //использую именно класс, чтобы работало в IE8

	try {
		if (!document.namespaces["v"])
			document.namespaces.add("v", "urn:schemas-microsoft-com:vml");
	} catch(e) { }

	createStylesheet();
	var dot_class = '.' + base_class;
	corners_ss.cssText = "." + vml_class + " {behavior:url(#default#VML);display:inline-block;position:absolute}" +
		dot_class + "-init {position:relative;zoom:1;}" +
		dot_class + " {position:absolute; display:inline-block; zoom: 1; overflow:hidden}" +
		dot_class + "-tl ." + vml_class + "{flip: 'y'}" +
		dot_class + "-tr ." + vml_class + "{rotation: 180;right:1px;}" +
		dot_class + "-br ." + vml_class + "{flip: 'x'; right:1px;}";

	if (browser.version < 7) {
		corners_ss.cssText += dot_class + '-tr, ' + dot_class + '-br {margin-left: 100%;}';
//				dot_class + ' .' + vml_class + '{position:absolute}' +
//				dot_class + '-tr .' + vml_class + '{right: 0}';
	}

	addRule = function(selector, rules){
		css_text += selector + '{' + rules + '}';
	};

	/**
	 * Создает элемент со скругленным уголком. В функции используется
	 * кэширование, то есть ранее созданный уголок дублируется,
	 * а не создается заново
	 * @param {getCornerParams()} options Параметры рисования уголка
	 * @return {HTMLElement}
	 */
	function createCornerElementIE(options) {
		var radius = options.radius,
			border_width = options.border_width,
			cache_key = radius + ':' + border_width + ':' + options.use_shape;

		if (!createCornerElementIE._cache[cache_key]) { // элемент еще не создан

			var multiplier = 10;

			var cv = createElement('v:shape');
			cv.className = vml_class;
			cv.strokeweight = border_width + 'px';
			cv.stroked = (border_width) ? true : false;
			var stroke = createElement('v:stroke');
			stroke.className = vml_class;
			stroke.joinstyle = 'miter';
			cv.appendChild(stroke);

			var w = radius, h = w;

			cv.style.width = w + 'px';
			cv.style.height = h + 'px';

			radius -= border_width / 2;
			radius *= multiplier;
			var bo = border_width / 2 * multiplier;
			var px = Math.round((radius + bo) / w);
			var rbo = radius + bo;

			cv.coordorigin = Math.round(px / 2) + ' ' + Math.round(px / 2);
			cv.coordsize = rbo + ' ' + rbo;

			var path = '';
			var max_width = rbo + px;

			if (options.use_shape) {
				max_width = 2000 * multiplier;
				path = 'm' + max_width + ',0 ns l' + bo +',0  qy' + rbo + ',' + radius + ' l' + max_width + ',' + radius + ' e ';
			} else {
				path = 'm0,0 ns l' + bo +',0  qy' + rbo + ',' + radius + ' l' + rbo + ',' + rbo + ' l0,' + rbo + ' e ';
			}


			// stroke
			path += 'm' + bo + ',' + (-px) + ' nf l' + bo + ',0 qy' + rbo + ',' + radius + ' l ' + (max_width) +','+ radius +' e x';

			cv.path = path;

			createCornerElementIE._cache[cache_key] = cv;
		}

		return createCornerElementIE._cache[cache_key].cloneNode(true);
	}

	createCornerElementIE._cache = {};

	/**
	 * Создает скругленный уголок
	 * @param {getCornerParams()} cparams параметры уголка
	 * @param {String} type Тип уголка (tl, tr, bl, br)
	 */
	function drawCornerIE(cparams, type){
		var cv = createCornerElementIE(cparams);
		cv.fillcolor = cparams.bg_color[corner_types[type]] || '#000';
		cv.strokecolor = cparams.border_color || '#000';

		var elem = createElement('span', base_class + ' ' + base_class + '-' + type);
		elem.appendChild(cv);

		return elem;
	}

	/**
	 * Удаляет у элемента старые уголки
	 * @param {HTMLElement} elem Элемент, у которого нужно удалить уголки
	 */
	function removeOldCorners(elem) {
		walkArray(elem.childNodes, function(){
			if (hasClass(this, base_class)) {
				elem.removeChild(this);
			}
		});

		cleanUp(elem);
	}

	/**
	 * Возвращает имя класса для переданных параметров. Используется для
	 * того, чтобы не плодить много разных классов для одних и тех же правил
	 * @param {getCornerParams()} options Параметры рисования уголка
	 * @return {String}
	 */
	function getClassName(options) {
		var key = options.radius + ':' + (options.real_border_width || 0) + ':' + options.use_shape;
		if (!_corner_cache[key]) {
			_corner_cache[key] = rule_prefix + _corner_cache.ix++;
		}

		return _corner_cache[key];
	}

	/**
	 * Создает CSS-правила для скругленных уголков
	 * @param {getCornerParams()} options Параметры рисования уголка
	 * @param {HTMLElement} elem Элемент, которому добавляются уголки
	 * @param {Number} border_width Толщина бордюра
	 */
	function createCSSRules(options, elem) {
		var radius = options.radius,
			border_width = options.real_border_width || 0,
			diff = (options.use_shape) ? options.real_border_width - options.border_width : 0;
//		border_width += 10;

//		corners_ss.disabled = true;

		var class_name = getClassName(options);
		if (!_corner_cache.created[class_name]) {
			// такое правило еще не создано в CSS, создадим его
			var prefix = (browser.version < 7)
							? '.' + class_name + ' .' + base_class  // IE6
							: '.' + class_name + '>.' + base_class; // IE7+

			var offset_x = -border_width,
				offset_y = -1 -border_width;


			addRule(prefix, 'width:' + (radius + border_width + 1) + 'px;height:' + (radius + 1) + 'px');

			if (options.use_shape) {
				offset_y = -radius - 1 - diff;
				var left_adjust = radius + options.border_width * 2 + diff;
				adjustBox(elem, class_name, options);
				var clip_size = Math.max(radius - border_width * 2, 0),
					pad_size = Math.min(radius - border_width * 2, 0) * -1;

				if (browser.version < 7) {
					pad_size += parseInt(getStyle(elem, 'padding-left') || 0) + parseInt(getStyle(elem, 'padding-right') || 0);
				}

				var css_rules = 'width:100%;clip:rect(auto auto auto ' + clip_size + 'px);padding-right:' + pad_size + 'px;left:' + (-border_width - clip_size) + 'px;';
				addRule(prefix + '-tl', css_rules + 'top:' + offset_y + 'px;');
				addRule(prefix + '-tl .' + vml_class, 'left:' + clip_size + 'px');

				addRule(prefix + '-bl', css_rules +'bottom:' + offset_y + 'px;');
				addRule(prefix + '-bl .' + vml_class, 'left:' + clip_size + 'px');
			} else {
				addRule(prefix + '-tl', 'left:' + offset_x + 'px;top:' + offset_y + 'px;');
				addRule(prefix + '-bl', 'left:' + offset_x + 'px;bottom:' + offset_y + 'px;');
			}

			if (browser.version < 7) {
				offset_x = -radius + (border_width ? radius % 2 - border_width % 2 : -radius % 2);

				addRule(prefix + '-tr', 'left:' + offset_x + 'px;top:' + offset_y + 'px;');
				addRule(prefix + '-br', 'left:' + offset_x + 'px;bottom:' + offset_y + 'px;');
			} else {
				addRule(prefix + '-tr', 'right:' + offset_x + 'px;top:' + offset_y + 'px;');
				addRule(prefix + '-br', 'right:' + offset_x + 'px;bottom:' + offset_y + 'px;');
			}

			_corner_cache.created[class_name] = true;
		}
	}

	addCorners = function(elem, radius) {
		var cparams = getCornerParams(elem, radius);

		createCSSRules(cparams, elem);

		// теперь добавляем сами уголки в элемент
		try {
			walkArray(['tl', 'tr', 'bl', 'br'], function(){
				elem.appendChild(drawCornerIE(cparams, this));
			});
		}
		catch(e) { }


		// говорим, что все добавилось
		elem.className += ' ' + getClassName(cparams) + ' ' + base_class + '-init';

	};

	result.update = function() {
		applyCornersToArgs(arguments, function(){
			removeOldCorners(this);
			addCorners(this);
		});
	};

	addDomReady(function(){
		corners_ss.cssText += css_text;
		css_text = '';
		addRule = corners_ss.addRule;
	});
};return result;})();