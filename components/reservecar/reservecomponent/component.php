<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("");
?><? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();


//получить время 
$request = \Bitrix\Main\Context::getCurrent()->getRequest();
$time_start = $request->getQuery("start");
$time_end = $request->getQuery("end");
//$time_start="03.04.2024 11:02:00";
//$time_end="03.04.2024 11:05:00";
//получить user id
global $USER;
$user_id = $USER->GetID();
//$user_id=3;

//получить по user id id сотрудника
//получить id должности
$elist = CIBlockElement::GetList(Array("SORT"=>"ASC"),Array('IBLOCK_ID'=>19,'PROPERTY_USER_ID'=>$user_id),false,false,Array('IBLOCK_ID','NAME','ID','PROPERTY_POSITION'));
$ob = $elist->GetNextElement();
$arFields = $ob->GetFields();
$arProps = $ob->GetProperties();

$pos_id = $arProps['POSITION']['VALUE'];


//получить список доступных уровней комфорта должности по id
$elist = CIBlockElement::GetList(Array("SORT"=>"ASC"),Array('IBLOCK_ID'=>18,'ID'=>$pos_id),false,false,Array('IBLOCK_ID','NAME','ID','PROPERTY_COMFORT'));
$ob = $elist->GetNextElement();
$arFields = $ob->GetFields();
$arProps = $ob->GetProperties();

$comflist=$arProps['COMFORT']['VALUE'];


//отфильтровать модели по списку комфорта
$elist = CIBlockElement::GetList(Array("SORT"=>"ASC"),Array('IBLOCK_ID'=>17,'PROPERTY_COMFORT'=>$comflist),false,false,Array('IBLOCK_ID','NAME','ID'));
$modlist = [];
while($ob = $elist->GetNextElement()) {
	$arFields = $ob->GetFields();
	$modlist[] = $arFields['ID'];
}


//отфильтровать автомобили по списку моделей
$elist = CIBlockElement::GetList(Array("SORT"=>"ASC"),Array('IBLOCK_ID'=>20,'PROPERTY_MODEL'=>$modlist),false,false,Array('IBLOCK_ID','NAME','ID'));
$carlist = [];
while($ob = $elist->GetNextElement()) {
	$arFields = $ob->GetFields();
	$carlist[] = $arFields['ID'];
}


//создать негативный список где конец записи больше заданного начала и начало записи меньше заданного конца
$elist = CIBlockElement::GetList(Array("SORT"=>"ASC"),Array('IBLOCK_ID'=>21,'PROPERTY_CAR'=>$carlist,'<DATE_ACTIVE_FROM'=>$time_end,'>DATE_ACTIVE_TO'=>$time_start),false,false,Array('IBLOCK_ID','NAME','ID','PROPERTY_CAR','DATE_ACTIVE_FROM','DATE_ACTIVE_TO'));
$neglist=[];
while($ob = $elist->GetNextElement()) {
	$arFields = $ob->GetFields();
	$arProps = $ob->GetProperties();
	$neglist[] = $arProps['CAR']['VALUE'];
}

foreach ($neglist as $item) {
	if (($key = array_search($item, $carlist)) !== false) {
    	unset($carlist[$key]);
	}
}
//
//собрать список таких автомобилей и вывести водителя, название модели и уровень комфорта 

$elist = CIBlockElement::GetList(Array("SORT"=>"ASC"),Array('IBLOCK_ID'=>20,'ID'=>$carlist),false,false,Array('IBLOCK_ID','NAME','ID','PROPERTY_MODEL','PROPERTY_DRIVER'));
$carmodlist = [];
while($ob = $elist->GetNextElement()) {
	$arFields = $ob->GetFields();
	$arProps = $ob->GetProperties();
	$carmodlist[] = [$arFields['ID'],$arProps['DRIVER']['VALUE'],$arProps['MODEL']['VALUE'],0];
}

$elist = CIBlockElement::GetList(Array("SORT"=>"ASC"),Array('IBLOCK_ID'=>17),false,false,Array('IBLOCK_ID','NAME','ID','PROPERTY_COMFORT'));
$modcolumn = array_column($carmodlist,2);
while($ob = $elist->GetNextElement()) {
	$arFields = $ob->GetFields();
	$arProps = $ob->GetProperties();
	$ind = array_keys($modcolumn,$arFields['ID']);
	foreach ($ind as $item) {
		$carmodlist[$item][2] = $arFields['NAME'];
		$carmodlist[$item][3] = $arProps['COMFORT']['VALUE'];
	}
}

$elist = CIBlockElement::GetList(Array("SORT"=>"ASC"),Array('IBLOCK_ID'=>16),false,false,Array('IBLOCK_ID','NAME','ID'));
$comfcolumn = array_column($carmodlist,3);
while($ob = $elist->GetNextElement()) {
	$arFields = $ob->GetFields();
	$ind = array_keys($comfcolumn,$arFields['ID']);
	foreach ($ind as $item) {
		$carmodlist[$item][3] = $arFields['NAME'];
	}
}



$arResult['CARLIST']=$carmodlist;


$this->IncludeComponentTemplate();
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>