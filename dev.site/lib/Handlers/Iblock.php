<?php

namespace Only\Site\Handlers;


class Iblock
{
    public static function addLog(&$arFields)
    {
		//получить код и имя инфоблока по ID
		$res = CIBlock::GetByID($arFields["IBLOCK_ID"]);
		if($ar_res = $res->GetNext()) {
			$ib_name=$ar_res['NAME'];
			$ib_code=$ar_res['CODE'];
		}
	
			//если код инфоблока созданного элемента не LOG
		if ($ib_code != "LOG") {
			$arFilter = Array('IBLOCK_ID'=>15, 'NAME'=>$ib_name,'CODE'=>$ib_code);
			$arSelect = array( 'ID', 'NAME');
			$db_list = CIBlockSection::GetList(Array($by=>$order), $arFilter, false);
			$found_ID=$db_list->GetNext()['ID'];
	
			if($found_ID==0) {
				$bs = new CIBlockSection;
				//создать новый раздел
				$arF = Array(
					"ACTIVE" => "Y",
					"IBLOCK_ID" => 15,
					"NAME" => $ib_name,
					"CODE" =>$ib_code,
					"SORT" => "ASC",
				);
				$ID_section = $bs->Add($arF);
			}
			else {
				$ID_section=$found_ID;
			}
			//составить навигационный путь
			$get_iblock = CIBlock::GetByID($arFields["IBLOCK_ID"]);
	
			if($ar_res = $get_iblock->GetNext())
				$iblock = $ar_res['NAME'];
	
			$get_el = CIBlockElement::GetByID($arFields["ID"]);
	
			if($ar_res = $get_el->GetNext())
				$el_name = $ar_res['NAME'];
	
			$list = CIBlockSection::GetNavChain(false,$arFields["IBLOCK_SECTION"][0],array(), true);
			$sectpath=$iblock;
	
			foreach ($list as $arSectionPath) {
				$sectpath=$sectpath." -> ".$arSectionPath['NAME'];
			}
	
			$sectpath=$sectpath." -> ".$el_name;
	
			//создать инфоблок
			$el = new CIBlockElement;
			$PROP = array();
			date_default_timezone_set("Asia/Krasnoyarsk");
	
			$arLoadProductArray = Array( // элемент изменен текущим пользователем
				"IBLOCK_SECTION_ID" => $ID_section,          // элемент лежит в корне раздела
				"IBLOCK_ID"      => 15,
				"PROPERTY_VALUES"=> $PROP,
				"NAME"           => $arFields["ID"],
				"ACTIVE"         => "Y",            // активен
				"PREVIEW_TEXT"   => $sectpath,
				"DATE_ACTIVE_FROM" => date("d.m.Y H:i:s")
					);
	
			if($PRODUCT_ID = $el->Add($arLoadProductArray))
				echo "New ID: ".$PRODUCT_ID;
			else
			echo "Error: ".$el->LAST_ERROR;
		}
	

    }

    function OnBeforeIBlockElementAddHandler(&$arFields)
    {
        $iQuality = 95;
        $iWidth = 1000;
        $iHeight = 1000;
        /*
         * Получаем пользовательские свойства
         */
        $dbIblockProps = \Bitrix\Iblock\PropertyTable::getList(array(
            'select' => array('*'),
            'filter' => array('IBLOCK_ID' => $arFields['IBLOCK_ID'])
        ));
        /*
         * Выбираем только свойства типа ФАЙЛ (F)
         */
        $arUserFields = [];
        while ($arIblockProps = $dbIblockProps->Fetch()) {
            if ($arIblockProps['PROPERTY_TYPE'] == 'F') {
                $arUserFields[] = $arIblockProps['ID'];
            }
        }
        /*
         * Перебираем и масштабируем изображения
         */
        foreach ($arUserFields as $iFieldId) {
            foreach ($arFields['PROPERTY_VALUES'][$iFieldId] as &$file) {
                if (!empty($file['VALUE']['tmp_name'])) {
                    $sTempName = $file['VALUE']['tmp_name'] . '_temp';
                    $res = \CAllFile::ResizeImageFile(
                        $file['VALUE']['tmp_name'],
                        $sTempName,
                        array("width" => $iWidth, "height" => $iHeight),
                        BX_RESIZE_IMAGE_PROPORTIONAL_ALT,
                        false,
                        $iQuality);
                    if ($res) {
                        rename($sTempName, $file['VALUE']['tmp_name']);
                    }
                }
            }
        }

        if ($arFields['CODE'] == 'brochures') {
            $RU_IBLOCK_ID = \Only\Site\Helpers\IBlock::getIblockID('DOCUMENTS', 'CONTENT_RU');
            $EN_IBLOCK_ID = \Only\Site\Helpers\IBlock::getIblockID('DOCUMENTS', 'CONTENT_EN');
            if ($arFields['IBLOCK_ID'] == $RU_IBLOCK_ID || $arFields['IBLOCK_ID'] == $EN_IBLOCK_ID) {
                \CModule::IncludeModule('iblock');
                $arFiles = [];
                foreach ($arFields['PROPERTY_VALUES'] as $id => &$arValues) {
                    $arProp = \CIBlockProperty::GetByID($id, $arFields['IBLOCK_ID'])->Fetch();
                    if ($arProp['PROPERTY_TYPE'] == 'F' && $arProp['CODE'] == 'FILE') {
                        $key_index = 0;
                        while (isset($arValues['n' . $key_index])) {
                            $arFiles[] = $arValues['n' . $key_index++];
                        }
                    } elseif ($arProp['PROPERTY_TYPE'] == 'L' && $arProp['CODE'] == 'OTHER_LANG' && $arValues[0]['VALUE']) {
                        $arValues[0]['VALUE'] = null;
                        if (!empty($arFiles)) {
                            $OTHER_IBLOCK_ID = $RU_IBLOCK_ID == $arFields['IBLOCK_ID'] ? $EN_IBLOCK_ID : $RU_IBLOCK_ID;
                            $arOtherElement = \CIBlockElement::GetList([],
                                [
                                    'IBLOCK_ID' => $OTHER_IBLOCK_ID,
                                    'CODE' => $arFields['CODE']
                                ], false, false, ['ID'])
                                ->Fetch();
                            if ($arOtherElement) {
                                /** @noinspection PhpDynamicAsStaticMethodCallInspection */
                                \CIBlockElement::SetPropertyValues($arOtherElement['ID'], $OTHER_IBLOCK_ID, $arFiles, 'FILE');
                            }
                        }
                    } elseif ($arProp['PROPERTY_TYPE'] == 'E') {
                        $elementIds = [];
                        foreach ($arValues as &$arValue) {
                            if ($arValue['VALUE']) {
                                $elementIds[] = $arValue['VALUE'];
                                $arValue['VALUE'] = null;
                            }
                        }
                        if (!empty($arFiles && !empty($elementIds))) {
                            $rsElement = \CIBlockElement::GetList([],
                                [
                                    'IBLOCK_ID' => \Only\Site\Helpers\IBlock::getIblockID('PRODUCTS', 'CATALOG_' . $RU_IBLOCK_ID == $arFields['IBLOCK_ID'] ? '_RU' : '_EN'),
                                    'ID' => $elementIds
                                ], false, false, ['ID', 'IBLOCK_ID', 'NAME']);
                            while ($arElement = $rsElement->Fetch()) {
                                /** @noinspection PhpDynamicAsStaticMethodCallInspection */
                                \CIBlockElement::SetPropertyValues($arElement['ID'], $arElement['IBLOCK_ID'], $arFiles, 'FILE');
                            }
                        }
                    }
                }
            }
        }
    }

}
