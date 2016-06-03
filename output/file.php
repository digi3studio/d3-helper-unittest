<?php
/**
 * Created by PhpStorm.
 * User: Digi3
 * Date: 6/7/2015
 * Time: 13:23
 */

class Helper_Output_File{
	public static function clean($str){
		//extract body
		$str = Helper_Unittest::extract_tag('body',$str);
		//remove share_link which not deploy in live server
		$str = str_replace(Helper_Unittest::extract_by('<div id="share_link">','<\/div>',$str,TRUE),'',$str);
		//trim linebreak, window and mac server is using different linkbreak
		$str = Helper_Unittest::trim_linebreak($str);
		//trim space, remove tab and double space.
		$str = Helper_Unittest::trim_space($str);
		//trim different page id in navigation
		$str = Helper_Output_File::trimNavPageId($str);
		//remove random string
		$str = preg_replace(
			'/\?r=[0-9]*/',
			'',
			$str
		);
		//trim baidu tracking
		$str = Helper_Output_File::trimBaidu($str);
		//trim late loading js
		$str = Helper_Output_File::trimLateLoadingJs($str);
		return $str;
	}

	public static function getRemoteContent($page){
		$opts = array(
			'http'=>array(
				'method'=>'GET',
				'header'=>'User-Agent: '.$_SERVER['HTTP_USER_AGENT'].'\r\n'
			)
		);
		$context = stream_context_create($opts);
		return file_get_contents($_SERVER['REMOTE_TESTING'].$page,false,$context);
	}

	public static function getSourceBody($page){
		$src_body = Helper_Output_File::getRemoteContent($page);
		return Helper_Output_File::clean($src_body);
	}

	public static function getOutputBody($page){
		Helper_Unittest::make_request($page);
		$local = Helper_Unittest::render();
		return Helper_Output_File::clean($local);
	}

	public static function trimNavPageId($str){
		return preg_replace( '/<li class="page_nav[^"]*" id="[^"]*">/', '<li class="page_nav" id="---">', $str);
	}

	public static function trimBaidu($str){
		$str = preg_replace( '/(?:<div id="tracking_baidu">)(.*)<\/div>/isU', '<!-- TRIM: baidu tracking -->', $str);
		return $str;
	}

	public static function trimLateLoadingJs($str){
		$str = preg_replace( '/(?:<div id="scripts">)(.*)<\/div>/isU', '<!-- TRIM: late loading javascript -->', $str);
		return $str;
	}
}