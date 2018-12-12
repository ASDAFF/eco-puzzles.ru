skyweb24_effects={
	targetO:'',
	show:function(obj, typeShow){
		this.targetO=obj.popupContainer;
		this.popup=obj;
		if(!typeShow || !this.effects[typeShow]){
			this.showChildren();
		}else{
			this.effects[typeShow].call(this);
		}
	},
	showChildren:function(){
		elems=this.targetO.childNodes;
		if(this.targetO){
			for(var i=0; i<elems.length; i++){
				elems[i].style.opacity=1;
			}
		}
	},
	effects:{}
}