<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$vk = $arResult['ID_VK'];
$inst = $arResult['ID_INST'];
$odnkl = $arResult['ID_ODNKL'];?>
		<?
		$count=0;
		if(!empty($vk))
				$count++;
		if(!empty($inst))
				$count++;
		if(!empty($odnkl))
				$count++;
		$count*=300;
		?>
<div id="skyweb24_social_all" style="
background: <?=$arResult['COLOR_BG']?>;
background: -moz-linear-gradient(top,<?=$arResult['COLOR_BG']?> 0%, #fff 200%);
background: -webkit-linear-gradient(top, <?=$arResult['COLOR_BG']?> 0%,#fff 200%);
background: linear-gradient(to bottom,<?=$arResult['COLOR_BG']?> 0%,#fff 200%);
filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='<?=$arResult['COLOR_BG']?>', endColorstr='#fff',GradientType=0 );
width:<?=$count?>px;
<?if(!empty($arResult['GOOGLE_FONT'])){?>font-family:<?=$arResult['GOOGLE_FONT']?>;<?}?>
">
<?if(!empty($arResult['GOOGLE_FONT'])){?><link href="https://fonts.googleapis.com/css?family=<?=$arResult['GOOGLE_FONT']?>:400,700" rel="stylesheet"><?}?>

	<h2>
		<?=$arResult['TITLE']?>
	</h2>
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
	<div class="social_holder">
		<?if(!empty($arResult['ID_VK'])){?>
		<!-- VK Widget -->
		<div id="vk_groups"></div>
		<script type="text/javascript">
            checkElement('#vk_groups').then((e) => {
                var vk_groups = document.getElementById('vk_groups');
                var wrapper = vk_groups.parentElement;
                var scriptVK = document.createElement('script');
                scriptVK.src = 'https://vk.com/js/api/openapi.js?154';
                wrapper.appendChild(scriptVK);
                scriptVK.onload = () => {VK.Widgets.Group('vk_groups', {mode: 5, width:'300', height:'316'}, <?=$arResult['ID_VK']?>)};
            });
			// setTimeout(function(){
			// 	var vk_js = document.createElement("script");
			// 	document.head.appendChild(vk_js);
			// 	vk_js.src = "https://vk.com/js/api/openapi.js?146";
			// 	vk_js.onload = vk_js.onreadystatechange = function () {
			// 		var loadVK=function(){
			// 			var targetVK=document.getElementById('vk_groups');

			// 			if(targetVK){
			// 				targetVK.innerHTML='';
			// 				VK.Widgets.Group("vk_groups", {mode: 5, width: "300",height:"316"}, <?=$arResult['ID_VK']?>);
			// 			}
			// 		}
			// 		loadVK();
			// 	}
			// }, 50);
		</script>

		<?}?>
		<?if(!empty($arResult['ID_ODNKL'])){?>
		<div id="ok_group_widget"></div>
		<script>
        checkElement('#ok_group_widget').then((e) => {
            var ok_groups = document.getElementById('ok_group_widget');
            var wrapper = ok_groups.parentElement;
            var scriptOK = document.createElement('script');
            scriptOK.src = 'https://connect.ok.ru/connect.js';
            wrapper.appendChild(scriptOK);
            scriptOK.onload = () => {
                OK.CONNECT.insertGroupWidget('ok_group_widget','<?=$arResult['ID_ODNKL']?>','{"width":300,"height":316}');
            };
        });

		// setTimeout(function(){
		// 	!function (d, id, did, st) {
		// 	  var js = d.createElement("script");
		// 	  js.src = "https://connect.ok.ru/connect.js";
		// 	  js.onload = js.onreadystatechange = function () {
		// 	  if (!this.readyState || this.readyState == "loaded" || this.readyState == "complete") {
		// 		if (!this.executed) {
		// 		  this.executed = true;

		// 			var loadOK=function(){
		// 				var targetOK=document.getElementById(id);
		// 				if(targetOK){
		// 					targetOK.innerHTML='';
		// 					OK.CONNECT.insertGroupWidget(id,did,st);
		// 				}else{
		// 					//setTimeout(loadOK, 200);
		// 				}
		// 			}
		// 			loadOK();

		// 		}
		// 	  }}
		// 	  d.documentElement.appendChild(js);
		// 	}(document,"ok_group_widget","<?=$arResult['ID_ODNKL']?>",'{"width":300,"height":316}');
		// }, 50);
		</script>
		<?}?>
		<?if(!empty($arResult['ID_INST'])){?>
		<div id='inst_wid'>
		<iframe
			src="//widget.instagramm.ru/?imageW=3&imageH=2&thumbnail_size=88&type=0&typetext=<?=$arResult['ID_INST']?>&head_show=1&profile_show=1&shadow_show=0&bg=255,255,255,1&opacity=true&head_bg=46729b&subscribe_bg=46729b&border_color=999999&head_title="
			allowtransparency="true"
			frameborder="0"
			scrolling="no"
			style="border:none;overflow:hidden;width:300px;height:316px;"></iframe>
		</div>
		<?}?>
		<?if(($arResult['CLOSE_TEXTBOX']=='Y') && (!empty($arResult['CLOSE_TEXTAREA']))) {?>
		<div><a href="javascript:void(0);" class="sw24TextCloseButton"><?=$arResult['CLOSE_TEXTAREA']?></a></div>
		<?}?>
		<div class="clear"></div>
	</div>
</div>
