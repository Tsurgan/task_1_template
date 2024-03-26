<?
/*
You can place here your functions and event handlers

AddEventHandler("module", "EventName", "FunctionName");
function FunctionName(params)
{
	//code
}
*/
$eventManager = \Bitrix\Main\EventManager::getInstance();
$eventManager->addEventHandlerCompatible("iblock", "OnAfterIBlockElementUpdate", "myfunc");
$eventManager->addEventHandlerCompatible("iblock", "OnAfterIBlockElementAdd", "myfunc");

function myfunc($arFields)
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

function delete_entries() {
	$arFilter = Array("IBLOCK_ID"=>15);
	$res = CIBlockElement::GetList(Array("CREATE"=>"asc"), $arFilter);
	$el_array=[];

	while($ar_fields = $res->GetNext()) {
		$el_array[]=$ar_fields["ID"];
	}
	$amount=count($el_array);

	if ($amount>10) {
		$amount=$amount-11;
		for ($i = 0; $i <= $amount; $i++) {
			CIBlockElement::Delete($el_array[$i]);
			}
	}
	return 'delete_entries();';
}

?>
