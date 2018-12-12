<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?if($arResult['TIMER']=='Y'){
		$APPLICATION->IncludeComponent('skyweb24:popup.pro.timer','',array(
			'TITLE'=>$arResult['TIMER_TEXT'],
			'TIME'=>$arResult['TIMER_DATE'],
			'LEFT'=>$arResult['TIMER_LEFT'],
			'RIGHT'=>$arResult['TIMER_RIGHT'],
			'TOP'=>$arResult['TIMER_TOP'],
			'BOTTOM'=>$arResult['TIMER_BOTTOM'],
		),$component);
	}?>
<div id="skyweb24_video_youtube">
	<iframe width="100%" height="480" src="https://www.youtube.com/embed/<?=$arResult['LINK_VIDEO']?>?rel=<?=$arResult['VIDEO_SIMILAR']?>&autoplay=<?=$arResult['VIDEO_AUTOPLAY']?>" frameborder="0"></iframe>
	<?/*
	Дополнительные параметры YouTube
	rel=0 - Запрет показа похожих роликов
	autoplay=1 - Автозапуск ролика
	disablekb=1 - Запрет на управление воспроизведением ролика с помощью клавиатуры (Теперь «пробелом» видео не остановить)
	fs=0 - Запрет на «разворачивание» на весь экран
	showinfo=0 - Скрытие информации о ролике
	start=N - Воспроизведение видео через N секунд после загрузки
	*/?>
	<?if(($arResult['CLOSE_TEXTBOX']=='Y') && (!empty($arResult['CLOSE_TEXTAREA']))) {?>
		<a href="javascript:void(0);" class="sw24TextCloseButton"><?=$arResult['CLOSE_TEXTAREA']?></a>
	<?}?>
</div>