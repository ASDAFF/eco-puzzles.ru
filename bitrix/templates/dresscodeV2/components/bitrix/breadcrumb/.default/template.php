<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(empty($arResult))
	return "";
	
// WebSEO.kz Michael: заменил микроразметку хлебных крошек на Schema.org
//$strReturn = '<div id="breadcrumbs"><ul>';
$strReturn = '<div id="breadcrumbs"><ul itemscope itemtype="http://schema.org/BreadcrumbList">';
$wsmmlinkpos = 0;

$num_items = count($arResult);
for($index = 0, $itemSize = $num_items; $index < $itemSize; $index++)
{
	$title = htmlspecialcharsex($arResult[$index]["TITLE"]);

	if($arResult[$index]["LINK"] <> "" && $index != $itemSize-1)
		//$strReturn .= '<li itemscope itemtype="http://data-vocabulary.org/Breadcrumb"><a href="'.$arResult[$index]["LINK"].'" title="'.$title.'" itemprop="url"><span itemprop="title">'.$title.'</span></a></li><li><span class="arrow"> &bull; </span></li>';
		$strReturn .= '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a itemprop="item" href="'.$arResult[$index]["LINK"].'" title="'.$title.'"><span itemprop="name">'.$title.'</span></a><meta itemprop="position" content="'.++$wsmmlinkpos.'"></li><li><span class="arrow"> &bull; </span></li>';
	else
		$strReturn .= '<li><span class="changeName">'.$title.'</span></li>';
}

$strReturn .= '</ul></div>';

return $strReturn;
?>