skyweb24_effects.effects.attenuation=function(){
	this.targetO.style.opacity=1;
	var self=this;
	var tmpPosObject=this.targetO.getBoundingClientRect(),
		startO={opacity:100},
		finishO={opacity:0},
		currentO=this.targetO,
		anim = new BX.easing({
			duration : 1200,
			start : startO,
			finish : finishO,
			transition : BX.easing.transitions.linear,
			step : function(state){
				currentO.style.opacity = state.opacity/100;
			},complete:function(){
				//self.popup.hideOverlay();
				self.popup.destroy();
			}
		});
	anim.animate();
}