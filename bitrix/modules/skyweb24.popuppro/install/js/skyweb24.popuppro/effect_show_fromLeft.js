skyweb24_effects.effects.fromLeft=function(){
	this.targetO.style.opacity=0;
	this.targetO.style.display='block';
	this.showChildren();
	
	var tmpPosObject=this.targetO.getBoundingClientRect(),
		startO={left:-tmpPosObject.width, opacity:0},
		finishO={left:(tmpPosObject.left), opacity:100},
		currentO=this.targetO,
		anim = new BX.easing({
			duration : 800,
			start : startO,
			finish : finishO,
			transition : BX.easing.transitions.linear,
			step : function(state){
				currentO.style.opacity = state.opacity/100;
				currentO.style.left = state.left+'px';
			},
			complete : function() {}
		});
		anim.animate();
		
}