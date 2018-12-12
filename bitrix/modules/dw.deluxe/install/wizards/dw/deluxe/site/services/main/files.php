<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if (!defined("WIZARD_SITE_ID") || !defined("WIZARD_SITE_DIR"))
	return;

function ___writeToAreasFile($path, $text)
{
	//if(file_exists($fn) && !is_writable($abs_path) && defined("BX_FILE_PERMISSIONS"))
	//	@chmod($abs_path, BX_FILE_PERMISSIONS);

	$fd = @fopen($path, "wb");
	if(!$fd)
		return false;

	if(false === fwrite($fd, $text))
	{
		fclose($fd);
		return false;
	}

	fclose($fd);

	if(defined("BX_FILE_PERMISSIONS"))
		@chmod($path, BX_FILE_PERMISSIONS);
}

if (COption::GetOptionString("main", "upload_dir") == "")
	COption::SetOptionString("main", "upload_dir", "upload");

$templateID = $wizard->GetVar("wizTemplateID");

$publicPath = ($templateID == "dresscode") ? "v1" : "v2";

if(COption::GetOptionString("eshop", "wizard_installed", "N", WIZARD_SITE_ID) == "N" || WIZARD_INSTALL_DEMO_DATA)
{
	if(file_exists(WIZARD_ABSOLUTE_PATH."/site/public/".$publicPath."/"))
	{
		CopyDirFiles(
			WIZARD_ABSOLUTE_PATH."/site/public/".$publicPath."/",
			WIZARD_SITE_PATH,
			$rewrite = true,
			$recursive = true,
			$delete_after_copy = false
		);
	}
}

if(COption::GetOptionString("eshop", "wizard_installed", "N", WIZARD_SITE_ID) == "Y" && !WIZARD_INSTALL_DEMO_DATA)
	return;

WizardServices::PatchHtaccess(WIZARD_SITE_PATH);

WizardServices::ReplaceMacrosRecursive(WIZARD_SITE_PATH."news/", Array("SITE_DIR" => WIZARD_SITE_DIR));
WizardServices::ReplaceMacrosRecursive(WIZARD_SITE_PATH."catalog/", Array("SITE_DIR" => WIZARD_SITE_DIR));
WizardServices::ReplaceMacrosRecursive(WIZARD_SITE_PATH."collection/", Array("SITE_DIR" => WIZARD_SITE_DIR));
WizardServices::ReplaceMacrosRecursive(WIZARD_SITE_PATH."services/", Array("SITE_DIR" => WIZARD_SITE_DIR));
WizardServices::ReplaceMacrosRecursive(WIZARD_SITE_PATH."brands/", Array("SITE_DIR" => WIZARD_SITE_DIR));
WizardServices::ReplaceMacrosRecursive(WIZARD_SITE_PATH."search/", Array("SITE_DIR" => WIZARD_SITE_DIR));
WizardServices::ReplaceMacrosRecursive(WIZARD_SITE_PATH."personal/cart/order/", Array("SITE_DIR" => WIZARD_SITE_DIR));


WizardServices::ReplaceMacrosRecursive(WIZARD_SITE_PATH."blog/", Array("SITE_DIR" => WIZARD_SITE_DIR));
WizardServices::ReplaceMacrosRecursive(WIZARD_SITE_PATH."sales/", Array("SITE_DIR" => WIZARD_SITE_DIR));
WizardServices::ReplaceMacrosRecursive(WIZARD_SITE_PATH."stock/", Array("SITE_DIR" => WIZARD_SITE_DIR));

CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/index.php", Array("SITE_DIR" => WIZARD_SITE_DIR));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/.section.php", array("SITE_DESCRIPTION" => htmlspecialcharsbx($wizard->GetVar("siteMetaDescription"))));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/.section.php", array("SITE_KEYWORDS" => htmlspecialcharsbx($wizard->GetVar("siteMetaKeywords"))));

copy(WIZARD_THEME_ABSOLUTE_PATH."/favicon.ico", WIZARD_SITE_PATH."favicon.ico");

