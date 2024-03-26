<?php

namespace Only\Site\Agents;


class Iblock
{
    public static function clearOldLogs()
    {
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

    public static function example()
    {
        global $DB;
        if (\Bitrix\Main\Loader::includeModule('iblock')) {
            $iblockId = \Only\Site\Helpers\IBlock::getIblockID('QUARRIES_SEARCH', 'SYSTEM');
            $format = $DB->DateFormatToPHP(\CLang::GetDateFormat('SHORT'));
            $rsLogs = \CIBlockElement::GetList(['TIMESTAMP_X' => 'ASC'], [
                'IBLOCK_ID' => $iblockId,
                '<TIMESTAMP_X' => date($format, strtotime('-1 months')),
            ], false, false, ['ID', 'IBLOCK_ID']);
            while ($arLog = $rsLogs->Fetch()) {
                \CIBlockElement::Delete($arLog['ID']);
            }
        }
        return '\\' . __CLASS__ . '::' . __FUNCTION__ . '();';
    }
}
