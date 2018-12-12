skyweb24_effects.effects.zoom=function(){
	this.targetO.style.opacity=1;
	this.targetO.style.display='block';
	this.showChildren();
	var tmpPosObject=this.targetO.getBoundingClientRect(),
		oldHeight=tmpPosObject.height,
		oldWidth=tmpPosObject.width,
		oldLeft=tmpPosObject.left,
		oldTop=tmpPosObject.top,
		startO={height:0, width:0},
		finishO={height:oldHeight*1.2, width:oldWidth*1.2},
		currentO=this.targetO;
		
	this.targetO.style.height='0';
	this.targetO.style.width='0';
	
	var anim = new BX.easing({
			duration : 200,
			start : {height:0, width:0, left:(oldLeft*1+oldWidth/2), top:(oldTop*1+oldHeight/2)},
			finish : {height:oldHeight, width:oldWidth, left:oldLeft, top:oldTop},
			transition : BX.easing.transitions.linear,
			step : function(state){
				currentO.style.height = state.height+'px';
				currentO.style.width = state.width+'px';
				currentO.style.left = state.left+'px';
				currentO.style.top = state.top+'px';
			},
			complete : function() {}
		});
		anim.animate();
}