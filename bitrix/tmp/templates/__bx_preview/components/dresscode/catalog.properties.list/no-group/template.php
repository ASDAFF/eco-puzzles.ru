<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<?$this->setFrameMode(true);?>
<?$propertyCounter=0;?>
<?if(!empty($arResult["DISPLAY_PROPERTIES"])):?>
    <div class="elementProperties">
        <div class="headingBox">
            <div class="heading">
                <?=GetMessage("CATALOG_ELEMENT_CHARACTERISTICS_SHORT");?>
            </div>
            <div class="moreProperties">
                <a href="#" class="morePropertiesLink"><?=GetMessage("CATALOG_ELEMENT_MORE_PROPERTIES")?></a>
            </div>
        </div>
        <div class="propertyList">
            <?foreach ($arResult["DISPLAY_PROPERTIES"] as $ip => $arProperty):?>
                <?if(!empty($arProperty["DISPLAY_VALUE"]) && ++$propertyCounter <= $arParams["COUNT_PROPERTIES"]):?>
                    <?if(gettype($arProperty["DISPLAY_VALUE"]) == "array"){
                        $arProperty["DISPLAY_VALUE"] = implode(" / ", $arProperty["DISPLAY_VALUE"]);
                    }?>
                    <div class="propertyTable">
                        <div class="propertyName"><?=preg_replace("/\[.*\]/", "", $arProperty["NAME"])?></div>
                        <div class="propertyValue">
                            <?if($arProperty["PROPERTY_TYPE"] == "E" || $arProperty["PROPERTY_TYPE"] == "S"):?>
                                <?=$arProperty["DISPLAY_VALUE"]?>
                            <?else:?>
                                <?if($arProperty["FILTRABLE"] =="Y" && !empty($arProperty["VALUE_ENUM_ID"]) && $arProperty["FROM_SKU"] != "Y" && !empty($arResult["LAST_SECTION"])):?>
                                    <a href="<?=$arResult["LAST_SECTION"]["SECTION_PAGE_URL"]?>?arrFilter_<?=$arProperty["ID"]?>_<?=abs(crc32($arProperty["VALUE_ENUM_ID"]))?>=Y&amp;set_filter=Y" class="analog">
                                <?endif;?><?=$arProperty["DISPLAY_VALUE"]?>
                                <?if($arProperty["FILTRABLE"] == "Y" && !empty($arProperty["VALUE_ENUM_ID"]) && $arProperty["FROM_SKU"] != "Y"):?>
                                    </a>
                                <?endif;?>
                            <?endif;?>
                        </div>
                    </div>
                <?endif;?>
            <?endforeach;?>
        </div>
    </div>
<?endif;?>                  