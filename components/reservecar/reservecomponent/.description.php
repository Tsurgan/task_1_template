<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("");
?><? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die(); $arComponentDescription = array(
	"NAME" => GetMessage("Поиск свободного автомобиля"),
	"DESCRIPTION" => GetMessage("Находим свободный автомобиль по времени поездки"),
);
?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>