<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<?$this->setFrameMode(true);?>
<?if(!empty($arResult["DISPLAY_PROPERTIES"])):?>
    <div id="elementProperties">
        <span class="heading"><?=GetMessage("SPECS")?></span>
		<table class="stats">
			<tbody>
                <?foreach($arResult["DISPLAY_PROPERTIES_GROUP"] as $arProp):?>
                    <?if((!empty($arProp["VALUE"]) || !empty($arProp["LINK"])) && $arProp["SORT"] <= 5000):?>
                        <?
                            $set++;
                            $PROP_NAME = $arProp["NAME"];
                        ?>
                        <?if(!empty($arProp["VALUE"]) && gettype($arProp["VALUE"]) == "array"){$arProp["VALUE"] = implode(" / ", $arProp["VALUE"]); $arProp["FILTRABLE"] = false;}?>
                        <?if(preg_match("/\[.*\]/", trim($arProp["NAME"]), $res, PREG_OFFSET_CAPTURE)):?>
                            <?$arProp["NAME"] = substr($arProp["NAME"], 0, $res[0][1]);?>
                            <?if($res[0][0] != $arResult["OLD_CAP"]):?>
                                <?
                                    $arResult["OLD_CAP"] = $res[0][0];
                                    $set = 1;
                                ?>
                                <tr class="cap">
                                    <td colspan="2"><?=substr($res[0][0], 1, -1)?></td>
                                    <td class="right"></td>
                                </tr>
                            <?endif;?>
                            <tr<?if($set % 2):?> class="gray"<?endif;?>>
                                <td class="name"><span><?=preg_replace("/\[.*\]/", "", trim($PROP_NAME))?></span><?if(!empty($arProp["HINT"])):?><a href="#" class="question" title="<?=$arProp["HINT"]?>" data-description="<?=$arProp["HINT"]?>"></a><?endif;?></td>
                                <td>
                                    <?=$arProp["DISPLAY_VALUE"]?>
                                </td>
                                <td class="right">
                                    <?if($arProp["FILTRABLE"] =="Y" && !is_array($arProp["VALUE"]) && !empty($arProp["VALUE_ENUM_ID"]) && !empty($arResult["LAST_SECTION"])):?><a href="<?=$arResult["LAST_SECTION"]["SECTION_PAGE_URL"]?>?arrFilter_<?=$arProp["ID"]?>_<?=abs(crc32($arProp["VALUE_ENUM_ID"]))?>=Y&amp;set_filter=Y" class="analog"><?=GetMessage("OTHERITEMS")?></a><?endif;?>
                                </td>
                            </tr>
                        <?else:?>
                            <? $arSome[] = $arProp?> 
                        <?endif;?>
                    <?endif;?>
                <?endforeach;?>
                <?if(!empty($arSome)):?>
                <?$set = 0;?>
                     <tr class="cap">
                        <td colspan="3"><?=GetMessage("CHARACTERISTICS")?></td>
                    </tr>
                    <?foreach($arSome as $arProp):?>
                        <?$set++?>
                        <tr<?if($set % 2 ):?> class="gray"<?endif;?>>
                            <td class="name"><span><?=$arProp["NAME"]?></span><?if(!empty($arProp["HINT"])):?><a href="#" class="question" title="<?=$arProp["HINT"]?>" data-description="<?=$arProp["HINT"]?>"></a><?endif;?></td>
                            <td>
                                    <?=$arProp["DISPLAY_VALUE"]?>
                            </td>
                            <td class="right">
                                <?if($arProp["FILTRABLE"] =="Y" && !is_array($arProp["VALUE"]) && !empty($arProp["VALUE_ENUM_ID"]) && !empty($arResult["LAST_SECTION"])):?>
                                    <a href="<?=$arResult["LAST_SECTION"]["SECTION_PAGE_URL"]?>?arrFilter_<?=$arProp["ID"]?>_<?=abs(crc32($arProp["VALUE_ENUM_ID"]))?>=Y&amp;set_filter=Y" class="analog"><?=GetMessage("OTHERITEMS")?></a>
                                <?endif;?>
                            </td>
                        </tr>
                    <?endforeach;?>
                <?endif;?>
            </tbody>
		</table>
	</div>
<?endif;?>				