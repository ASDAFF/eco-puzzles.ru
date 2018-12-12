<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="skyweb24_popup_pro_timer <?echo ($arParams['LEFT']=='Y')?'left':'right'?> <?echo ($arParams['TOP']=='Y')?'top':'bottom'?>">
    
    <svg width="30px"  height="30px"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" class="lds-bricks">
    <rect ng-attr-fill="{{config.c1}}" ng-attr-x="{{config.x}}" ng-attr-y="{{config.x}}" ng-attr-width="{{config.w}}" ng-attr-height="{{config.w}}" ng-attr-rx="{{config.radius}}" ng-attr-ry="{{config.radius}}" fill="#ff7c81" x="21.5" y="21.5" width="25" height="25" rx="3" ry="3">
      <animate attributeName="x" calcMode="linear" values="21.5;53.5;53.5;53.5;53.5;21.5;21.5;21.5;21.5" keyTimes="0;0.083;0.25;0.333;0.5;0.583;0.75;0.833;1" dur="1.5" begin="-1.375s" repeatCount="indefinite"></animate>
      <animate attributeName="y" calcMode="linear" values="21.5;53.5;53.5;53.5;53.5;21.5;21.5;21.5;21.5" keyTimes="0;0.083;0.25;0.333;0.5;0.583;0.75;0.833;1" dur="1.5" begin="-1s" repeatCount="indefinite"></animate>
    </rect>
    <rect ng-attr-fill="{{config.c2}}" ng-attr-x="{{config.x}}" ng-attr-y="{{config.x}}" ng-attr-width="{{config.w}}" ng-attr-height="{{config.w}}" ng-attr-rx="{{config.radius}}" ng-attr-ry="{{config.radius}}" fill="#ffec58" x="21.5" y="53.5" width="25" height="25" rx="3" ry="3">
      <animate attributeName="x" calcMode="linear" values="21.5;53.5;53.5;53.5;53.5;21.5;21.5;21.5;21.5" keyTimes="0;0.083;0.25;0.333;0.5;0.583;0.75;0.833;1" dur="1.5" begin="-0.875s" repeatCount="indefinite"></animate>
      <animate attributeName="y" calcMode="linear" values="21.5;53.5;53.5;53.5;53.5;21.5;21.5;21.5;21.5" keyTimes="0;0.083;0.25;0.333;0.5;0.583;0.75;0.833;1" dur="1.5" begin="-0.5s" repeatCount="indefinite"></animate>
    </rect>
    <rect ng-attr-fill="{{config.c3}}" ng-attr-x="{{config.x}}" ng-attr-y="{{config.x}}" ng-attr-width="{{config.w}}" ng-attr-height="{{config.w}}" ng-attr-rx="{{config.radius}}" ng-attr-ry="{{config.radius}}" fill="#7cd7ff" x="53.5" y="42.919" width="25" height="25" rx="3" ry="3">
      <animate attributeName="x" calcMode="linear" values="21.5;53.5;53.5;53.5;53.5;21.5;21.5;21.5;21.5" keyTimes="0;0.083;0.25;0.333;0.5;0.583;0.75;0.833;1" dur="1.5" begin="-0.375s" repeatCount="indefinite"></animate>
      <animate attributeName="y" calcMode="linear" values="21.5;53.5;53.5;53.5;53.5;21.5;21.5;21.5;21.5" keyTimes="0;0.083;0.25;0.333;0.5;0.583;0.75;0.833;1" dur="1.5" begin="0s" repeatCount="indefinite"></animate>
    </rect>
  </svg>
    <span class="title ">
        <?=$arResult['TITLE']?>
    </span>
    <div class="timer">
        <div>
            <span><?=GetMessage("SW24_POPUPPRO_TIMER_DAY")?></span><br>
        </div>
        <div>
            <span><?=GetMessage("SW24_POPUPPRO_TIMER_HOUR")?></span><br>
        </div>
        <div>
            <span><?=GetMessage("SW24_POPUPPRO_TIMER_MINUTES")?></span><br>
        </div>
        <div>
            <span><?=GetMessage("SW24_POPUPPRO_TIMER_SECOND")?></span><br>
        </div>
        <div class="clock">
            <?$tmp = explode(':',$arResult['TIME']);
            foreach($tmp as $key=>$t){?><span><?=$t?></span><?if($key!=3){echo "<span class='sep'>:</span>";}?><?}?>
        </div>
    </div>
</div>