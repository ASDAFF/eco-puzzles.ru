<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$blocks = array();
if(!empty($arResult['ID_VK'])){
	$blocks[]='ID_VK';
}
if(!empty($arResult['ID_INST'])){
	$blocks[]='ID_INST';
}
if(!empty($arResult['ID_ODNKL'])){
	$blocks[]='ID_ODNKL';
}
$rand = array_rand($blocks,1);
$choosen = $blocks[$rand];
$arResult['COLOR_BG']=(empty($arResult['COLOR_BG']))?'#fff':$arResult['COLOR_BG'];
// $choosen == 'ID_VK';
?>
<script>
    function checkElement(selector) {
        if (document.querySelector(selector) === null) {
            return rafAsync().then(() => checkElement(selector));
        } else {
            return Promise.resolve(true);
        }
    }
    function rafAsync() {
        return new Promise(resolve => {
            requestAnimationFrame(resolve); 
        });
    }
</script>
<div id="skyweb24_banner_default" style="background:<?=$arResult['COLOR_BG']?>;<?if(!empty($arResult['GOOGLE_FONT'])){?>font-family:<?=$arResult['GOOGLE_FONT']?><?}?>">
<?if(!empty($arResult['GOOGLE_FONT'])){?><link href="https://fonts.googleapis.com/css?family=<?=$arResult['GOOGLE_FONT']?>:400,700" rel="stylesheet"><?}?>
	<div class="top_border"></div>
	<div class="left_border"></div>
	<h2><?=$arResult['TITLE']?></h2>
	<?$img='vk.png';
		if($choosen == 'ID_VK'){
	?>
		<img src="<?=$templateFolder?>/img/<?=$img?>">
			<div id="skyweb24_vk_groups"></div>
            <script>
                checkElement('#skyweb24_vk_groups').then((e) => {
                    var vk_groups = document.getElementById('skyweb24_vk_groups');
                    var wrapper = vk_groups.parentElement;
                    var scriptVK = document.createElement('script');
                    scriptVK.src = 'https://vk.com/js/api/openapi.js?154';
                    wrapper.appendChild(scriptVK);
                    scriptVK.onload = () => {VK.Widgets.Group('skyweb24_vk_groups', {mode: 5, width:'auto', height:'316'}, <?=$arResult['ID_VK']?>)};
                });
            </script>
	<?}elseif($choosen == 'ID_INST'){
			$img='instagram.png';
			?>
			<img src="<?=$templateFolder?>/img/<?=$img?>">
			<iframe
			src="//widget.instagramm.ru/?width=auto&imageW=3&imageH=2&thumbnail_size=88&type=0&typetext=<?=$arResult['ID_INST']?>&head_show=1&profile_show=1&shadow_show=0&bg=255,255,255,1&opacity=true&head_bg=46729b&subscribe_bg=46729b&border_color=999999&head_title="
			allowtransparency="true"
			frameborder="0"
			scrolling="no"
			style="border:none;overflow:hidden;width:296px;height:316px;text-align:center;"></iframe>

	<?}elseif($choosen=='ID_ODNKL'){
		$img='odnkl.png';?>
		<img src="<?=$templateFolder?>/img/<?=$img?>">
		<div id="ok_group_widget"></div>
		<script>
        
        checkElement('#ok_group_widget').then((e) => {
            var ok_groups = document.getElementById('ok_group_widget');
            var wrapper = ok_groups.parentElement;
            var scriptOK = document.createElement('script');
            scriptOK.src = 'https://connect.ok.ru/connect.js';
            wrapper.appendChild(scriptOK);
            scriptOK.onload = () => {
                OK.CONNECT.insertGroupWidget('ok_group_widget','<?=$arResult['ID_ODNKL']?>','{"width":305,"height":316}');
            };

            //remove dublicate
            checkElement('#__okGroup1').then((e) => {
                var dublicate = document.getElementById('__okGroup1');
                ok_groups.removeChild(dublicate);
            });
        });

		// setTimeout(function(){
		// 	!function (d, id, did, st) {
		// 	  var js = d.createElement("script");
		// 	  js.src = "https://connect.ok.ru/connect.js";
		// 	  js.onload = js.onreadystatechange = function () {
		// 	  if (!this.readyState || this.readyState == "loaded" || this.readyState == "complete") {
		// 		if (!this.executed) {
		// 		  this.executed = true;
		// 		  setTimeout(function () {
		// 			OK.CONNECT.insertGroupWidget(id,did,st);
		// 		  }, 0);
		// 		}
		// 	  }}
		// 	  d.documentElement.appendChild(js);
		// 	}(document,"ok_group_widget","<?=$arResult['ID_ODNKL']?>",'{"width":305,"height":316}');
		// }, 50);
		</script>
	<?}?>
	<?if(($arResult['CLOSE_TEXTBOX']=='Y') && (!empty($arResult['CLOSE_TEXTAREA']))) {?>
		<div><a href="javascript:void(0);" class="sw24TextCloseButton"><?=$arResult['CLOSE_TEXTAREA']?></a></div>
	<?}?>
	<div class="right_border"></div>
	<div class="bottom_border"></div>
</div>
