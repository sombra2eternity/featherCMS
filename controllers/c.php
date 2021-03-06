<?php
	function c_main(){
		$c = $f = false;
		$args = func_get_args();

		$params = implode('/',$args);
		/* INI-loading resources */
		if(preg_match('/(css|js|images|font)\/.*?\.([a-z]{2,4}$)/',$params,$m)){
			$m[0] = '../../assis/'.$m[0];if(!file_exists($m[0])){exit;}
			switch($m[2]){
				case 'css':header('Content-type: text/css');break;
				case 'js':header('Content-type: application/javascript');break;
				case 'png':header('Content-type: image/png');break;
				case 'gif':header('Content-type: image/gif');break;
				case 'ttf':case 'woff':case 'otf':case 'eot':header('Content-type: application/x-unknown-content-type');break;
			}
			readfile($m[0]);exit;
		}
		/* END-loading resources */

		$TEMPLATE = &$GLOBALS['TEMPLATE'];
		if($args){$c = array_shift($args);}
		if($args){$f = array_shift($args);}

		$controller = $c;
		if(substr($controller,-4) !== '.php'){$controller .= '.php';}
		if(!file_exists($GLOBALS['controllersExte'].$controller)){header('Location: '.$GLOBALS['baseURL']);exit;}
		$currentFunctions = get_defined_functions();
		$currentFunctions = $currentFunctions['user'];
		chdir('../../PHP/');
		include_once($GLOBALS['controllersExte'].$controller);
		$functionName = $c.'_'.$f;
		if(!$f || !function_exists($functionName)){
			if($f){array_unshift($args,$f);}
			$functionName = $c.'_main';
		}

		if(function_exists($functionName)){
			common_setBase('../../featherCMS/views/base');
			common_setPath('../assis/views/');
			common_loadStyle('{%w.featherURL%}/c/css/index.css');

			$jsControllerPath = '../assis/js/';
			$jsControllerFile = $c.'.'.$f.'.js';if(file_exists($jsControllerPath.$jsControllerFile)){$TEMPLATE['BLOG_JS'][] = '{%baseURL%}c/js/'.$jsControllerFile;}

			return call_user_func_array($functionName,$args);
		}

		$newFunctions = get_defined_functions();
		$newFunctions = $newFunctions['user'];
		$newFunctions = array_diff($newFunctions,$currentFunctions);

		$TEMPLATE['list.functions'] = '';
		foreach($newFunctions as $func){
			$TEMPLATE['list.functions'] .= '<li><a href="{%baseURL%}c/'.str_replace('_','/',$func).'">'.$func.'</a></li>';
		}

		return common_renderTemplate('c/main');
	}

