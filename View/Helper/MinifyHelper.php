<?php

App::uses('JSMin', 'Vendor');
App::import('Vendor', 'Minify.Minify_CSS_Compressor', array(
	'file' => 'minify/min/lib/Minify/CSS/Compressor.php',
	));

class MinifyHelper extends AppHelper {

	public $helpers = array(
		'Html',
		);

	/**
	 * @param array $scripts to minify
	 * @param array $options theme
	 */
	public function script($scripts, $options = array()) {
		if (Configure::read('debug') || Configure::read('Minify.minify') === false) {
			return $this->Html->script($scripts, $options);
		}
		$options = Set::merge(array(
			'theme' => $this->_View->theme,
			'plugin' => false,
			'subdir' => false,
			), $options);
		extract($options);

		$path = APP;
		if (!empty($theme)) {
			$path = App::themePath($theme);
		}
		if (!empty($plugin)) {
			$path = CakePlugin::path($plugin);
		}
		if ($subdir && $subdir[strlen($subdir) - 1] != DS) {
			$subdir .= DS;
		}

		$targetDirectory = $path . 'webroot' . DS . 'js' . DS;
		$outputfile = $targetDirectory . $subdir  . 'minified-' . sha1(join(':', $scripts)) . '.js';

		if (file_exists($outputfile)) {
			if ($plugin) {
				$outputfile = '/' . Inflector::underscore(strtolower($plugin)) . '/js/' . basename($outputfile);
			} else {
				$outputfile = str_replace($targetDirectory, '', $outputfile);
			}
			return $this->Html->script($outputfile, $options);
		}

		$contents = '';
		$loaded = CakePlugin::loaded();
		foreach ($scripts as $script) {
			$scriptE = explode('.', $script, 2);
			if (in_array($scriptE[0], $loaded)) {
				$script = $scriptE[1];
			}
			$file = $targetDirectory .  $script;
			if (!preg_match('/\.js$/', $file)) {
				$file .= '.js';
			}
			if (!empty($options['plugin'])) {
				$file = preg_replace('/' . Inflector::underscore(strtolower($options['plugin'])) . '\/js/', '', $file);
			}

			$contents .= ";\r" . file_get_contents($file);
		}
		$contents = JSMin::minify($contents);
		file_put_contents($outputfile, $contents);

		return $this->Html->script($scripts, $options);
	}

	/**
	 * @param array $scripts to minify
	 * @param array $options theme
	 */
	public function css($scripts, $options = array()) {
		if (Configure::read('debug') || Configure::read('Minify.minify') === false) {
			return $this->Html->css($scripts);
		}
		$options = Set::merge(array(
			'theme' => $this->_View->theme,
			'plugin' => false,
			'subdir' => false,
			), $options);
		extract($options);

		$path = APP;
		if (!empty($theme)) {
			$path = App::themePath($theme);
		} elseif (!empty($plugin)) {
			$path = CakePlugin::pluginPath($plugin);
		}

		$targetDirectory = $path .DS. 'webroot' .DS. 'css' .DS;
		$outputfile = $targetDirectory . $subdir . 'minified-' . sha1(join(':', $scripts)) . '.css';


		if (file_exists($outputfile)) {
			$outputfile = str_replace($targetDirectory, '', $outputfile);
			return $this->Html->css($outputfile);
		}

		$contents = '';
		foreach ($scripts as $script) {
			$file = $targetDirectory .  $script;
			if (!preg_match('/\.css$/', $file)) {
				$file .= '.css';
			}
			$contents .= file_get_contents($file);
		}

		$contents = Minify_CSS_Compressor::process($contents);
		file_put_contents($outputfile, $contents);

		return $this->Html->css($scripts);
	}

}
