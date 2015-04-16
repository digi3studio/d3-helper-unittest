<?php defined('SYSPATH') OR die('No direct access allowed.');

class Helper_Unittest {
	public static function make_request($uri,$method='GET'){
		$strings = explode('?',$uri);

		$_SERVER['REQUEST_URI'] = $uri;//'/hk_en/2015?hello=1&world=2';
		$_SERVER['QUERY_STRING']= isset($strings[1])?$strings[1]:'';//'hello=1&world=2';
		$_SERVER['PATH_INFO']   = $strings[0];//$uri;//'/hk_en/2015';

		if($method=='GET'){
			$_REQUEST = array();
			parse_str($_SERVER['QUERY_STRING'],$_REQUEST);
			$_GET = $_REQUEST;
		}


//		$_SERVER['PHP_SELF']= '/index.php'.$uri;///hk_en/2015';
//		$_SERVER['REDIRECT_URL']= $uri;//'/hk_en/2015';
//		$_SERVER['PATH_TRANSLATED']= 'redirect:\index.php\hk_en\2015\2015';
	}

	public static function make_post_vars($name,$value){
		$_POST[$name] = $value;
		$_REQUEST[$name] = $value;
	}

	public static function tearDown(){
		Helper_Unittest::make_request('');
		Request::$instance = NULL;
		if(class_exists ('Helper_Browser')){
			Helper_Browser::$info = NULL;
		}
		Helper_Template::instance()->title       = NULL;
		Helper_Template::instance()->scripts 	 = array();
		Helper_Template::instance()->ext_scripts = array();
		Helper_Template::instance()->ext_styles  = array();
		Helper_Template::instance()->styles 	 = array();
		Helper_Template::instance()->metatags	 = array();

		$_REQUEST = array();
		$_POST = array();
		$_GET = array();
	}

	public static function render(){
		/**
		 * Execute the main request. A source of the URI can be passed, eg: $_SERVER['PATH_INFO'].
		 * If no source is specified, the URI will be automatically detected.
		 */

		try{
			//echo
			$output_result =
				Request::instance()
					->execute()
					->send_headers()
					->response;

			if(Request::instance()->param('format')=='php'){
				$output_result .= '<!-- using Controller '.Request::instance()->controller.' and action '.Request::instance()->action.' -->';
			}
		}catch(ReflectionException $e){
			//kohana set status to 404 if error.
			//restore the status to 200;
			Request::instance()->status=200;

			$request 	= Request::instance();
			$city 		= $request->param('city');
			$campaign 	= $request->param('campaign');
			$language	= $request->param('language');
			$page		= $request->controller;

			$city_lang = (empty($city)?'asia':$city).((empty($language)?'':('_'.$language)));

			//echo
			$output_result = Request::factory($city_lang.'/'.$campaign.'/page/static/'.$page)
				->execute()
				->send_headers()
				->response;

			if(Request::instance()->param('format')=='php'){
				$output_result .= '<!-- Controller '.$request->controller.' or action '.$request->action.' not found, exception handle by bootstrap and page controller -->';
			}
		}
		return $output_result;
	}

	public static function save_result($file,$txt)
	{
		$txt = str_replace('><','>'.PHP_EOL.'<',$txt);
		$save_path = '../phpunit/result/'.$file;

		Helper_System::mkdir_recursive(
			dirname(DOCROOT.$save_path),
			0777,
			true
		);

		file_put_contents(DOCROOT.$save_path,$txt);
	}

	public static function extract_tag($tag,$html,$isIncludeTag=TRUE)
	{
		$pattern = '/(?:<'.$tag.'[^>]*>)(.*)<\/'.$tag.'>/isU';
		preg_match($pattern,$html,$matches);
		return $matches[($isIncludeTag)?0:1];
	}

	public static function extract_by($start,$end,$html,$isIncludeTag=TRUE)
	{
		$pattern = '/(?:'.$start.')(.*)'.$end.'/isU';
		preg_match($pattern,$html,$matches);
		if(count($matches)==0)return '';
		return $matches[($isIncludeTag)?0:1];
	}

	public static function trim_linebreak(&$str){
		return preg_replace( "/\r|\n/", "", $str);
	}

	public static function trim_space(&$str){
		$str = str_replace(' ',' ',$str);
		$str = preg_replace('/\s+/',' ',$str);
		$str = str_replace('> <','><',$str);
		return $str;
	}

	public static function trim_absolute_path(&$str){
		return preg_replace('/"http:\/\/[^\/]*\//','"ABSOLUTE_PATH/',$str);
	}

	public static function remove_node(DOMDocument &$dom, $id)
	{
		$element = $dom->getElementById($id);
		$element->parentNode->removeChild($element);
	}
}