<?
//buffer modify
class DwBuffer{

    //functions
    public static function modifyBuffer(&$buffer){

    	//browser caching bypass
    	if(defined("SITE_TEMPLATE_PATH")){
			$buffer = str_replace("favicon.ico", "favicon.ico?v=".filemtime($_SERVER["DOCUMENT_ROOT"].SITE_TEMPLATE_PATH."/images/favicon.ico"), $buffer);
			$buffer = str_replace("logo.png", "logo.png?v=".filemtime($_SERVER["DOCUMENT_ROOT"].SITE_TEMPLATE_PATH."/images/logo.png"), $buffer);
	  		$buffer = str_replace("<script type=\"text/javascript\"", "<script", $buffer);
  		}
    	return $buffer;

    }

}
?>