$arUrlRewrite = array(); 
if (file_exists(WIZARD_SITE_ROOT_PATH."/urlrewrite.php"))
{
	include(WIZARD_SITE_ROOT_PATH."/urlrewrite.php");
}

$arNewUrlRewrite = array(
	array(
		"CONDITION"	=>	"#^".WIZARD_SITE_DIR."news/#",
		"RULE"	=>	"",
		"ID"	=>	"bitrix:news",
		"PATH"	=>	 WIZARD_SITE_DIR."news/index.php",
	),
	array(
		"CONDITION"	=>	"#^".WIZARD_SITE_DIR."catalog/#",
		"RULE"	=>	"",
		"ID"	=>	"bitrix:catalog",
		"PATH"	=>	 WIZARD_SITE_DIR."catalog/index.php",
	),
	array(
		"CONDITION"	=>	"#^".WIZARD_SITE_DIR."survey/#",
		"RULE"	=>	"",
		"ID"	=>	"bitrix:news",
		"PATH"	=>	 WIZARD_SITE_DIR."survey/index.php",
	),
	array(
		"CONDITION"	=>	"#^".WIZARD_SITE_DIR."brands/#",
		"RULE"	=>	"",
		"ID"	=>	"bitrix:news",
		"PATH"	=>	 WIZARD_SITE_DIR."brands/index.php",
	),
	array(
		"CONDITION"	=>	"#^".WIZARD_SITE_DIR."stock/#",
		"RULE"	=>	"",
		"ID"	=>	"bitrix:news",
		"PATH"	=>	 WIZARD_SITE_DIR."stock/index.php",
	),
);

foreach ($arNewUrlRewrite as $arUrl)
{
	if (!in_array($arUrl, $arUrlRewrite))
	{
		CUrlRewriter::Add($arUrl);
	}
}

	$UTF8 = true;

	// Обязательные проверки
	
	if (!defined('BX_UTF') || BX_UTF !== true)
		$UTF8 = false;

	if ($UTF8 !== false && !function_exists('mb_convert_encoding'))
		Error('Не доступна библиотека mbstring');

	if ($UTF8 !== false && ini_get('mbstring.func_overload') != 2)
		Error('Значение параметра mbstring.func_overload не равно 2');

	define('START_PATH', $_SERVER['DOCUMENT_ROOT']);

		if($UTF8 !== false){
			Search(START_PATH);
		}



	function Search($path)
	{

		if (defined('SKIP_PATH') && !defined('FOUND')) // проверим, годится ли текущий путь
		{
			if (0 !== strpos(SKIP_PATH, dirname($path))) // отбрасываем имя или идём ниже 
				return;

			if (SKIP_PATH == $path) // путь найден, продолжаем искать текст
				define('FOUND', true);
		}

		if (is_dir($path)) // dir
		{
			$dir = opendir($path);
			while($item = readdir($dir))
			{
				if ($item == '.' || $item == '..')
					continue;

				Search($path.'/'.$item);
			}
			closedir($dir);
		}
		else // file
		{
			if (!defined('SKIP_PATH') || defined('FOUND'))
			{
				if ((substr($path, -3) == '.js' || substr($path, -4) == '.txt' || substr($path,-4) == '.php' || basename($path) == 'trigram') && $path != __FILE__)
					Process($path);
			}
		}
	}

	function Process($file)
	{
		
		$content = file_get_contents($file);

		if (GetStringCharset($content) != 'cp1251')
			return;

		if ($content === false)
			Error('Не удалось прочитать файл: '.$file);

		if (file_put_contents($file, mb_convert_encoding($content, 'utf8', 'cp1251')) === false)
			Error('Не удалось сохранить файл: '.$file);

	}

	function GetStringCharset($str)
	{ 
		global $APPLICATION;
		if (preg_match("/[\xe0\xe1\xe3-\xff]/",$str))
			return 'cp1251';
		$str0 = $APPLICATION->ConvertCharset($str, 'utf8', 'cp1251');
		if (preg_match("/[\xe0\xe1\xe3-\xff]/",$str0,$regs))
			return 'utf8';
		return 'ascii';
	}

	function Error($text)
	{
		die('<font color=red>'.$text.'</font>');
	}
?>