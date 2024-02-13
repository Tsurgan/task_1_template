		<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if ($arResult["isFormErrors"] == "Y"):?> <?=$arResult["FORM_ERRORS_TEXT"];?><?endif;?>
    <?=$arResult["FORM_NOTE"]?>
<?if ($arResult["isFormNote"] != "Y") {
?>
        <?=$arResult["FORM_HEADER"]?>
<div class="contact-form">
    <div class="contact-form__head">
        <?
        if ($arResult["isFormDescription"] == "Y" || $arResult["isFormTitle"] == "Y" || $arResult["isFormImage"] == "Y") {
        ?>
            <?
            if ($arResult["isFormTitle"]) {
            ?>
        <div class="contact-form__head-title">
                <?=$arResult["FORM_TITLE"]?>
		</div>
            <?
            } //endif ;

	        if ($arResult["isFormImage"] == "Y") {
	        ?>
<a href="<?=$arResult["FORM_IMAGE"]["URL"]?>" target="_blank" alt="<?=GetMessage("FORM_ENLARGE")?>"><img src="<?=$arResult["FORM_IMAGE"]["URL"]?>" <?if($arResult["FORM_IMAGE"]["WIDTH"] > 300):?>width="300"<?elseif($arResult["FORM_IMAGE"]["HEIGHT"] > 200):?>height="200"<?else:?><?=$arResult["FORM_IMAGE"]["ATTR"]?><?endif;?> hspace="3" vscape="3" border="0" /></a>
	<?//=$arResult["FORM_IMAGE"]["HTML_CODE"]?>
			<?
	        } //endif
	        ?>
		<div class="contact-form__head-text">
			<?=$arResult["FORM_DESCRIPTION"]?>
		</div>
        <?
        } // endif
	    ?>
	</div>
    <form class="contact-form__form" action="/" method="POST">
        <div class="contact-form__form-inputs">
            <div class="contact-form__form-inputs">
        <?
        foreach ($arResult["QUESTIONS"] as $FIELD_SID => $arQuestion) {
	    //single question start

            if ($arQuestion['STRUCTURE'][0]['FIELD_TYPE'] == 'hidden') {
                echo $arQuestion["HTML_CODE"];
            }
            elseif ($arQuestion["CAPTION"]=="Сообщение") {
	    ?>
			</div>
            <div class="contact-form__form-message">
                <div class="input">
					<label class="input__label" for="medicine_message">
			    <?if (is_array($arResult["FORM_ERRORS"]) && array_key_exists($FIELD_SID, $arResult['FORM_ERRORS'])):?>
					<span class="error-fld" title="&lt;span id="><?=htmlspecialcharsbx($arResult["FORM_ERRORS"][$FIELD_SID])?>
					<span class="bxhtmled-surrogate-inner"><span class="bxhtmled-right-side-item-icon"></span>
					<span class="bxhtmled-comp-lable" unselectable="on" spellcheck="false">Код PHP</span>
					</span></span>"&gt;
				<?endif;?>
					<div class="input__label-text">
				<?=$arQuestion["CAPTION"]?><?if ($arQuestion["REQUIRED"] == "Y"):?><?=$arResult["REQUIRED_SIGN"];?><?endif;?>
				<?=$arQuestion["IS_INPUT_CAPTION_IMAGE"] == "Y" ? "<br />".$arQuestion["IMAGE"]["HTML_CODE"] : ""?>
					</div>
					<div class="input__input">
				<?=$arQuestion["HTML_CODE"]?>
					</div>
					<div class="input__notification">
					</div>
					</label>
				</div>
			</div>
			<?
			}
			else {
			?>
			<div class="input contact-form__input">
				<label class="input__label" for="medicine_name">
				<div class="input__label-text">
				<?=$arQuestion["CAPTION"]?><?if ($arQuestion["REQUIRED"] == "Y"):?><?=$arResult["REQUIRED_SIGN"];?><?endif;?>
				<?=$arQuestion["IS_INPUT_CAPTION_IMAGE"] == "Y" ? "<br />".$arQuestion["IMAGE"]["HTML_CODE"] : ""?>
				</div>
				<div class="input__input">
				<?=$arQuestion["HTML_CODE"]?>
				</div>
				<div class="input__notification">
					Поле должно содержать не менее 3-х символов
				</div>
				<?if (is_array($arResult["FORM_ERRORS"]) && array_key_exists($FIELD_SID, $arResult['FORM_ERRORS'])):?>
				<span class="error-fld" title="&lt;span id="><?=htmlspecialcharsbx($arResult["FORM_ERRORS"][$FIELD_SID])?>
				<span class="bxhtmled-surrogate-inner"><span class="bxhtmled-right-side-item-icon"></span>
				<span class="bxhtmled-comp-lable" unselectable="on" spellcheck="false">Код PHP</span></span></span>"&gt;
				<?endif;?>
				</label>
			</div>
			<?
			}

		//single question end
        } //endwhile
	    ?>
		</div>
		<?
if($arResult["isUseCaptcha"] == "Y")
{
?>
<?=GetMessage("FORM_CAPTCHA_TABLE_TITLE")?>

<input type="hidden" name="captcha_sid" value="<?=htmlspecialcharsbx($arResult["CAPTCHACode"]);?>" /><img src="/bitrix/tools/captcha.php?captcha_sid=<?=htmlspecialcharsbx($arResult["CAPTCHACode"]);?>" width="180" height="40" />

<?=GetMessage("FORM_CAPTCHA_FIELD_TITLE")?><?=$arResult["REQUIRED_SIGN"];?>
<input type="text" name="captcha_word" size="30" maxlength="50" value="" class="inputtext" />

<?
} // isUseCaptcha
        //captcha end
        //button start
        ?>
		<div class="contact-form__bottom">
			<div class="contact-form__bottom-policy">
				Нажимая «Отправить», Вы&nbsp;подтверждаете, что ознакомлены, полностью согласны и&nbsp;принимаете условия «Согласия на&nbsp;обработку персональных данных».
			</div>
            <button class="form-button contact-form__bottom-button" data-success="Отправлено" data-error="Ошибка отправки">
			<div class="form-button__title">
				<input <?=(intval($arResult["F_RIGHT"]) < 10 ? "disabled=\"disabled\"" : "");?> type="submit" name="web_form_submit" value="<?=htmlspecialcharsbx(trim($arResult["arForm"]["BUTTON"]) == '' ? GetMessage("FORM_ADD") : $arResult["arForm"]["BUTTON"]);?>" />
			</div>
            </button>
		</div>
		<?=$arResult["REQUIRED_SIGN"];?> - <?=GetMessage("FORM_REQUIRED_FIELDS")?> <?=$arResult["FORM_FOOTER"]?>
<?
} //endif (isFormNote)
//button end
//form end
?>
    </form>
</div>
