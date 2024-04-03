<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("");
?><? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
foreach ($arResult['CARLIST'] as $item) {
    foreach ($item as $val) {
    echo $val." ";
    }
    echo "<br>";
}
?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>