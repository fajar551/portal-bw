<?php

namespace App\Compiler;

class MyBladeCompiler extends \Illuminate\View\Compilers\BladeCompiler
{
    public function setContentTags($openTag, $closeTag, $escaped = false)
	{
		$property = ($escaped === true) ? 'escapedTags' : 'contentTags';

		$this->{$property} = array(preg_quote($openTag), preg_quote($closeTag));
	}

    public function setRawTags($openTag, $closeTag)
	{
		$this->rawTags = array(preg_quote($openTag), preg_quote($closeTag));
	}
}
