
<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
if (!$USER->IsAdmin()) {
    LocalRedirect('/');
}


if(CModule::IncludeModule("iblock")) {
    $row = 0;
    $keys = array();
    $data = array();

    //установить нужный ID инфоблока
    $IBLOCK_ID = 14;

    $PROP = array();
    $arProps = [];

    //получить свойства ID, NAME инфоблока
    $rsElement = CIBlockElement::getList([], ['IBLOCK_ID' => $IBLOCK_ID],
        false, false, ['ID', 'NAME']);

    //получить поля ID, NAME всех элементов, назначить поле "OFFICE"
    while ($ob = $rsElement->GetNextElement()) {
        $arFields = $ob->GetFields();
        $key = str_replace(['»', '«', '(', ')'], '', $arFields['NAME']);
        $key = strtolower($key);
        $arKey = explode(' ', $key);
        $key = '';
        foreach ($arKey as $part) {
            if (strlen($part) > 2) {
                $key .= trim($part) . ' ';
            }
        }
        $key = trim($key);
        $arProps['OFFICE'][$key] = $arFields['ID'];
    }

    //получить список вариантов значений свойтв типа "список" инфоблока
    $rsProp = CIBlockPropertyEnum::GetList(
        ["SORT" => "ASC", "VALUE" => "ASC"],
        ['IBLOCK_ID' => $IBLOCK_ID]
    );

    //добавить в arProps списки вариантов значений
    while ($arProp = $rsProp->Fetch()) {
        $key = trim($arProp['VALUE']);
        $arProps[$arProp['PROPERTY_CODE']][$key] = $arProp['ID'];
    }

    //получить свойства ID инфоблока
    $rsElements = CIBlockElement::GetList([], ['IBLOCK_ID' => $IBLOCK_ID], false, false, ['ID']);

    //удалить поле ID
    while ($element = $rsElements->GetNext()) {
    CIBlockElement::Delete($element['ID']);
    }

    //открыть файл
    if (($handle = fopen("vacancy.csv", "r")) !== false) {
        while (($str = fgetcsv($handle, 1000, ",")) !== false) {
        	$row++;

            if ($row == 1) {
            	//присвоить ключи
            	$keys = $str;
    			array_pop($keys);
                continue;
            }
            else {
                $el = array();
                foreach ($str as $key=>$item) {
                    //очистить данные
                    $item = trim($item);
                    $item = str_replace('\n', '', $item);
                    //присвоить значения ключам строки
                    //если есть знак точки, разбить на несколько строк
                    if (stripos($item, '•') !== false) {
                        $item = explode('•', $item);
                        array_splice($item, 0, 1);
                        foreach ($item as &$s) {
                            $s = trim($s);
                        }
                    }
                    $el[$keys[$key]] = $item;
                }
                $data[] = $el;
            }

            //загрузить el в элемент
            $PROP['ACTIVITY'] = $el['Тип занятости'];
            $PROP['FIELD'] = $el['Сфера деятельности'];
            $PROP['OFFICE'] = $el['Комбинат'];
            $PROP['LOCATION'] = $el['Местоположение'];
            $PROP['REQUIRE'] = $el['Требования'];
            $PROP['DUTY'] = $el['Обязанности'];
            $PROP['CONDITIONS'] = $el['Условия работы'];
            $PROP['EMAIL'] = $el['Кому направить резюме (e-mail)'];
            $PROP['DATE'] = date('d.m.Y');
            $PROP['TYPE'] = $el['Категория позиции'];
            $PROP['SALARY_TYPE'] = '';
            $PROP['SALARY_VALUE'] = $el['Зарплата'];
            $PROP['SCHEDULE'] = $el['График работы'];


            //присвоить нужные значения свойствам-спискам "OFFICE" "SALARY_TYPE"
            foreach ($PROP as $key => &$value) {
                if ($arProps[$key]) {
                    $arSimilar = [];
                    foreach ($arProps[$key] as $propKey => $propVal) {
                        if ($key == 'OFFICE') {
                            $value = strtolower($value);
                            if ($value == 'центральный офис') {
                                $value .= 'свеза ' . $data[2];
                            } elseif ($value == 'лесозаготовка') {
                                $value = 'свеза ресурс ' . $value;
                            } elseif ($value == 'свеза тюмень') {
                                $value = 'свеза тюмени';
                            }
                            $arSimilar[similar_text($value, $propKey)] = $propVal;
                        }
                        if (stripos($propKey, $value) !== false) {
                            $value = $propVal;
                            break;
                        }

                        if (similar_text($propKey, $value) > 50) {
                            $value = $propVal;
                        }
                    }
                    if ($key == 'OFFICE' && !is_numeric($value)) {
                        ksort($arSimilar);
                        $value = array_pop($arSimilar);
                    }
                }
            }


            if ($PROP['SALARY_VALUE'] == '-') {
                    $PROP['SALARY_VALUE'] = '';
            } elseif ($PROP['SALARY_VALUE'] == 'по договоренности') {
                $PROP['SALARY_VALUE'] = '';
                $PROP['SALARY_TYPE'] = $arProps['SALARY_TYPE']['договорная'];
            } else {
                $arSalary = explode(' ', $PROP['SALARY_VALUE']);
                if ($arSalary[0] == 'от' || $arSalary[0] == 'до') {
                    $PROP['SALARY_TYPE'] = $arProps['SALARY_TYPE'][$arSalary[0]];
                    array_splice($arSalary, 0, 1);
                    $PROP['SALARY_VALUE'] = implode(' ', $arSalary);
                } else {
                    $PROP['SALARY_TYPE'] = $arProps['SALARY_TYPE']['='];
                }
            }



            $arLoadProductArray = [
                "MODIFIED_BY" => $USER->GetID(),
                "IBLOCK_SECTION_ID" => false,
                "IBLOCK_ID" => $IBLOCK_ID,
                "PROPERTY_VALUES" => $PROP,
                "NAME" => $str[3],
                "ACTIVE" => end($str) ? 'Y' : 'N',
            ];
            $e = new CIBlockElement;
            if ($PRODUCT_ID = $e->Add($arLoadProductArray)) {
                echo "Добавлен элемент с ID : " . $PRODUCT_ID . "<br>";
            } else {
                echo "Error: " . $e->LAST_ERROR . '<br>';
            }

        }
        if (!feof($handle)) {
            echo "Error: unexpected fgetcsv() fail\n";
        }
        //закрыть файл
        fclose($handle);

    }
    foreach($keys as $key=>$value) {
    	echo $value." ";
    }
    echo "<br>";

    foreach($data as $key=>$item) {
    	foreach ($item as $k=>$el) {
    		echo "<h2>".$k." :</h2> ";
    		if (is_array($el)){
    			foreach ($el as $e=>$l) {
    				echo $l." ";
    			}
    		}
    		else {
    			echo $el." ";
    		}
    	}
    	echo "<br>";

    }
}



