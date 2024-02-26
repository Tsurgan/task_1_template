<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="article-card">


    <?if($arParams["DISPLAY_NAME"]!="N" && $arResult["NAME"]):?>
    <div class="article-card__title">
    	<?=$arResult["NAME"]?>
    </div>
    <?endif;?>



	<?if($arParams["DISPLAY_DATE"]!="N" && $arResult["DISPLAY_ACTIVE_FROM"]):?>
	<div class="article-card__date">
		<?=$arResult["DISPLAY_ACTIVE_FROM"]?>
	</div>
	<?endif;?>

    <div class="article-card__content">


    <?if($arParams["DISPLAY_PICTURE"]!="N" && is_array($arResult["DETAIL_PICTURE"])):?>
        <div class="article-card__image sticky">
            <img class="detail_picture" src="<?=$arResult["DETAIL_PICTURE"]["SRC"]?>" width="<?=$arResult["DETAIL_PICTURE"]["WIDTH"]?>" height="<?=$arResult["DETAIL_PICTURE"]["HEIGHT"]?>" alt="<?=$arResult["NAME"]?>"  title="<?=$arResult["NAME"]?>" data-object-fit="cover" />
        </div>
    <?endif?>

        <div class="article-card__text">
            <div class="block-content" data-anim="anim-3">
 	<?if($arResult["DETAIL_TEXT"] <> ''):?>
		<?echo $arResult["DETAIL_TEXT"];?>
 	<?else:?>
		<?echo $arResult["PREVIEW_TEXT"];?>
	<?endif?>
            </div>

	<a class="article-card__button" href="/novosti.php">&larr; <?=GetMessage("T_NEWS_DETAIL_BACK")?></a>
	</div>
     </div>
</div>
