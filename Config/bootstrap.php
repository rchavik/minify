<?php

App::build(array(
    'Vendor' => CakePlugin::path('Minify') . 'Vendor' .DS. 'minify' .DS. 'min' .DS. 'lib'. DS,
	));

Configure::load('Minify.minify');

if (class_exists('Croogo')) {

	Croogo::hookHelper('*', 'Minify.Minify');

}
