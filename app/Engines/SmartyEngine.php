<?php

namespace App\Engines;

use Illuminate\View;
use Illuminate\View\Engines;
use Illuminate\View\Compilers\CompilerInterface;
use Illuminate\Contracts\View\Engine;

use Smarty;

class SmartyEngine implements Engine {

	protected $config;

	public function __construct($config) {
		$this->config = $config;
	}

	public function get($path, array $data = []) {
		return $this->evaluatePath($path, $data);
	}

	protected function evaluatePath($path, $data) {
		$smarty = new Smarty();
		$smarty->setTemplateDir($this->config('template_path'));
		$smarty->setCompileDir($this->config('compile_path'));
		$smarty->setCacheDir($this->config('cache_path'));
		$smarty->setConfigDir($this->config('config_path'));
		$smarty->addPluginsDir($this->config('plugins_path'));

		$smarty->caching = $this->config('caching', true);
		$smarty->cache_lifetime = $this->config('cache_lifetime');
		$smarty->compile_check = true;
		$smarty->escape_html = $this->config('escape_html', false);

		foreach ($data as $var => $val) {
			$smarty->assign($var, $val);
		}

		return $smarty->fetch($path);
	}

	public function getCompiler() {
		return $this->compiler;
	}

	protected function config($key, $default = null) {
		return $this->config->get('smarty.' . $key, $default);
	}

}
