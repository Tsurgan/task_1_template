<div id="barba-wrapper">
    <div class="article-list">
    <?foreach($arResult["ITEMS"] as $arItem):?>
    <a class="article-item article-list__item" href="<?echo $arItem["DETAIL_PAGE_URL"]?>"
                                 data-anim="anim-3">
        <div class="article-item__background"><img src="<?=$arItem["PREVIEW_PICTURE"]["SRC"]?>"
                                                   data-src="xxxHTMLLINKxxx0.39186223192351520.41491856731872767xxx"
                                                   alt=""/></div>
        <div class="article-item__wrapper">
    		<?if($arParams["DISPLAY_NAME"]!="N" && $arItem["NAME"]):?>
                    <div class="article-item__title"><?echo $arItem["NAME"]?></div>
    		<?endif;?>

    		<?if($arParams["DISPLAY_PREVIEW_TEXT"]!="N" && $arItem["PREVIEW_TEXT"]):?>
            <div class="article-item__content"><?echo $arItem["PREVIEW_TEXT"];?></div>
    		<?endif;?>

        </div>
    </a>
    <?endforeach;?>
    </div>
</div>
