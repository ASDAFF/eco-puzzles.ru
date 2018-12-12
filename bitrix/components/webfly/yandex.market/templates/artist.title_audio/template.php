<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
if ($arParams["USE_SITE"] == "Y")
        $server_name = $arParams["SITE"];
    else
        $server_name = $_SERVER["SERVER_NAME"];?>
<?if ($arParams["ECHO_ADMIN_INFO"] == "Y") {
    $stepStartTime = date("d.m.Y H:i:s");
            file_put_contents("ym_log.txt", GetMessage("TEMPLATE_WORK"), FILE_APPEND | LOCK_EX);
            file_put_contents("ym_log.txt", GetMessage("SCRIPT_START") . $stepStartTime . "\r\n", FILE_APPEND | LOCK_EX);
        }?>
<? if (!$arResult["SAVE_IN_FILE"]): ?>
<? echo '<?xml version="1.0" encoding="' . LANG_CHARSET . '"?>'; ?>
<!DOCTYPE yml_catalog SYSTEM "shops.dtd">
<yml_catalog date="<?= $arResult["DATE"] ?>">
        <shop>
            <name><?= $arResult["SITE"] ?></name>
            <company><?= $arResult["COMPANY"] ?></company>
            <url><? if ($arParams ["HTTPS_CHECK"] == "Y"): ?><?= "https://" .$server_name ?><? else: ?><?= "http://" .$server_name ?><? endif ?></url>

            <currencies>
                <? if (!empty($arResult["CURRENCIES"])): ?>
                    <? foreach ($arResult["CURRENCIES"] as $k => $cur): ?>
                        <? if (!empty($cur) && $cur != 'RUR' && (!empty($arResult["WF_AMOUNTS"][$cur]) || $cur == 'RUB')): ?><currency id="<?= $cur ?>"<? if ($cur == 'RUB'): ?> rate="1"<? else: ?> rate="<?= $arResult["WF_AMOUNTS"][$cur] ?>"<? endif; ?>/><? endif; ?>
                    <? endforeach; ?>
                <? else: ?>
                    <currency id="<?= $arParams["CURRENCY"] ?>" rate="1"/>
                <? endif; ?>
            </currencies>

            <categories>
                <? foreach ($arResult["CATEGORIES"] as $arCategory): ?>
                    <category id="<?= $arCategory["ID"] ?>"<?
                    if ($arCategory["PARENT"])
                        echo ' parentId="' . $arCategory['PARENT'] . '"';
                    ?>><?= $arCategory["NAME"] ?></category>
                          <? endforeach; ?>
            </categories>
            <? if ($arParams["LOCAL_DELIVERY_COST"] != "" and empty($arResult["DELIVERY_OPTION_SHOP"])): ?>
                <local_delivery_cost><?= $arParams["LOCAL_DELIVERY_COST"] ?></local_delivery_cost>
            <? endif ?>
            <? if (!empty($arResult["DELIVERY_OPTION_SHOP"])): ?>
                <delivery-options>
                    <? foreach ($arResult["DELIVERY_OPTION_SHOP"] as $delK => $delV): ?>
                        <option cost="<?= $delV[0] ?>" days="<?= $delV[1] ?>"<? if ($delV[2] != ""): ?> order-before="<?= $delV[2] ?>"<? endif ?>/>
                    <? endforeach ?>
                </delivery-options>
            <? endif ?>
            <? if ($arResult["CPA_SHOP"]=="1" || $arResult["CPA_SHOP"]=="0"): ?>
                <cpa><?= $arResult["CPA_SHOP"] ?></cpa>
            <? endif ?>
            <? if ($arParams["ADULT_ALL"] == "Y"): ?>
                <adult>true</adult>
            <? endif ?>
            <offers>
                <? foreach ($arResult["OFFER"] as $arOffer): ?>
                    <offer type="artist.title" id="<?= $arOffer["ID"] ?>" available="<?= $arOffer["AVAIBLE"] ?>"<? if (!empty($arOffer["BID"])): ?> bid="<?= $arOffer["BID"] ?>"<? endif ?><? if (!empty($arOffer["CBID"])): ?> cbid="<?= $arOffer["CBID"] ?>"<? endif ?><? if (!empty($arOffer["FEE"])): ?> fee="<?= $arOffer["FEE"] ?>"<? endif ?>>
                        <url><?= $arOffer["URL"] ?></url>
                        <price><?= $arOffer["PRICE"] ?></price>
                        <?if (!empty($arOffer["OLD_PRICE"])):?>
                        <oldprice><?= $arOffer["OLD_PRICE"] ?></oldprice>
                        <?endif?>
                        <? if (!empty($arOffer["PURCHASE_PRICE"])): ?>
                            <purchase_price><?= $arOffer["PURCHASE_PRICE"] ?></purchase_price>
                        <? endif ?>
                        <currencyId>
                            <? if (!empty($arOffer["CURRENCY"])): ?>
                                <?= $arOffer["CURRENCY"] ?>
                            <? else: ?>
                                <?= $arParams["CURRENCY"] ?>
                            <? endif; ?>
                        </currencyId>

                        <categoryId><?= $arOffer["CATEGORY"] ?></categoryId>
                        <? if ($arOffer["PICTURE"]): ?><picture><?= $arOffer["PICTURE"] ?></picture><? endif ?>
                        <? if ($arOffer["STORE_OFFER"] == "true"): ?><store>true</store><? endif ?>
                        <? if ($arOffer["STORE_OFFER"] == "false"): ?><store>false</store><? endif ?>
                        <? if ($arOffer["STORE_PICKUP"] == "true"): ?><pickup>true</pickup><? endif ?>
                        <? if ($arOffer["STORE_PICKUP"] == "false"): ?><pickup>false</pickup><? endif ?>
                        <? if ($arOffer["STORE_DELIVERY"] == "true"): ?><delivery>true</delivery><? endif ?>
                        <? if ($arOffer["STORE_DELIVERY"] == "false"): ?><delivery>false</delivery><? endif ?>
                        <? if (is_numeric($arOffer["LOCAL_DELIVERY_COST_OFFER"]) and empty($arOffer["DELIVERY_OPTIONS_EX"])): ?><local_delivery_cost><?= $arOffer["LOCAL_DELIVERY_COST_OFFER"] ?></local_delivery_cost><? endif ?>
                        <? if (!empty($arOffer["DELIVERY_OPTIONS_EX"]) and count($arOffer["DELIVERY_OPTIONS_EX"]) > 0): ?>
                            <delivery-options>
                                <? foreach ($arOffer["DELIVERY_OPTIONS_EX"] as $optK => $opt): ?>
                                    <? if ($opt[0] != ""): ?>
                                        <option cost="<?= $opt[0]; ?>" days="<?= $opt[1] ?>"<? if ($opt[2] != ""): ?> order-before="<?= $opt[2] ?>"<? endif ?>/>
                                    <? endif ?>
                                <? endforeach ?>
                            </delivery-options>
                        <? endif ?>
                        <? if (!empty($arOffer["OUTLETS"]) and count($arOffer["OUTLETS"]) > 0): ?>
                            <outlets>
                                <? foreach ($arOffer["OUTLETS"] as $outK => $out): ?>
                                    <outlet id="<?= $out[0] ?>" instock="<?= $out[1] ?>"<? if ($out[2] != ""): ?> booking="<?= $out[2] ?>"<? endif ?>/>
                                <? endforeach; ?>
                            </outlets>
                        <? endif; ?>
                        <? if (!empty($arOffer["PREFIX_PROP"])): ?>
                            <typePrefix><?= $arOffer["PREFIX_PROP"] ?></typePrefix>
                        <? endif; ?>
                        <title><?= $arOffer["MODEL"] ?></title>
                        <? if ($arOffer["DISPLAY_PROPERTIES"][$arParams["ARTIST"]]["DISPLAY_VALUE"]): ?><artist><?= $arOffer["DISPLAY_PROPERTIES"][$arParams["ARTIST"]]["DISPLAY_VALUE"] ?></artist><? endif ?>				
                        <? if ($arOffer["DISPLAY_PROPERTIES"][$arParams["YEAR"]]["DISPLAY_VALUE"]): ?><year><?= $arOffer["DISPLAY_PROPERTIES"][$arParams["YEAR"]]["DISPLAY_VALUE"] ?></year><? endif ?>								
                        <? if ($arOffer["DISPLAY_PROPERTIES"][$arParams["MEDIA"]]["DISPLAY_VALUE"]): ?><media><?= $arOffer["DISPLAY_PROPERTIES"][$arParams["MEDIA"]]["DISPLAY_VALUE"] ?></media><? endif ?>								
                        <? if ($arOffer["DISPLAY_PROPERTIES"][$arParams["BARCODE"]]["DISPLAY_VALUE"]): ?><barcode><?= $arOffer["DISPLAY_PROPERTIES"][$arParams["BARCODE"]]["DISPLAY_VALUE"] ?></barcode><? endif ?>								
                        <? if (!empty($arOffer["DESCRIPTION"]) or ! empty($arOffer["DOP_PROPS"])): ?>
                            <description><? if (!empty($arOffer["DOP_PROPS"])): ?><?= $arOffer["DOP_PROPS"] ?>. <? endif ?><?= $arOffer["DESCRIPTION"] ?></description>
                        <? endif; ?>
                        <? if ($arOffer["ADULT"] == "true"): ?><adult>true</adult><? endif ?>
                        <? if (is_numeric($arOffer["AGE_CATEGORY"])): ?>
                            <age unit="<?= $arParams["AGE_CATEGORY_UNIT"] ?>"><?= $arOffer["AGE_CATEGORY"] ?></age>
                        <? endif ?>
                        <? if (is_numeric($arOffer["CPA_OFFERS"])): ?>
                            <cpa><?= $arOffer["CPA_OFFERS"] ?></cpa>
                        <? endif ?>
                        <? if (!empty($arOffer["RECOMMENDATION"])): ?>
                            <rec><?= $arOffer["RECOMMENDATION"] ?></rec>
                        <? endif ?>
                        <? if ($arOffer["EXPIRY"]): ?>
                            <expiry><?= $arOffer["EXPIRY"] ?></expiry>
                        <? endif ?>
                        <? if ($arOffer["WEIGHT"]): ?>
                            <weight><?= $arOffer["WEIGHT"] ?></weight>
                        <? endif ?>
                        <? if ($arOffer["DIMENSIONS"]): ?>
                            <dimensions><?= $arOffer["DIMENSIONS"] ?></dimensions>
                        <? endif ?>
                    </offer>
                <? endforeach; ?>
            </offers>
        </shop>
    </yml_catalog>
    <?
