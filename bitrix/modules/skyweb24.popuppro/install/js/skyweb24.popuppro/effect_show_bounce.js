skyweb24_effects.effects.bounce=function(){
	this.targetO.style.opacity=1;
	this.targetO.style.display='block';
	
	this.showChildren();
	var tmpPosObject=this.targetO.getBoundingClientRect(),
		oldHeight=tmpPosObject.height,
		oldTop=tmpPosObject.top,
		oldWidth=tmpPosObject.width,
		oldLeft=tmpPosObject.left,
		currentO=this.targetO;
	
	anim = new BX.easing({
		duration : 400,
		start : {height:0, width:0, left:(oldLeft*1+oldWidth/2), top:(oldTop*1+oldHeight/2)},
		finish : {height:oldHeight, width:oldWidth, left:oldLeft, top:oldTop},
		//transition : BX.easing.transitions.linear,
		transition : BX.easing.makeEaseOut(BX.easing.transitions.bounce),
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