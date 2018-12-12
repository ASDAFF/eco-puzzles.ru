<?php
namespace Shiptor\Delivery\Options;

use Bitrix\Main\Config\Option;

class Helper{
    public static function generate($arOptions,$arConfigData){
        foreach ($arOptions as $optionName => $arOption):?>
            <tr>
                <?if($arOption["type"] == "NOTE"):?>
                    <td colspan="2" align="center">
                        <?=BeginNote();?>
                        <img src="/bitrix/js/main/core/images/hint.gif" style="margin-right: 5px;"/><?=$arOption["name"]?>
                        <?=EndNote();?>
                    </td>
                <?elseif($arOption["type"] == "heading"):?>
                    <td colspan="2" class="heading">
                        <?=$arOption["name"]?>
                    </td>
                <?else:
                    $value = isset($arConfigData[$optionName])?$arConfigData[$optionName]:$arOption["default"];
                    ?>
                    <td class="field-name" style="width:45%"><?=$arOption["name"]?>:</td>
                    <td>
                        <?switch($arOption["type"]):
                            case "text":case "email":case "number";
                                $arOption["name"] = $optionName;
                                $arOption["value"] = $value;
                                echo self::input($arOption);
                                break;
                            case "textarea":
                                $arParams = array(
                                    "ATTRS" => array(
                                        "name" => $optionName
                                    ),
                                    "VALUE" => $value
                                );
                                echo self::pairTag($arOption["type"], $arParams);
                                break;
                            case "select":
                                if($arOption["multiple"]){
                                    $values = explode("|",$value);
                                }else{
                                    $values = [$value];
                                }
                                ?>
                                <select name="<?=$optionName?><?if($arOption["multiple"]):?>[]<?endif?>" 
                                    <?if($arOption["multiple"]):?>multiple<?endif?>
                                    <?if($arOption["onchange"]):?>onChange="<?=$arOption["onchange"]?>"<?endif?>
                                    >
                                    <?foreach($arOption["options"] as $id => $text):?>
                                        <option <?if(in_array($id, $values)) echo " selected "?> value="<?=$id?>"><?=$text?></option>
                                    <?endforeach?>
                                </select>
                                <?break;
                        endswitch?>
                        <?if(!empty($arOption["hint"])):?>
                            <img src="/bitrix/js/main/core/images/hint.gif" style="margin-right: 5px;cursor:pointer" title="<?=$arOption["hint"]?>"
                                 <?if(!empty($arOption["href"])):?>
                                    onclick="window.open('<?=$arOption["href"]?>');"
                                 <?endif?>
                                 />
                        <?endif?>
                    </td>
                <?endif?>
            </tr><?
        endforeach;
    }
    public static function checkbox($arOption){
        $val = Option::get(THIS_MODULE_ID, $arOption[0], $arOption[2]);
        $inpParams = array(
            "type" => $arOption[3][0],
            "id" => htmlspecialchars($arOption[0]),
            "class" => "adm-designed-checkbox",
            "name" => htmlspecialchars($arOption[0]),
            "value" => "Y"
        );
        if($val == "Y"){
            $inpParams["checked"] = "checked";
        }
        $labelParams = array(
            "ATTRS" => array(
                "for" => htmlspecialchars($arOption[0]),
                "class" => "adm-designed-checkbox-label"
            )
        );
        return self::input($inpParams).self::label($labelParams);
    }
    public static function text($arOption){
        $val = Option::get(THIS_MODULE_ID, $arOption[0], $arOption[2]);
        $inpParams = array(
            "type" => $arOption[3][0],
            "id" => htmlspecialchars($arOption[0]),
            "maxlength" => 255,
            "size" => $arOption[3][1],
            "name" => htmlspecialchars($arOption[0]),
            "value" => htmlspecialchars($val)
        );
        return self::input($inpParams);
    }
    public static function password($arOption){
        return self::text($arOption);
    }
    public static function number($arOption){
        $val = Option::get(THIS_MODULE_ID, $arOption[0], $arOption[2]);
        $inpParams = array(
            "type" => $arOption[3][0],
            "id" => htmlspecialchars($arOption[0]),
            "min" => $arOption[3][3],
            "max" => $arOption[3][2],
            "size" => $arOption[3][1],
            "name" => htmlspecialchars($arOption[0]),
            "value" => htmlspecialchars($val),
            "style" => 'border-radius:4px;min-height:17px;border: 1px solid #a3a5a5;box-shadow: 0 1px 0 0 rgba(255,255,255,0.3), inset 0 2px 2px -1px rgba(180,188,191,0.7);padding:5px;width:50px'
        );
        return self::input($inpParams);
    }
    public static function textarea($arOption){
        $val = Option::get(THIS_MODULE_ID, $arOption[0], $arOption[2]);
        $taParams = array(
            "ATTRS" => array(
                "rows" => $arOption[3][1],
                "cols" => $arOption[3][2],
                "name" => htmlspecialchars($arOption[0])
            ),
            "VALUE" => htmlspecialchars($val)
        );
        return self::pairTag("textarea", $taParams);
    }
    public static function select($arOption){
        $val = Option::get(THIS_MODULE_ID, $arOption[0], $arOption[2]);
        $seParams = array(
            "ATTRS" => array(
                "size" => $arOption[3][1],
                "name" => htmlspecialchars($arOption[0])
            ),
            "VALUE" => ""
        );
        foreach($arOption[3][2] as $item){
            $opParams = array(
                "ATTRS" => ["value" => $item["ID"]],
                "VALUE" => $item["NAME"]
            );
            if($item["ID"] == $val){
                $opParams["ATTRS"]["selected"] = "selected";
            }
            $seParams["VALUE"] .= self::pairTag("option",$opParams);
        }
        return self::pairTag("select",$seParams);
    }
    public static function input($arParams){
        return self::singleTag("input", $arParams);
    }
    public static function label($arParams){
        return self::pairTag("label", $arParams);
    }
    public static function singleTag($tagName,$tagParams){
        $params = array();
        foreach($tagParams as $name => $value){
            $params[] = $name.'="'.$value.'"';
        }
        return "<{$tagName} ".implode(" ",$params)." />";
    }
    public static function pairTag($tagName,$tagParams){
        $params = array();
        $innerHTML = $tagParams["VALUE"];
        foreach($tagParams["ATTRS"] as $name => $value){
            $params[] = $name.'="'.$value.'"';
        }
        return "<{$tagName} ".implode(" ",$params)." >".$innerHTML."</{$tagName}>";
    }
}