else:
    if (!function_exists("itemsCycle"))
    {

        function itemsCycle(&$savedXML, $arResult, $arParams) {
            foreach ($arResult["OFFER"] as $arOffer):
                        $savedXML .= '<offer type="artist.title" id="' . $arOffer["ID"] . '" available="' . $arOffer["AVAIBLE"] . '"' . (!empty($arOffer["BID"]) ? ' bid="' . $arOffer["BID"] . '"' : '') . (!empty($arOffer["CBID"])? ' cbid="' . $arOffer["CBID"] . '"' : '') . (!empty($arOffer["FEE"]) ? ' fee="' . $arOffer["FEE"] . '"' : '') . '>';
                        $savedXML .= '<url>' . $arOffer["URL"] . '</url>';
                        $savedXML .= '<price>' . $arOffer["PRICE"] . '</price>';
                        if (!empty($arOffer["OLD_PRICE"])):
                            $savedXML .= '<oldprice>' . $arOffer["OLD_PRICE"] . '</oldprice>';
                        endif;
                        if (!empty($arOffer["PURCHASE_PRICE"])):
                            $savedXML .= '<purchase_price>' . $arOffer["PURCHASE_PRICE"] . '</purchase_price>';
                        endif;
                        $savedXML .= '<currencyId>';
                        if (!empty($arOffer["CURRENCY"])):
                            $savedXML .= $arOffer["CURRENCY"];
                        else:
                            $savedXML .= $arParams["CURRENCY"];
                        endif;
                        $savedXML .= '</currencyId>';

                        $savedXML .= '<categoryId>' . $arOffer["CATEGORY"] . '</categoryId>';
                        if ($arOffer["PICTURE"]):
                            $savedXML .= '<picture>' . $arOffer["PICTURE"] . '</picture>';
                        endif;
                        if ($arOffer["STORE_OFFER"] == "true"):
                            $savedXML .= '<store>true</store>';
                        endif;
                        if ($arOffer["STORE_OFFER"] == "false"):
                            $savedXML .= '<store>false</store>';
                        endif;
                        if ($arOffer["STORE_PICKUP"] == "true"):
                            $savedXML .= '<pickup>true</pickup>';
                        endif;
                        if ($arOffer["STORE_PICKUP"] == "false"):
                            $savedXML .= '<pickup>false</pickup>';
                        endif;
                         if ($arOffer["STORE_DELIVERY"] == "true"):
                    $savedXML .= '<delivery>true</delivery>';
                endif;
                if ($arOffer["STORE_DELIVERY"] == "false"):
                    $savedXML .= '<delivery>false</delivery>';
                endif;
                if (is_numeric($arOffer["LOCAL_DELIVERY_COST_OFFER"]) and empty($arOffer["DELIVERY_OPTIONS_EX"])):
                    $savedXML .= '<local_delivery_cost>' . $arOffer["LOCAL_DELIVERY_COST_OFFER"] . '</local_delivery_cost>';
                endif;
                if (!empty($arOffer["DELIVERY_OPTIONS_EX"]) and count($arOffer["DELIVERY_OPTIONS_EX"]) > 0):
                    $savedXML .= '<delivery-options>';
                    foreach ($arOffer["DELIVERY_OPTIONS_EX"] as $optK => $opt):
                        if ($opt[0] != ""):
                            $savedXML .= '<option cost="' . $opt[0] . '" days="' . $opt[1] . '"' . ($opt[2] != '' ? ' order-before="' . $opt[2] . '"' : '') . '/>';
                        endif;
                    endforeach;
                    $savedXML .= '</delivery-options>';
                endif;
                if (!empty($arOffer["OUTLETS"]) and count($arOffer["OUTLETS"]) > 0):
                    $savedXML .= '<outlets>';
                    foreach ($arOffer["OUTLETS"] as $outK => $out):
                        $savedXML .= '<outlet id="' . $out[0] . '" instock="' . $out[1] . '"' . ($out[2] != '' ? ' booking="' . $out[2] . '"' : '') . '/>';
                    endforeach;
                    $savedXML .= '</outlets>';
                endif;
                        if (!empty($arOffer["PREFIX_PROP"])):
                            $savedXML .= '<typePrefix>' . $arOffer["PREFIX_PROP"] . '</typePrefix>';
                        endif;
                        $savedXML .= '<title>' . $arOffer["MODEL"] . '</title>';
                        if ($arOffer["DISPLAY_PROPERTIES"][$arParams["ARTIST"]]["DISPLAY_VALUE"]):
                            $savedXML .= '<artist>' . $arOffer["DISPLAY_PROPERTIES"][$arParams["ARTIST"]]["DISPLAY_VALUE"] . '</artist>';
                        endif;
                        if ($arOffer["DISPLAY_PROPERTIES"][$arParams["YEAR"]]["DISPLAY_VALUE"]):
                            $savedXML .= '<year>' . $arOffer["DISPLAY_PROPERTIES"][$arParams["YEAR"]]["DISPLAY_VALUE"] . '</year>';
                        endif;
                        if ($arOffer["DISPLAY_PROPERTIES"][$arParams["MEDIA"]]["DISPLAY_VALUE"]):
                            $savedXML .= '<media>' . $arOffer["DISPLAY_PROPERTIES"][$arParams["MEDIA"]]["DISPLAY_VALUE"] . '</media>';
                        endif;
                        if ($arOffer["DISPLAY_PROPERTIES"][$arParams["BARCODE"]]["DISPLAY_VALUE"]):
                            $savedXML .= '<barcode>' . $arOffer["DISPLAY_PROPERTIES"][$arParams["BARCODE"]]["DISPLAY_VALUE"] . '</barcode>';
                        endif;
                        if (!empty($arOffer["DESCRIPTION"]) or ! empty($arOffer["DOP_PROPS"])):
                            if (!empty($arOffer["DOP_PROPS"])):
                                $savedXML .= '<description>' . $arOffer["DOP_PROPS"] . ". " . $arOffer["DESCRIPTION"] . '</description>';
                            else:
                                $savedXML .= '<description>' . $arOffer["DESCRIPTION"] . '</description>';
                            endif;
                        endif;
                        if ($arOffer["ADULT"] == "true"):
                    $savedXML .= '<adult>true</adult>';
                endif;
                if (is_numeric($arOffer["AGE_CATEGORY"])):
                    $savedXML .= '<age unit="' . $arParams["AGE_CATEGORY_UNIT"] . '">' . $arOffer["AGE_CATEGORY"] . '</age>';
                endif;
                if (is_numeric($arOffer["CPA_OFFERS"])):
                    $savedXML .= '<cpa>' . $arOffer["CPA_OFFERS"] . '</cpa>';
                endif;
                if (!empty($arOffer["RECOMMENDATION"])):
                    $savedXML .= '<rec>' . $arOffer["RECOMMENDATION"] . '</rec>';
                endif;
                        if ($arOffer["EXPIRY"]):
                            $savedXML .= '<expiry>'.$arOffer["EXPIRY"].'</expiry>';
                        endif;
                        if ($arOffer["WEIGHT"]): 
                            $savedXML .= '<weight>'.$arOffer["WEIGHT"].'</weight>';
                        endif;
                        if ($arOffer["DIMENSIONS"]):
                            $savedXML .= '<dimensions>'.$arOffer["DIMENSIONS"].'</dimensions>';
                        endif;
                        $savedXML .= '</offer>';
                    endforeach;
        }

    }
    if (!function_exists("xmlHeader"))
    {
        function xmlHeader(&$savedXML, $arResult, $arParams,$server_name) {
            $savedXML = '<?xml version="1.0" encoding="' . LANG_CHARSET . '"?>';
                    $savedXML .= '<!DOCTYPE yml_catalog SYSTEM "shops.dtd">';
                    $savedXML .= '<yml_catalog date="' . $arResult["DATE"] . '">';
                    $savedXML .= '<shop>';
                    $savedXML .= '<name>' . $arResult["SITE"] . '</name>';
                    $savedXML .= '<company>' . $arResult["COMPANY"] . '</company>';
                    $savedXML .= '<url>';
                    if ($arParams ["HTTPS_CHECK"] == "Y"):
                        $savedXML .= "https://" . $server_name;
                    else:
                        $savedXML .= "http://" . $server_name;
                    endif;
                    $savedXML .= '</url>';
                    $savedXML .= '<currencies>';
                    if (!empty($arResult["CURRENCIES"])):
                        foreach ($arResult["CURRENCIES"] as $k => $cur):
                            if (!empty($cur) && $cur != 'RUR' && (!empty($arResult["WF_AMOUNTS"][$cur]) || $cur == 'RUB')):
                                $savedXML .= '<currency id="' . $cur . '"' . (($cur == 'RUB') ? ' rate="1"' : ' rate="' . $arResult["WF_AMOUNTS"][$cur] . '"') . '/>';
                            endif;
                        endforeach;
                    else:
                        $savedXML .= '<currency id="' . $arParams["CURRENCY"] . '" rate="1"/>';
                    endif;
                    $savedXML .= '</currencies>';

                    $savedXML .= '<categories>';
                    foreach ($arResult["CATEGORIES"] as $arCategory):
                        $savedXML .= '<category id="' . $arCategory["ID"] . '"';
                        if ($arCategory["PARENT"])
                            $savedXML .= ' parentId="' . $arCategory['PARENT'] . '"';
                        $savedXML .= '>' . $arCategory["NAME"] . '</category>';
                    endforeach;
                    $savedXML .= '</categories>';

                    if ($arParams["LOCAL_DELIVERY_COST"] != "" and empty($arResult["DELIVERY_OPTION_SHOP"])):
                $savedXML .= '<local_delivery_cost>' . $arParams["LOCAL_DELIVERY_COST"] . '</local_delivery_cost>';
            endif;
            if (!empty($arResult["DELIVERY_OPTION_SHOP"])):
                $savedXML .= '<delivery-options>';
                foreach ($arResult["DELIVERY_OPTION_SHOP"] as $delK => $delV):
                    $savedXML .= '<option cost="' . $delV[0] . '" days="' . $delV[1] . '"' . ($delV[2] != '' ? ' order-before="' . $delV[2] . '"' : '') . '/>';
                endforeach;
                $savedXML .= '</delivery-options>';
            endif;
            if ($arResult["CPA_SHOP"]=="1" || $arResult["CPA_SHOP"]=="0"):
                $savedXML .= '<cpa>' . $arResult["CPA_SHOP"] . '</cpa>';
            endif;
            if ($arParams["ADULT_ALL"] == "Y"):
                $savedXML .= '<adult>true</adult>';
            endif;
                    $savedXML .= '<offers>';
        }

    }
    $wf_page = $APPLICATION->GetCurDir();
    $permanent_file = $_SERVER["DOCUMENT_ROOT"] . $wf_page . '/saved_file.xml';
    $temp_file = $_SERVER["DOCUMENT_ROOT"] . $wf_page . '/saved_file_temp.xml';
    $arParams["BIG_CATALOG_PROP"] = trim($arParams["BIG_CATALOG_PROP"]);
    if (!empty($arParams["BIG_CATALOG_PROP"]) and $_SESSION["WFYM_FINISH"] != "Yes")
    {
        if ((($arResult["WF_CURR"] - $arParams["BIG_CATALOG_PROP"]) < $arResult["WF_FULL"]))
        {
            if ($arResult["WF_CURR"] < $arResult["WF_FULL"])
            {
                if ($arResult["WF_NUM"] == 1)
                {
                    xmlHeader($savedXML, $arResult, $arParams,$server_name);
                    itemsCycle($savedXML, $arResult, $arParams);
                    file_put_contents("saved_file_temp.xml", $savedXML, LOCK_EX);
                }
                else
                {
                    itemsCycle($savedXML, $arResult, $arParams);
                    file_put_contents("saved_file_temp.xml", $savedXML, FILE_APPEND | LOCK_EX);
                }
                $arResult["WF_NUM"] ++;
                if ($arResult["WF_NUM"] == 21)
                {
                    $savedXML = '</offers>
</shop>
    </yml_catalog>';
                    file_put_contents("saved_file_temp.xml", $savedXML, FILE_APPEND | LOCK_EX);
                    echo GetMessage("LOAD_FAIL");
                    $_SESSION["WFYM_FINISH"] = "Yes";
                    if (file_exists($temp_file))
                    {
                        if (file_exists($permanent_file))
                            unlink($permanent_file);
                        rename($temp_file, $permanent_file);
                    }
                }
                else
                {
                    $url = $APPLICATION->GetCurPageParam("WF_PAGE={$arResult["WF_NUM"]}", array("WF_PAGE"));
                    LocalRedirect($url);
                }
            }
            else
            {
                if ($arResult["WF_NUM"] == 1)
                {
                    xmlHeader($savedXML, $arResult, $arParams,$server_name);
                    itemsCycle($savedXML, $arResult, $arParams);
                    $savedXML .= '</offers>
      </shop>
    </yml_catalog>';
                    file_put_contents("saved_file_temp.xml", $savedXML, LOCK_EX);
                    echo GetMessage("FILE_SAVED_TO", array("#URL#" => $APPLICATION->GetCurDir() . "saved_file.xml"));
                    $_SESSION["WFYM_FINISH"] = "Yes";
                    if (file_exists($temp_file))
                    {
                        if (file_exists($permanent_file))
                            unlink($permanent_file);
                        rename($temp_file, $permanent_file);
                    }
                }
                else
                {
                    itemsCycle($savedXML, $arResult, $arParams);
                    $savedXML .= '</offers>
      </shop>
    </yml_catalog>';
                    file_put_contents("saved_file_temp.xml", $savedXML, FILE_APPEND | LOCK_EX);
                    echo GetMessage("FILE_SAVED_TO", array("#URL#" => $wf_page . "saved_file.xml"));
                    $_SESSION["WFYM_FINISH"] = "Yes";
                    if (file_exists($temp_file))
                    {
                        if (file_exists($permanent_file))
                            unlink($permanent_file);
                        rename($temp_file, $permanent_file);
                    }
                }
            }
        }
    }
    else
    {
        xmlHeader($savedXML, $arResult, $arParams,$server_name);
        itemsCycle($savedXML, $arResult, $arParams);
        $savedXML .= '</offers>
      </shop>
    </yml_catalog>';
        file_put_contents("saved_file.xml", $savedXML, LOCK_EX);
        echo GetMessage("FILE_SAVED_TO", array("#URL#" => $APPLICATION->GetCurDir() . "saved_file.xml"));
    }
endif;?>
<?if ($arParams["ECHO_ADMIN_INFO"] == "Y") {
            if (!function_exists("convert_size")) {

                function convert_size($size) {
                    $unit = array('B', 'Kb', 'M', 'Gb', 'Tb', 'Pb');
                    return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
                }

            }

            $stepEndTime = date("d.m.Y H:i:s");
            file_put_contents("ym_log.txt", GetMessage("SCRIPT_END") . $stepEndTime . "\r\n", FILE_APPEND | LOCK_EX);
            file_put_contents("ym_log.txt", GetMessage("TIME_SUM") . FormatDate("sdiff",$stepStartTime,$stepEndTime) . "\r\n", FILE_APPEND | LOCK_EX);
            file_put_contents("ym_log.txt", GetMessage("MEMORY_GET") . convert_size(memory_get_usage(true)) . "\r\n\r\n", FILE_APPEND | LOCK_EX);
        }?>