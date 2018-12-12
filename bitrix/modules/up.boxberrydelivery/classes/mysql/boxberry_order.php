<?
class CBoxberryOrder {
	
	function err_mess()
	{
		$module_id = "up.boxberrydelivery";
		include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module_id."/install/version.php");
		return "<br>Module: ".$module_id." (".$arModuleVersion["VERSION"].")<br>Class: CBoxberryOrder<br>File: ".__FILE__;
	}
	
    function Add($arFields)
    {
        if (self::GetByOrderId($arFields['ORDER_ID'])){
			return false;
		}
		global $DB;
        $err_mess = (self::err_mess())."<br>Function: Add<br>Line: ";
		$arInsert = $DB->PrepareInsert("b_boxberry_order", $arFields);
		$strSql = "INSERT INTO b_boxberry_order(".$arInsert[0].") VALUES(".$arInsert[1].")";
		$DB->Query($strSql, false, $err_mess.__LINE__);       
		$ID = intval($DB->LastID());
		return $ID;
    } 
    
    function GetList($by = "ORDER_ID", $order = "ASC", $arFilter = array())
    {	
    	global $DB;
    	$where = "";
    	
    	$arOrderFields = array(
    		"ID",
    		"ORDER_ID",
    		"DATE_CHANGE",
    		"LID",
    		"TRACKING_CODE",
    		"STATUS",
    		"STATUS_TEXT",
    		"STATUS_DATE",
    		"SEND_REQUEST",
    		"SEND_REQUEST_DATE",
    		"SEND_PDF_LINK",
    		"CHECK_REQUEST",
    		"CHECK_REQUEST_DATE",
    		"CHECK_PDF_LINK",
    		"ERRORS",
    		"STATUS_HISTORY"
    	);
    	
    	if(!in_array($by, $arOrderFields))
    		$by = "DATE_CHANGE";
    	
		if(count($arFilter) > 0)
		{
			foreach($arFilter as $field => $value)
			{
				if(in_array($field, $arOrderFields))
				{
					if(is_array($value))
					{	
						$strValue = '';
						
						foreach($value as $val)
						{	
							if($val != end($value))
								$strValue .= "'".$val."',";
							else
								$strValue .= "'".$val."'";
						}
						
						if($value != end($arFilter))
						{
							$where .= $field." in (".$strValue.") AND ";
						}
						else
						{
							$where .= $field." in (".$strValue.")";
						}
					}
					else
					{
						if($value != end($arFilter))
						{
							$where .= $field." = '".$value."' AND ";
						}
						else
						{
							$where .= $field." = '".$value."'";
						}
					}

				}
			}
		}
		
		$strSql = "
			SELECT 
				*
			FROM b_boxberry_order
			".(strlen($where) > 1 ? "WHERE ".$where : "")."
			GROUP BY ORDER_ID
			ORDER BY
				$by $order
			";
		$respond = $DB->Query($strSql, true);
		
		return $respond;
	}
    
    function Update($ORDER_ID, $arFields)
    {
    	if(intval($ORDER_ID) <= 0) return false;
    	
    	if(!$arFields['DATE_CHANGE']) $arFields['DATE_CHANGE'] = date('d.m.Y H:i:s');
    	
        global $DB;  
        $strUpdate = $DB->PrepareUpdate("b_boxberry_order", $arFields);  
        $strSql = "UPDATE b_boxberry_order SET ".$strUpdate." WHERE ORDER_ID='".$DB->ForSql(intval($ORDER_ID))."'";
        
        $res = $DB->Query($strSql, true);
        
        if($res == false) {
            return false;    
        }
        else {
            return $ID;     
        }  
    } 
    
    function GetByOrderId($ORDER_ID)
    {
    	if(intval($ORDER_ID) <= 0) return false;
    	
        global $DB;    
        $strSql = "SELECT * FROM b_boxberry_order WHERE ORDER_ID='".$DB->ForSql(intval($ORDER_ID))."' ORDER BY `ID` DESC";
        $res = $DB->Query($strSql, true);
		if (!empty($res)){
			$arRes = $res->Fetch();    
			return $arRes;        
		}
		
		return false;
		
        
    }       
    
    function Delete($ORDER_ID)
    {	
		if(intval($ORDER_ID) <= 0) return false;
		
		global $DB;
		$err_mess = (self::err_mess())."<br>Function: Add<br>Line: ";
		$strSql = "DELETE FROM b_boxberry_order WHERE ORDER_ID='".$ORDER_ID."'";
		
		$respond = $DB->Query($strSql, false, $err_mess.__LINE__);
		
		return $respond->result;
		
	}         
}
?>