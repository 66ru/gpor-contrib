<?php
/**
 * User: bazilio
 * Date: 19.03.12
 * Time: 18:10
 */

return array(
	'apiUrl' => 'http://66.bazilio/api/', // gpor api url
	'apiKey' => 'телега, например', // gpor api key

	'root' => '/var/www/contrib/afisha-cinema-bilek/', // document root
	'debug' => true,
	'url' => 'http://contrib.bazilio/afisha-cinema-bilek/', // static url

	'urls' => array(
		'performances' => 'http://bilektron.org/api/afisha.php?act=performances&referal=r66', // performances api url
		'movie'        => 'http://bilektron.org/api/afisha.php?act=filmdesc&referal=r66&filmId={id}',  // movie api url
		'image'        => '{url}{filesDir}/images/{id}.{ext}', // internal images url
	),

	'filesDir' => 'files', // files dir (relative to root)

	// tmp files paths (relative to filesDir)
	'files' => array(
		'performances' => 'performances.json',
		'movie'        => 'movie_{id}.json',
		'image'        => 'images/{id}.{ext}',
	),
);