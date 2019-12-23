<?php namespace sox\sdk\com;

class html
{
	static $d = [];

	static $uri = '';

	static $file = 'index.php';
	static $base = 'html';

	static $base_dir = '';
	static $view_dir = '';
	static $vars_dir = '';

	static $def_route = '';
	static $hide_file = TRUE;

	static function __workon($file = 'index.php',$base = 'html',$must = '',$def_route = 'index',$hide_file = TRUE)
	{
		self::$file = $file;
		self::$base = $base;

		self::$def_route = $def_route;
		self::$hide_file = $hide_file;

		self::$base_dir = getcwd().'/'.self::$base.'/';
		self::$view_dir = self::$base_dir.'view/';
		self::$vars_dir = self::$base_dir.'vars/';

		self::$uri = self::uri(1,$def_route).'/'.self::uri(2,$def_route);

		if($must) require self::$base_dir.$must.'.php';

		if(substr(self::$uri,0,1) == '_' || strpos(self::$uri,'/_')) self::__403();

		self::uri(explode('/',self::$uri));

		if(self::uri(1,$def_route) != '')
		{
			if(is_file(self::$view_dir.self::uri(1,$def_route).'.php'))
			{
				self::__output(self::uri(1,$def_route));
			}
			elseif(is_dir(self::$view_dir.self::uri(1,$def_route)))
			{
				if(is_file(self::$view_dir.self::uri(1,$def_route).'/'.self::uri(2,$def_route).'.php'))
				{
					self::__output(self::uri(1,$def_route).'/'.self::uri(2,$def_route));
				}
				else
				{
					self::__404();
				}
			}
			else
			{
				self::__404();
			}
		}
		else
		{
			self::__404();
		}
	}

	static function __403()
	{
		header('HTTP/1.1 403 Forbidden');

		echo '<!DOCTYPE html><html><head><title>404 Not Found</title></head><body bgcolor="white"><center><h1>403 Forbidden</h1></center><hr></body></html>';

		exit;
	}

	static function __404()
	{
		header('HTTP/1.1 404 Not Found');

		echo '<!DOCTYPE html><html><head><title>404 Not Found</title></head><body bgcolor="white"><center><h1>404 Not Found</h1></center><hr></body></html>';

		exit;
	}

	static function __output($path)
	{
		header('Cache-Control: no-cache');
		header('Pragma: no-cache');
		header('Expires: 0');
		header('Content-type: text/html; charset=utf-8');

		self::render($path);

		exit;
	}

	static function render($path,$vars = '',$key = '')
	{
		$html = new \sox\sdk\com\html;

		$d = self::$d;

		if($vars !== FALSE)
		{
			if(is_array($vars))
			{
				$d = array_merge($d,$vars);
			}
			else
			{
				$method = '\\sox\\'.str_replace('/','\\',self::$base).'\\vars\\'.self::uri(1).'::'.self::uri(2);
				$params = array_slice(self::uri(),3);

				$result = empty($params) ? $method() : call_user_func_array($method,$params);

				if($result) $d = array_merge($d,$result);

				if($vars)
				{
					if($key)
					{
						self::$d[$vars] = $result[$key];
					}
					else
					{
						self::$d[$vars] = $result;
					}
				}
			}
		}

		ob_start();

		include self::$view_dir.$path.'.php';

		$view = ob_get_contents();

		ob_clean();

		echo $view;
	}

	static function import($path,$vars = FALSE)
	{
		self::render($path,$vars);
	}

	static function url($uri = '')
	{
		if(url() == '/')
		{
			return url(self::$hide_file ? $uri : self::$file.'/'.$uri);
		}
		else
		{
			return url(self::$file.'/'.$uri);
		}
	}

	static function uri($key = 0,$def = '')
	{
		return http_uri($key,$def,self::$file);
	}
}

?>