<?
//remove default h1 tag
if(!empty($arResult["BANNER"])){
	AddEventHandler("main", "OnEndBufferContent", "bufferControl");
	function bufferControl(&$buffer){
		preg_match_all("'<h1[^>]*?>.*?</h1>'si", $buffer, $findTags, PREG_PATTERN_ORDER);
		if(!empty($findTags)){
			if(count($findTags[0]) > 1){
				unset($findTags[0][0]);
				$buffer = str_replace($findTags[0], "", $buffer);
			}
		}
	}
}
?>