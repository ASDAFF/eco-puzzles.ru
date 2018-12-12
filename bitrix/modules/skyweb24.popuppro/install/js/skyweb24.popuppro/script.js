var managerPopupPro={
	type:'action', //selected type popup
	imgType:'IMG_1_SRC', //selected type image for change
	selectedImages:{}, //selected images {type1:id1, ....}
	previewIframe:'',
	renderTimer: undefined,
	updateImgBox:function(imgId){
		$.ajax({
			url: '/bitrix/admin/skyweb24_popuppro.php?ajax=y&command=get_img',
			type: "POST",
			data:{img_type:this.imgType},
			dataType:'html',
			success: function(data){
				$('#popuppro_img_list').html(data);
				if(imgId && imgId>0){
					$('#popuppro_img_list').find('a').each(function(){
						if($(this).data('id') && $(this).data('id')==imgId){
							$('#popuppro_img_list').prepend($(this).closest('figure'));
							return;
						}
					});
				}
			},
			error:function(data){
				console.log(data);
			},
		});
	},
	hint:function(key,hint){
		var keys = key.split('#$%');
		var hints = hint.split('#$%');
		for(var i=0; i<keys.length;i++){
			if(keys[i]!='')
		new BX.CHint({
				parent: BX('hint_'+keys[i]),
				show_timeout: 10,
				hide_timeout: 200,
				dx: 2,
				preventHide: true,
				min_width: 400,
				hint: hints[i]
			});
		}
	},
	rouletteRenderTry:function(){
		if(managerPopupPro.type=="roulette"){
			//setTimeout($('.preview-iframe')[0].contentWindow.paintRoulette(),5000);
		}
	},
	positionPopup:function(){
		managerPopupPro.detailTemplateArea = $('.preview-iframe').contents().find('body #detail_template_area');
		managerPopupPro.detailTemplateArea.css({
			'position':'',
			'top':'',
			'left':'',
			'width':'',
			'right':'',
			'bottom':''
		});
		//managerPopupPro.previewIframe.css({'height':''});
		if(managerPopupPro.detailTemplateArea.width()==0 || managerPopupPro.detailTemplateArea.height()==0){
			setTimeout(managerPopupPro.positionPopup, 10);
		}else{
			setTimeout(function(){
				var tmpLeft='0', tmpTop='0', tmpRight='', tmpBottom='', tmpPosition='absolute';
				managerPopupPro.detailTemplateArea.css({'position':tmpPosition});

				if(managerPopupPro.detailTemplateArea.width()<managerPopupPro.detailTemplateArea.closest('body').width()){
					tmpLeft=(managerPopupPro.detailTemplateArea.closest('body').width()-managerPopupPro.detailTemplateArea.width())/2+'px';
				}
				if(managerPopupPro.detailTemplateArea.height()<managerPopupPro.previewIframe.height()){
					tmpTop=(managerPopupPro.previewIframe.height()-managerPopupPro.detailTemplateArea.height())/2+'px';
					//tmpTop='20px';
				}
				if(managerPopupPro.props){
					if(managerPopupPro.props.POSITION_BOTTOM && managerPopupPro.props.POSITION_BOTTOM=='Y'){
						tmpBottom='0'; tmpTop='';
					}
					if(managerPopupPro.props.POSITION_LEFT && managerPopupPro.props.POSITION_LEFT=='Y'){
						tmpLeft='0'; tmpRight='';
					}
					if(managerPopupPro.props.POSITION_RIGHT && managerPopupPro.props.POSITION_RIGHT=='Y'){
						tmpRight='0'; tmpLeft='';
					}
					if(managerPopupPro.props.POSITION_TOP && managerPopupPro.props.POSITION_TOP=='Y'){
						tmpTop='0'; tmpBottom='';
					}
				}
				
				managerPopupPro.detailTemplateArea.css({
					'position':tmpPosition,
					'top':tmpTop,
					'left':tmpLeft,
					'right':tmpRight,
					'bottom':tmpBottom
				});
			}, 100);
		}
	},
	personalizeReplace:function(val){
		if(!managerPopupPro.personalizeArr && personalize){
			managerPopupPro.personalizeArr=personalize;
		}
		for(var key in managerPopupPro.personalizeArr){
			let tmpVal=managerPopupPro.personalizeArr[key];
			if(tmpVal!=''){
				let reg = new RegExp('#'+key+'#', "gi");
				val=val.replace(reg, tmpVal);
			}
		}
		return val;
	},
	updatePreview:function(newSrc){
		var currentTmplt=templatesPopup[this.type];
		for(i=0; i<currentTmplt.length; i++){
			if(currentTmplt[i]['active']){
				managerPopupPro.detailEditContentBlock.find('input[type=text], input[type=number], input[type=hidden], input[type=range], textarea, select').each(function(){
					if(this.name.indexOf('IMG_')<0){
						var tmpVal=$(this).val();
		tmpVal=managerPopupPro.personalizeReplace(tmpVal);

						if($(this).attr('name')=='CONSENT_LIST' && typeof(agreements) != "undefined"){
							tmpVal=agreements[$(this).val()];
							tmpVal=tmpVal.replace('#BUTTON_TEXT#', currentTmplt[i]['props']['BUTTON_TEXT']);
							tmpVal={key:$(this).val(), value:tmpVal};
						}
						currentTmplt[i]['props'][$(this).attr('name')]=tmpVal;
					}
					if(this.name=='BACKGROUND_COLOR'){
						BX('overlay_simulator').style.background=this.value;
					}
					if(this.name=='BACKGROUND_COLOR'){
						BX('overlay_simulator').style.background=this.value;
					}
					if(this.name=='BACKGROUND_OPACITY'){
						BX('overlay_simulator').style.opacity=this.value/100;
					}
				});
				var tmpTemplate=currentTmplt[i]['templateHTML'];
				if(newSrc){
					templatesPopup[this.type][i]['props'][this.imgType]=newSrc.src;
					tmpTemplate=tmpTemplate.replace('#'+this.imgType+'#', newSrc.src);
					templatesPopup[this.type][i]['props'][this.imgType+'_id']=newSrc.id;
					$('input[name='+this.imgType+']').val(newSrc.id);
				}

				var tmpStructureArr={'REQUIRED':{'N':'', 'Y':'required'}, 'SHOW':{'N':'notshow', 'Y':''}};
				for(var nextProp in currentTmplt[i].props){
					var currentStr=currentTmplt[i].props[nextProp];
					if(nextProp=='CONSENT_LIST'){
						currentStr=currentTmplt[i].props[nextProp].value;
						currentTmplt[i].props[nextProp]=currentTmplt[i].props[nextProp].key;
					}
					if(currentTmplt[i].props[nextProp]=='N' || currentTmplt[i].props[nextProp]=='Y'){
						if(nextProp.indexOf('_REQUIRED')>-1){
							currentStr=tmpStructureArr.REQUIRED[currentTmplt[i].props[nextProp]];
						}else if(nextProp.indexOf('_SHOW')>-1){
							currentStr=tmpStructureArr.SHOW[currentTmplt[i].props[nextProp]];
						}
					}
					var regExp = new RegExp('#'+nextProp+'#','g');
					if(tmpTemplate){
						tmpTemplate=tmpTemplate.replace(regExp, currentStr);
					}
				}
				var rouletteSet = "var dataset = [];";
				var rouletteChecher = 0;
				if(tmpTemplate.indexOf(rouletteSet)>0){
					var container = document.querySelector('.block.roulette tbody');
					rouletteChecher=1;
					var items = container.querySelectorAll('tr');
					var innerDataset = "var dataset = [";
					var tmpDeg = 100/items.length;
					items.forEach(function(element){
						var tmpName = element.querySelector('input').value;
						var tmpColor = element.querySelector('.color_selector').value;
						innerDataset+="{value:"+tmpDeg+",color:'"+tmpColor+"',text:'"+tmpName+"',rule:'nothing'},";
					});
					innerDataset+='];';
					tmpTemplate=tmpTemplate.replace(rouletteSet,innerDataset);
				}
				managerPopupPro.props=currentTmplt[i]['props'];
				//managerPopupPro.detailTemplateArea.html(tmpTemplate);
				managerPopupPro.detailTemplateArea = $('.preview-iframe').contents().find('body #detail_template_area');
				PopupTimer.Timer.UpdateData();
				managerPopupPro.detailTemplateArea[0].innerHTML = PopupTimer.Timer.Html + tmpTemplate;
				managerPopupPro.detailTemplateArea.find('input, button, textarea').prop('disabled', true);

				break;
			}
		}
		//fix iframe
		managerPopupPro.animateCloseButtonBlock();
	},
	setPreviewBlock:function(activeI){
		var currentTmplt=templatesPopup[this.type][activeI];
		if(!managerPopupPro.detailTemplateArea){
			managerPopupPro.detailTemplateArea=$('#detail_template_area_outer').find('.preview-iframe').contents().find('body #detail_template_area');
		}
		var areaHTML=currentTmplt['templateHTML'];
		if(currentTmplt.props){

			var tmpStructureArr={'REQUIRED':{'N':'', 'Y':'required'}, 'SHOW':{'N':'notshow', 'Y':''}};
			for(var nextProp in currentTmplt.props){
				var currentStr=currentTmplt.props[nextProp];
				if(currentStr=='N' || currentStr=='Y'){
					if(nextProp.indexOf('_REQUIRED')>-1){
						currentStr=tmpStructureArr.REQUIRED[currentStr];
					}else if(nextProp.indexOf('_SHOW')>-1){
						currentStr=tmpStructureArr.SHOW[currentStr];
					}
				}
				areaHTML=areaHTML.replace('#'+nextProp+'#', currentStr);
			}
		}

		//managerPopupPro.detailTemplateArea.fadeOut(200, function(){ });

		var iframeDoc=$('#detail_template_area_outer').find('.preview-iframe').contents();

		iframeDoc.find('#popup_template_css').remove();
		iframeDoc.find('#popup_template_color_css').remove();
		iframeDoc.find('head').append('<link rel="stylesheet" id="popup_template_css" href="'+currentTmplt['templateCss']+'/style.css" type="text/css" />');
		iframeDoc.find('head').append('<link rel="stylesheet" href="/bitrix/themes/.default/skyweb24.popuppro_public.css" type="text/css" />');
        iframeDoc.find('head').append('<link rel="stylesheet" href="/bitrix/js/main/core/css/core_popup.css" type="text/css" />');
        iframeDoc.find('head').append('<link rel="stylesheet" href="/bitrix/themes/.default/skyweb24.popuppro.css" type="text/css" />');
		if(currentTmplt['color_style']){
			iframeDoc.find('head').append('<link rel="stylesheet" id="popup_template_color_css" href="'+currentTmplt['templateCss']+'/themes/'+currentTmplt['color_style']+'.css" type="text/css" />');
		}
		managerPopupPro.detailTemplateArea = $('.preview-iframe').contents().find('body #detail_template_area');
		managerPopupPro.detailTemplateArea[0].innerHTML=areaHTML;
		managerPopupPro.detailTemplateArea.find('input, button, textarea').prop('disabled', true);
		managerPopupPro.updatePreview();
		managerPopupPro.renderIframeContent();
	},
	togglePersonalozation:function(o){
		if(!managerPopupPro.personalList){
			managerPopupPro.personalList=$('.personalizationList');
		}
		let nextBlock=$(o).next();
		if(!nextBlock.hasClass('personalizationList')){
			managerPopupPro.personalList.insertAfter($(o));
			nextBlock=$(o).next();
			nextBlock.css('display', 'none');
		}
		let view=(nextBlock.css('display')=='block')?'none':'block';
		nextBlock.css('display', view);
	},
	createTemplateForm:function(type){
		$('.preview-iframe').css('visibility', 'hidden');
		this.type=type;
		var currentTmplt=templatesPopup[type],
			currentListTemplate='<select name="template">',
			activeOptionColor='',
			contentBlock='';
		if(!managerPopupPro.templatesListArea){
			managerPopupPro.templatesListArea=$('#templates_list');
		}
		if(!managerPopupPro.detailTemplateHeader){
			managerPopupPro.detailTemplateHeader=$('.select_block h2');
		}
		var addTemplateName='';
		for(i=0; i<currentTmplt.length; i++){
			var isLocalData = false;
			var tmpName=currentTmplt[i]['name'],
				activeOptionTemplate='';
			if(currentTmplt[i]['active']){
				addTemplateName=currentTmplt[i].name;
				activeOptionTemplate=' selected="selected"';
				activeOptionColor=currentTmplt[i]['color_style'];
				managerPopupPro.detailTemplateHeader.html(tmpName);

				if(templatesType[type]['color_style'] && currentTmplt[i]['color_styles']){
					var activeOptionColors=currentTmplt[i]['color_styles'];
				}
				if(!currentTmplt[i]['templateHTML']){
					isLocalData = false;
					var activeI=i;
					$.ajax({
						url: '/bitrix/admin/skyweb24_popuppro.php?ajax=y&command=gettemplate',
						type: "POST",
						data:{template:type+'_'+currentTmplt[activeI]['template']},
						dataType:'html',
						//processData:false,
						success: function(data){
							currentTmplt[activeI]['templateHTML']=data;
							$.ajax({
								url: '/bitrix/admin/skyweb24_popuppro.php?ajax=y&command=gettemplatepath',
								type: "POST",
								data:{template:type+'_'+currentTmplt[activeI]['template']},
								dataType:'html',
								success: function(data){
									currentTmplt[activeI]['templateCss']=data;
									managerPopupPro.setPreviewBlock(activeI);
								},
								error:function(data){
									console.log(data);
								},
							});

						},
						error:function(data){
							console.log(data);
						},
					});
				}
				else{
					isLocalData = true;
				}
				//content block
				var tmpProp=currentTmplt[i]['props'],
					currentTmpHeader='',hints='',hints_text='',
					usePersonalize=false,
					usePersonalizeMarker=false;
				for(nextProp in tmpProp){
					usePersonalizeMarker=false;
					if((nextProp=='USE_CONSENT_SHOW' || nextProp=='CONSENT_LIST') && typeof(agreements) == "undefined"){
						continue;
					}
					if(templatesType[type]['props'][nextProp] && templatesType[type]['props'][nextProp]['type']!==currentTmpHeader){
						currentTmpHeader=templatesType[type]['props'][nextProp]['type'];
						contentBlock+='<h4>'+popupMessages['titleSet'+currentTmpHeader]+'</h4>';
					}
					if(templatesType[type]['props'][nextProp]){
						if(nextProp.indexOf('IMG_')>-1){
							
							if(templatesType[type]['props'][nextProp]['hint']){
								hint = '<span class="skwb24-item-hint" id="hint_'+nextProp+'">?</span>';
								hints+=nextProp+'#$%';
								hints_text+=templatesType[type]['props'][nextProp]['hint']+'#$%';
							}
							
							var tmpImgVal=(tmpProp[nextProp+'_id'])?tmpProp[nextProp+'_id']:'';
							contentBlock+='<input type="hidden" name="'+nextProp+'" value="'+tmpImgVal+'" /><label><span>'+templatesType[type]['props'][nextProp]['name']+hint+'</span> <a href="javascript:void(0);" class="upload" data-idupload="'+nextProp+'">'+popupMessages.selectImg+'</a></label>';
						}else{
							var inputArea='';
							if(templatesType[type]['props'][nextProp]['tag'] && templatesType[type]['props'][nextProp]['tag']=='select'){
								inputArea='<select name="'+nextProp+'">';

								for(nextSel in templatesType[type]['props'][nextProp]['list']){
									var selectOption=(nextSel==tmpProp[nextProp])?' selected="selected"':'';
									inputArea+='<option value="'+nextSel+'"'+selectOption+'>'+templatesType[type]['props'][nextProp]['list'][nextSel]+'</option>';
								}
								inputArea+='</select>';
								if(nextProp=='RULE_ID'){
									inputArea+='<a href="/bitrix/admin/sale_discount_edit.php?ID='+tmpProp[nextProp]+'" target="_blank">'+rule_info+'</a>';
								}
								if(nextProp=='EMAIL_TEMPLATE'||nextProp=='MAIL_TEMPLATE'){
									inputArea+='<a href="/bitrix/admin/message_edit.php?lang=ru&ID='+tmpProp[nextProp]+'" target="_blank">'+rule_info+'</a>';
								}
							}else if(templatesType[type]['props'][nextProp]['tag'] && templatesType[type]['props'][nextProp]['tag']=='textarea'){
								inputArea='<textarea rows="5"  name="'+nextProp+'">'+tmpProp[nextProp]+'</textarea>';
							}else if(templatesType[type]['props'][nextProp]['tag'] && templatesType[type]['props'][nextProp]['tag']=='checkbox'){
								tmpChecked=(tmpProp[nextProp]=='Y')?' checked="checked"':'';
								tmpHiddenVal=(tmpProp[nextProp]=='Y')?'Y':'N';
								var showparam='';
								if(typeof(templatesType[type]['props'][nextProp]['block'])=="string"){
										if(templatesType[type]['props'][nextProp]['block']=='start'){
												showparam='start';
										}
								}
								if(showparam=='start'){
									var message='';
									if(tmpHiddenVal=='Y'){
										message=popupMessages.hideBlock;
									}else{
										message=popupMessages.ShowBlock;
									}
									if(nextProp=='EMAIL_SHOW') if(type=='roulette'||type=='discount'||type=='coupon') message='';
									inputArea='<a href="javascript:void(0)" class="toggle">'+ message +'</a><input type="hidden" name="'+nextProp+'" value="'+tmpHiddenVal+'" class="'+showparam+'"/>';
								}else{
									inputArea='<input type="checkbox" value="Y"'+tmpChecked+' /><input type="hidden" name="'+nextProp+'" value="'+tmpHiddenVal+'" class="'+showparam+'"/>';
								}

								showparam='';
							}else if(templatesType[type]['props'][nextProp]['tag'] && templatesType[type]['props'][nextProp]['tag']=='color'){
								inputArea='<input type="text" id="'+templatesType[type]['props'][nextProp]['id']+'" name="'+nextProp+'" value="'+tmpProp[nextProp]+'" />';
								this.colorPicker(templatesType[type]['props'][nextProp]['id'], tmpProp[nextProp]);
							}else if(templatesType[type]['props'][nextProp]['tag'] && templatesType[type]['props'][nextProp]['tag']=='number'){
								max=(templatesType[type]['props'][nextProp]['max'])?' max="'+templatesType[type]['props'][nextProp]['max']+'"':'';
								min=(templatesType[type]['props'][nextProp]['max'])?' min="'+templatesType[type]['props'][nextProp]['min']+'"':'';
								step=(templatesType[type]['props'][nextProp]['step'])?' step="'+templatesType[type]['props'][nextProp]['step']+'"':'';
								inputArea='<input type="number" name="'+nextProp+'" value="'+tmpProp[nextProp]+'"'+max+min+step+' />';
							}else if(templatesType[type]['props'][nextProp]['tag'] && templatesType[type]['props'][nextProp]['tag']=='posttemplate'){
								for(nextSel in templatesType[type]['props'][nextProp]['list']){
									inputArea='<input value="'+nextSel+'"  name="'+nextProp+'" type="hidden" />';
									break;
								}
								inputArea+='<a href="/bitrix/admin/message_edit.php?lang=ru&ID='+nextSel+'" target="_blank" style="margin-left:10px;">'+popupMessages.showPostTemplate+'#'+nextSel+'</a>';
							}else if(templatesType[type]['props'][nextProp]['tag'] && templatesType[type]['props'][nextProp]['tag']=='range'){
								inputArea='<input onchange="BX(\''+nextProp+'_range\').value=this.value;" type="range" name="'+nextProp+'" value="'+tmpProp[nextProp]+'" step="'+templatesType[type]['props'][nextProp]['step']+'" min="'+templatesType[type]['props'][nextProp]['min']+'" max="'+templatesType[type]['props'][nextProp]['max']+'" /><output name="'+nextProp+'value"  id="'+nextProp+'_range" for="'+nextProp+'" >'+tmpProp[nextProp]+'</output>';
							}else{
								inputArea='<input type="text" name="'+nextProp+'" value="'+tmpProp[nextProp]+'" />';
							}
							if(templatesType[type]['props'][nextProp].PERSONALISATION && templatesType[type]['props'][nextProp].PERSONALISATION=='Y'){
								usePersonalize=true;
								usePersonalizeMarker=true;
							}
							var hint = '';
							if(templatesType[type]['props'][nextProp]['hint']){
								hint = '<span class="skwb24-item-hint" id="hint_'+nextProp+'">?</span>';
								hints+=nextProp+'#$%';
								hints_text+=templatesType[type]['props'][nextProp]['hint']+'#$%';
							}
							var startblock='',endblock='';
							if(typeof(templatesType[type]['props'][nextProp]['block'])=="string"){
								if(templatesType[type]['props'][nextProp]['block']=='start'){
									startblock = '<div class="block '+tmpHiddenVal+'">';
								}
								if(templatesType[type]['props'][nextProp]['block']=='end'){
									endblock = '</div>';
								}
							}
							if(usePersonalizeMarker){inputArea+='<img src="/bitrix/themes/.default/skyweb24.popuppro/images/personal.png" style="height:20px;width:20px;cursor:pointer;" title="'+popupMessages.personalisationMarker+'">';}
							if(!(type=='discount'&&(nextProp=='EMAIL_ADD2BASE'||nextProp=='EMAIL_NOT_NEW'/*||nextProp==''||nextProp==''*/)))
							contentBlock+=startblock+'<label><span>'+templatesType[type]['props'][nextProp]['name']+hint+'</span> '+inputArea+'</label>'+endblock;
						}
					}
				}

				if(!managerPopupPro.detailEditContentBlock){
					managerPopupPro.detailEditContentBlock=$('#edit_content');
					
					managerPopupPro.detailEditContentBlock.on('change', 'input[type=checkbox]', function(){
						var newVal=(this.checked)?'Y':'N';
						if($(this).closest('div.block').length>0){
							if($(this).next().hasClass('start')){
								$(this).closest('div.block').removeClass('Y').removeClass('N');
								$(this).closest('div.block').addClass(newVal);
							}
						}
						$(this).next().val(newVal);
						var tmpName=$(this).next().attr('name');
						if(tmpName=='EMAIL_NOT_NEW'){
							let viewEmailTextMode=(newVal=='Y')?'flex':'none';
							managerPopupPro.detailEditContentBlock.find('input[name=EMAIL_NOT_NEW_TEXT]').closest('label').css('display', viewEmailTextMode);
						}
						else if(tmpName=='EMAIL_EMAIL_TO'){
							let viewEmailTextMode=(newVal=='Y')?'flex':'none';
							managerPopupPro.detailEditContentBlock.find('input[name=EMAIL_TEMPLATE]').closest('label').css('display', viewEmailTextMode);
						}
						/*if(tmpName=='POSITION_LEFT' || tmpName=='POSITION_RIGHT' || tmpName=='POSITION_TOP' || tmpName=='POSITION_BOTTOM'){
							var uncheckO={
								'POSITION_LEFT':'POSITION_RIGHT',
								'POSITION_RIGHT':'POSITION_LEFT',
								'POSITION_TOP':'POSITION_BOTTOM',
								'POSITION_BOTTOM':'POSITION_TOP',
							};
							var linkInput=managerPopupPro.detailEditContentBlock.find('input[name='+uncheckO[tmpName]+']');
							linkInput.parent().find('input[type=checkbox]').prop('checked', false);
							linkInput.val('N');
						}*/

						managerPopupPro.updatePreview();
						managerPopupPro.renderIframeContent();
					});
					
					//fix
					setTimeout(function(){
						$checkedEmail=managerPopupPro.detailEditContentBlock.find('input[name=EMAIL_EMAIL_TO]');
						if($checkedEmail){
							let tmpTmpltViewMode=($checkedEmail.val()=='Y')?'flex':'none';
							managerPopupPro.detailEditContentBlock.find('input[name=EMAIL_TEMPLATE]').closest('label').css('display', tmpTmpltViewMode);
						}
					}, 20);
					
					////////////////////////////// 30.05
					managerPopupPro.detailEditContentBlock.on('click', 'a.toggle', function(){
						var newVal = ($(this).next().val() == 'Y') ? 'N' : 'Y'
						$(this).text( newVal == 'Y' ? popupMessages.hideBlock : popupMessages.ShowBlock );
						if($(this).closest('div.block').length>0){
							if($(this).next().hasClass('start')){
								$(this).closest('div.block').removeClass('Y').removeClass('N');
								$(this).closest('div.block').addClass(newVal);
							}

						}
						$(this).next().val(newVal);
						managerPopupPro.updatePreview();
						managerPopupPro.renderIframeContent();
					});

					////////////////////////////// 30.05
				}
				managerPopupPro.detailEditContentBlock.html(contentBlock);
				if(usePersonalize){
					let contentH=managerPopupPro.detailEditContentBlock.find('h4').eq(0);
					$('<a href="javascript:void(0);" class="personalization">'+popupMessages.personalisation+'</a>').insertAfter(contentH);
				}
				managerPopupPro.detailEditContentBlock.find('input[type=text], textarea').on('keyup', function(){
					managerPopupPro.updatePreview(false);
					managerPopupPro.renderIframeContent(this);
				});
				managerPopupPro.detailEditContentBlock.find('input[type=number], input[type=range], select').on('change', function(){
					managerPopupPro.updatePreview(false);
					managerPopupPro.renderIframeContent(this);
				});
				managerPopupPro.hint(hints,hints_text);
				$('.block.roulette').find('select').on('change',function(){
					managerPopupPro.updatePreview(false);
					managerPopupPro.renderIframeContent(this);
				});
				$('.block.roulette').find('input').on('keyup',function(){
					managerPopupPro.updatePreview(false);
					managerPopupPro.renderIframeContent(this);
				});
				$('.block.roulette').find('input[name="roulette_element_count"]').on('change',function(){
					managerPopupPro.updatePreview(false);
					managerPopupPro.renderIframeContent(this);
				});
			}
			currentListTemplate+='<option'+activeOptionTemplate+' value="'+currentTmplt[i]['template']+'">'+tmpName+'</option>';
			if(isLocalData){
				managerPopupPro.setPreviewBlock(i);
			}
		}
		currentListTemplate+='</select> <a href="javascript:void(0);" class="addCustomTemplate">'+popupMessages.addColorTemplate+'  "<span>'+addTemplateName+'</span>"</a>';

		//colors themes
		if(templatesType[type]['color_style']){
			var currentListColors='<select name="color_style">';
			var tmpColors=templatesType[type]['color_style'];
			if(activeOptionColors){
				tmpColors=activeOptionColors;
			}
			var addcustomColorName='',
				optIsGroup=false,
				optGroupEnd='';
			/*for(var nextColor in tmpColors){
				activeOptionTemplate='';
				optGroupStart='';
				if(nextColor.indexOf('custom_')>-1 && !optIsGroup){

					optGroupStart='<optgroup label="'+popupMessages.additional+'">';
					optGroupEnd='</optgroup>';
					optIsGroup=true;
				}
				if(nextColor==activeOptionColor){
					activeOptionTemplate=' selected="selected"';
					addcustomColorName=tmpColors[nextColor];
				}
				currentListColors+=optGroupStart+'<option'+activeOptionTemplate+' value="'+nextColor+'">'+tmpColors[nextColor]+'</option>';
			}*/
			optGroupStart='';
			optGroupEnd='</optgroup>';
			for(var nextColor in tmpColors){
				activeOptionTemplate='';
				tmpOptGroupStart=popupMessages['color_main'];
				if(nextColor.indexOf('custom_')>-1 && optGroupStart!=popupMessages.additional){
					tmpOptGroupStart=popupMessages.additional;
				}else if(nextColor.indexOf('_')>-1){
					tmpOptGroupStart=nextColor.split('_');
					tmpOptGroupStart=tmpOptGroupStart[0];
					if(popupMessages['color_'+tmpOptGroupStart]){
						tmpOptGroupStart=popupMessages['color_'+tmpOptGroupStart];
					}else{
						tmpOptGroupStart=popupMessages['color_main'];
					}
				}
				if(nextColor==activeOptionColor){
					activeOptionTemplate=' selected="selected"';
					addcustomColorName=tmpColors[nextColor];
				}
				if(tmpOptGroupStart!=optGroupStart && optGroupStart==''){
					currentListColors+='<optgroup label="'+tmpOptGroupStart+'">';
				}else if(tmpOptGroupStart!=optGroupStart && optGroupStart!=''){
					currentListColors+=optGroupEnd+'<optgroup label="'+tmpOptGroupStart+'">';
				}
				currentListColors+='<option'+activeOptionTemplate+' value="'+nextColor+'">'+tmpColors[nextColor]+'</option>';
				optGroupStart=tmpOptGroupStart;
			}
			currentListColors+=optGroupEnd+'</select> <a href="javascript:void(0);" class="addCustomColorTheme">'+popupMessages.addColorTheme+' "<span>'+addcustomColorName+'</span>"</a>';
			currentListColors=$(currentListColors);

			if(!managerPopupPro.detailEditViewBlock){
				managerPopupPro.detailEditViewBlock=$('#edit_view');
			}
			managerPopupPro.detailEditViewBlock.html(currentListColors);
			currentListColors.change(function(){
				var currentId=$(this).val();
				for(i=0; i<templatesPopup[type].length; i++){
					if(templatesPopup[type][i]['active']){
						templatesPopup[type][i]['color_style']=currentId;
						break;
					}
				}
				managerPopupPro.createTemplateForm(type);
			});
		}else{
			$('#edit_view').html('');
		}

		managerPopupPro.templatesListArea.html(currentListTemplate);
		managerPopupPro.templatesListArea.find('select').change(function(){
			var currentId=$(this).val();
			for(i=0; i<templatesPopup[type].length; i++){

				if(currentId==templatesPopup[type][i]['template']){
					templatesPopup[type][i]['active']=true;
				}else{
					templatesPopup[type][i]['active']=false;
				}
			}
			managerPopupPro.createTemplateForm(type);
		});
		/* managerPopupPro.updatePreview(); */
		managerPopupPro.animateCloseButtonBlock();
		managerPopupPro.animatePositionBlock();
		managerPopupPro.animateTimerPositionBlock();
	},
	colorPicker:function(id, color){
		if(!BX(id)){
			let _this=this;
			setTimeout(function(){
				_this.colorPicker(id, color);
			}, 50);
		}else{
			BX.bind(BX(id), 'focus', function () {
				new BX.ColorPicker({
					bindElement: BX(id),
					defaultColor: color,
					allowCustomColor: true,
					onColorSelected: function (item) {
						BX(id).value = item
					},
					popupOptions:{
						angle: true,
						autoHide: true,
						closeByEsc: true,
						events: {
							onPopupClose: function () {
								managerPopupPro.updatePreview();
							}
						}
					}
				}).open();
			})
		}
	},
	hideCalendar:function(){
        $('.popup-window .bx-calendar .bx-calendar-header').hide();
        $('.popup-window .bx-calendar .bx-calendar-name-day-wrap').hide();
        $('.popup-window .bx-calendar .bx-calendar-cell-block').hide();
    },
	showCalendar:function(){
        $('.popup-window .bx-calendar .bx-calendar-header').show();
        $('.popup-window .bx-calendar .bx-calendar-name-day-wrap').show();
        $('.popup-window .bx-calendar .bx-calendar-cell-block').show();
    },
	animateTimerPositionBlock(){
		var checkedTimer=$('.block.timer').find('input[name=timer_enable]').prop('checked');
		var positionBlockLeft=$('.block.timer').find('input[name=timer_left]');
		var positionBlockRight=$('.block.timer').find('input[name=timer_right]');
		var positionBlockTop=$('.block.timer').find('input[name=timer_top]');
		var positionBlockBottom=$('.block.timer').find('input[name=timer_bottom]');
		if(positionBlockLeft.length>0){
			$('.block.timer').find('input[name=timer_left],input[name=timer_right],input[name=timer_top],input[name=timer_bottom]').closest('label').css('display', 'none');
			if(!managerPopupPro.positionBlockTimer){
				managerPopupPro.positionBlockTimer=$('.positionTimer');
			}
			if(checkedTimer){
				managerPopupPro.positionBlockTimer.css('display', 'block');
			}
			managerPopupPro.positionBlockTimer.children().removeClass('active');
			managerPopupPro.positionBlockTimer.insertBefore(positionBlockLeft.closest('label'));
			var activeSelector='';
			if(positionBlockLeft.val()=='Y'){activeSelector+='.left';}
			if(positionBlockRight.val()=='Y'){activeSelector+='.right';}
			if(positionBlockTop.val()=='Y'){activeSelector+='.top';}
			if(positionBlockBottom.val()=='Y'){activeSelector+='.bottom';}
			if(activeSelector==''){
				activeSelector='.left.top';
			}
			managerPopupPro.positionBlockTimer.find(activeSelector).addClass('active');
			$('.positionTimer').find('.top, .bottom').click(function(){
				$('.block.timer').find('input[name=timer_left],input[name=timer_right],input[name=timer_top],input[name=timer_bottom]').val('N');
				$(this).parent().children().removeClass('active');
				$(this).addClass('active');
				var leftRight=($(this).hasClass('left'))?'left':'right';
					topBottom=($(this).hasClass('top'))?'top':'bottom';
				$('.block.timer').find('input[name^=timer_'+leftRight+']').val('Y');
				$('.block.timer').find('input[name^=timer_'+topBottom+']').val('Y');
			});
			if(!checkedTimer){
				$('.block.timer').find('label.toggle').css('display', 'none');
			}
		}
	},
	animateCloseButtonBlock(){
		let closeTextChecked=$('#edit_content').find('input[name=CLOSE_TEXTBOX]'),
			closeTextArea=$('#edit_content').find('input[name=CLOSE_TEXTAREA]'),
			textareaDisplay=(closeTextChecked.val()=='Y')?'flex':'none';
			closeTextArea.closest('label').css('display', textareaDisplay);
	},
	animatePositionBlock(){
		var positionBlockLeft=$('#edit_content').find('input[name=POSITION_LEFT]');
		var positionBlockRight=$('#edit_content').find('input[name=POSITION_RIGHT]');
		var positionBlockTop=$('#edit_content').find('input[name=POSITION_TOP]');
		var positionBlockBottom=$('#edit_content').find('input[name=POSITION_BOTTOM]');
		var activeSelector='';
		if(positionBlockLeft.val()=='Y'){activeSelector+='.left';}
		if(positionBlockRight.val()=='Y'){activeSelector+='.right';}
		if(positionBlockTop.val()=='Y'){activeSelector+='.top';}
		if(positionBlockBottom.val()=='Y'){activeSelector+='.bottom';}
		if(activeSelector==''){
			activeSelector='.center';
		}
		if(positionBlockLeft.length>0){
			if(!managerPopupPro.positionBlockAnimator){
				managerPopupPro.positionBlockAnimator=$('.exampleWindow');
			}
			managerPopupPro.positionBlockAnimator.css('display', 'block');
			managerPopupPro.positionBlockAnimator.find('.positionBlockAnimator').children().removeClass('active');
			managerPopupPro.positionBlockAnimator.insertBefore(positionBlockLeft.closest('label'));
			$('#edit_content').find('input[name^=POSITION_]').closest('label').css('display', 'none');
			$('#edit_content').find('input[name=POSITION_FIXED]').closest('label').css('display', '');
			if($('.positionBlockAnimator').find(activeSelector).length>1){
				$('.positionBlockAnimator').find(activeSelector).eq(1).addClass('active');
			}else{
				$('.positionBlockAnimator').find(activeSelector).addClass('active');
			}
			$('.positionBlockAnimator').find('.top, .bottom, .left, .right, .center').click(function(){
				$(this).parent().children().removeClass('active');
				$(this).addClass('active');
				//$('#edit_content').find('input[name^=POSITION_]').val('N');
				
				positionBlockLeft.val('N');
				positionBlockRight.val('N');
				positionBlockTop.val('N');
				positionBlockBottom.val('N');
				
				managerPopupPro.props.POSITION_BOTTOM='N';
				managerPopupPro.props.POSITION_LEFT='N';
				managerPopupPro.props.POSITION_RIGHT='N';
				managerPopupPro.props.POSITION_TOP='N';

				if($(this).hasClass('top')){positionBlockTop.val('Y'); managerPopupPro.props.POSITION_TOP='Y';}
				if($(this).hasClass('bottom')){positionBlockBottom.val('Y'); managerPopupPro.props.POSITION_BOTTOM='Y';}
				if($(this).hasClass('left')){positionBlockLeft.val('Y'); managerPopupPro.props.POSITION_LEFT='Y';}
				if($(this).hasClass('right')){positionBlockRight.val('Y'); managerPopupPro.props.POSITION_RIGHT='Y';}
				managerPopupPro.positionPopup();
			});
		}
	},
	renderIframeContent: function(changeElem){
		clearTimeout(managerPopupPro.renderTimer);
		managerPopupPro.renderTimer = setTimeout(() => {
			var html = $('.preview-iframe').contents()[0].documentElement.innerHTML;
			$(".preview-iframe")[0].contentDocument.open();
			$(".preview-iframe")[0].contentDocument.write(html);
			$(".preview-iframe")[0].contentDocument.close();
			$('.preview-iframe').contents().find('#detail_template_area').css('display', 'block');
			let showButton=true;
			if(managerPopupPro.props.SHOW_CLOSEBUTTON && managerPopupPro.props.SHOW_CLOSEBUTTON=='N'){
				showButton=false;
			}
			if(showButton){
				$('.preview-iframe').contents().find('#detail_template_area').append('<div class="popup-window-close-icon"></div>');
			}
			//<div class="popup-window-close-icon">hi</div>
			$('.preview-iframe').contents().find('body').css({'margin': '0', "width": "100%"});
			$('.preview-iframe').contents().find('body').addClass('popup-window');
			$('.preview-iframe').contents().find('body').addClass('sw24PopupPro');
			managerPopupPro.correctPositionBecauseTimer();
			PopupTimer.Timer.StartTimer($('.preview-iframe').contents().find('.clock'));
			$(changeElem).focus();
			//managerPopupPro.positionPopup();
			setTimeout(() => {
				managerPopupPro.positionPopup();
				$('.preview-iframe').css('visibility', 'visible');
			}, 100);
			setTimeout(() => managerPopupPro.rouletteRenderTry(), 100);
		},250);
	},
	correctPositionBecauseTimer: function(){
		 var detail_area_elem = $('.preview-iframe').contents().find('#detail_template_area');
		 detail_area_elem.css({
			 "margin-top": "0",
			 "maring-bottom": "0"
		 });
		 if(PopupTimer.Timer.IsOnTimer && PopupTimer.Timer.IsPopSuppot){
		   if(PopupTimer.Timer.IsTop)
		 		 detail_area_elem.css('margin-top', '50px');
			 else{
				 detail_area_elem.css('margin-bottom', '50px');
			 }
		 }
	}
}


function sliderWorks(){
	$('.slide_type .wrapper').on('click', 'a', function(){
		$(this).parent().find('a').removeClass('active');
		$(this).addClass('active');
		var desc=($(this).data('description'))?$(this).data('description'):'';
		var target=($(this).data('target'))?$(this).data('target'):'';
		$('#subslider_desc').html(desc);
		$('#subslider_target').html(target);
		$('input[name=type]').val($(this).data('id'));
		managerPopupPro.createTemplateForm($(this).data('id'));
		selectContactTab();
	})
	$('.slide_type .wrapper a').each(function(){
		if($(this).hasClass('active')){
			managerPopupPro.createTemplateForm($(this).data('id'));
			return;
		}
	})
}
function resort(){
	var container=$('.block.roulette tbody');
	var items = container.find('tr');
	for(var i=0;i<items.length;i++){
		$(items[i]).find('td:nth-child(2)')[0].innerHTML=(i+1);
		$(items[i]).find('select.color_selector').attr('name','roulette_'+(i+1)+'_color');
		$(items[i]).find('select.rule_selector').attr('name','roulette_'+(i+1)+'_rule');
		$(items[i]).find('input').attr('name','roulette_'+(i+1)+'_text');
	}
}
function color_selector(selector){
	var color=selector.val();
	selector.css('background',color).css('color',color);
}

function remove_roulette_row(row){
	if($(row).closest('tbody').find('tr').length>4){
		count=$(row).closest('tbody').find('tr').length-1;
		$(row).closest('tr').remove();
		$('input[name="roulette_element_count"]').val(count);
		$('input[name="roulette_element_count"]').change();
		chance_change();
	}else{
		alert(minimum_message);
	}
}
function chance_change(){
		var data = [];
		var gravity_sum = 0;
		$('.block.roulette').find('table').find('select.roulette_chance_gravity').each(function(){
			gravity_sum += parseInt($(this).val());
		})
		$('.block.roulette').find('table').find('select.roulette_chance_gravity').each(function(){
			var gravity = parseInt($(this).val());
			$(this).closest('td').find('input.roulette_chance_hidden').val(Math.round(gravity / (gravity_sum / 100) * 10) / 10);
			$(this).closest('td').find('span.roulette_chance').text(Math.round(gravity / (gravity_sum / 100) * 10) / 10 + '%');
		});
	}
function row_rule_url(){
	var elements = $('div.roulette table tbody.drag_container tr.draggable');
	for(var i=0;i<elements.length;i++){
		var rule=$(elements[i]).find('select.rule_selector').val();
		$(elements[i]).find('select.rule_selector').closest('td').find('a').remove();
		if(rule>0){
			var url="/bitrix/admin/sale_discount_edit.php?ID="+(rule)+"&lang=ru";
			$(elements[i]).find('select.rule_selector').closest('td').append('<a href="'+url+'" target="_blank">'+rule_info+'</a>');
		}
	}

}
var drag;var smartTipsTree;



$(document).ready(function(){
	chance_change();
	
	//personalization
	$(document).on('click', 'a.personalization', function(){
		managerPopupPro.togglePersonalozation(this);
	})
	
	var tmpFrame = new BXBlockEditorPreview({'context':BX('detail_template_area_outer'), 'site':'s1', 'url':'/bitrix/admin/skyweb24_popuppro.php'});
	tmpFrame.changeDevice = function(deviceNode){
		$('.preview-iframe').css('visibility', 'hidden');
		if(!deviceNode)
		{
			deviceNode = this.deviceList[0];
		}

		var width = deviceNode.getAttribute('data-bx-preview-device-width');
		var height = deviceNode.getAttribute('data-bx-preview-device-height');
		var className = deviceNode.getAttribute('data-bx-preview-device-class');

		var classNameList = [];
		for(var i in this.deviceList)
		{
			var deviceNodeTmp = this.deviceList[i];
			if(!deviceNodeTmp)
			{
				break;
			}
			if(deviceNodeTmp !== deviceNode)
			{
				BX.removeClass(deviceNodeTmp, 'active');
			}
			classNameList.push(deviceNodeTmp.getAttribute('data-bx-preview-device-class'));
		}
		BX.addClass(deviceNode, 'active');

		var frameWrapper = BX.findChildByClassName(this.previewContext, 'iframe-wrapper', true);
		if(frameWrapper)
		{
			BX.removeClass(frameWrapper, classNameList.join(' '));
			BX.addClass(frameWrapper, className);
		}

		this.iframePreview.style.width = width + 'px';
		this.iframePreview.style.height = height + 'px';
	}
	managerPopupPro.previewIframe=$('#detail_template_area_outer').find('.preview-iframe');
	$('.devices .device').on('click', function(){
		//managerPopupPro.positionPopup();
		//$('.preview-iframe').css('visibility', 'hidden');
		setTimeout(() => {
			//$('.site_background').contents().find('body').css('overflow', 'hidden');
			managerPopupPro.positionPopup();
			setTimeout(()=> $('.preview-iframe').css('visibility', 'visible'), 100);
		}, 600);
	});
	tmpFrame.changeDevice(document.querySelector('.devices .desktop'));

	$('#edit_content').on('change','select[name="RULE_ID"]',function(){
		$(this).val();
		$(this).closest('label').find('a').attr('href','/bitrix/admin/sale_discount_edit.php?ID='+$(this).val());
	});
	$('#edit_content').on('change','select[name="EMAIL_TEMPLATE"]',function(){
		$(this).val();
		$(this).closest('label').find('a').attr('href','/bitrix/admin/message_edit.php?lang=ru&ID='+$(this).val());
	});
	$('#edit_content').on('change','select[name="MAIL_TEMPLATE"]',function(){
		$(this).val();
		$(this).closest('label').find('a').attr('href','/bitrix/admin/message_edit.php?lang=ru&ID='+$(this).val());
	});

	for(var i=0;i<$('.block.roulette tbody').find('select.color_selector').length;i++){
		color_selector($($('.block.roulette tbody').find('select.color_selector')[i]));
		row_rule_url();
	}
	
	$('.block.roulette').on('change','select.roulette_chance_gravity',chance_change);

	$('.block.roulette').on('change','select.rule_selector',function(){
		row_rule_url();
	});
	$('.block.roulette').on('click','a.add-roulette-row',function(){
		count=$('.block.roulette tbody').find('tr').length;
		var append_row='<tr class="adm-list-table-row draggable">';
			append_row+='<td class="adm-list-table-cell">';
				append_row+='<div class="adm-list-table-popup drag_key" draggable="true"></div>';
			append_row+='</td>';
			append_row+='<td class="adm-list-table-cell">';
				append_row+=count+1;
			append_row+='</td>';
			append_row+='<td class="adm-list-table-cell">';
				append_row+='<input type="text" size=50 name="roulette_'+(count+1)+'_text">';
			append_row+='</td>';
			append_row+='<td class="adm-list-table-cell">';
				append_row+='<select class="color_selector" name="roulette_'+(count+1)+'_color">';
				for(var i in colors_for_roulette)
					append_row+='<option value="'+i+'" style="background:'+i+';color:'+i+'">'+colors_for_roulette[i]+'</option>';
				append_row+='</select>';
			append_row+='</td>';
			append_row+='<td class="adm-list-table-cell">';
				append_row+='<select name="roulette_'+(count+1)+'_rule">';
				for(var i in basket_rule_for_roulette){
					if(i=='nothing') append_row+='<optgroup label="'+basket_rule_basic+'">';
					if(i==tmpFirstBasketRule) append_row+='<optgroup label="'+basket_rule_rules+'">';
					append_row+='<option value="'+i+'">'+basket_rule_for_roulette[i]+'</option>';
					if(i==tmpLastBasketRule) append_row+='</optgroup>';
					if(i=='win') append_row+='</optgroup>';
				}
				append_row+='</select>';
			append_row+='</td>';
			append_row+='<td class="adm-list-table-cell"><select class="roulette_chance_gravity" name="roulette_'+i+'_gravity">\
	<option selected="selected">100</option>\
	<option>90</option>\
	<option>80</option>\
	<option>70</option>\
	<option>60</option>\
	<option>50</option>\
	<option>40</option>\
	<option>30</option>\
	<option>20</option>\
	<option>10</option>\
	<option>0</option>\
</select>\
<input name="roulette_'+i+'_chance" class="roulette_chance_hidden" step="0.01" type="hidden" value="0">\
<span class="roulette_chance"></span></td>'
			append_row+='<td class="adm-list-table-cell">';
				append_row+='<a href="javascript:;" onclick="remove_roulette_row(this);"><img width="20px" height="25px" src="data:image/svg+xml;utf8;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMTkuMC4wLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iTGF5ZXJfMSIgeD0iMHB4IiB5PSIwcHgiIHZpZXdCb3g9IjAgMCAzNzguMzAzIDM3OC4zMDMiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDM3OC4zMDMgMzc4LjMwMzsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHdpZHRoPSI1MTJweCIgaGVpZ2h0PSI1MTJweCI+Cjxwb2x5Z29uIHN0eWxlPSJmaWxsOiNGRjM1MDE7IiBwb2ludHM9IjM3OC4zMDMsMjguMjg1IDM1MC4wMTgsMCAxODkuMTUxLDE2MC44NjcgMjguMjg1LDAgMCwyOC4yODUgMTYwLjg2NywxODkuMTUxIDAsMzUwLjAxOCAgIDI4LjI4NSwzNzguMzAyIDE4OS4xNTEsMjE3LjQzNiAzNTAuMDE4LDM3OC4zMDIgMzc4LjMwMywzNTAuMDE4IDIxNy40MzYsMTg5LjE1MSAiLz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPC9zdmc+Cg=="></a>';
			append_row+='</td>';
		append_row+='</tr>';
		$('.block.roulette tbody').append(append_row);
		for(var i=0;i<$('.block.roulette tbody').find('select.color_selector').length;i++){
			color_selector($($('.block.roulette tbody').find('select.color_selector')[i]));
		}

		drag = BX.DragDrop.create({
			'dragItemControlClassName':'drag_key',
			'dragItemClassName':'draggable',
			'dragActiveClass':'sorting',
			'dropZoneList':document.querySelector('tbody.drag_container'),
			'dragEnd':resort,
			'sortable':{
				'rootElem':document.querySelector('tbody.drag_container'),
				'gagClass':'sort',
			}
		});
		$('input[name="roulette_element_count"]').val((count+1));
		$('input[name="roulette_element_count"]').change();
		row_rule_url();
		chance_change();
	});
	$('.block.roulette tbody').on('change','select.color_selector',function(){
		color_selector($(this));
	});
	drag = BX.DragDrop.create({
		'dragItemControlClassName':'drag_key',
		'dragItemClassName':'draggable',
		'dragActiveClass':'sorting',
		'dropZoneList':document.querySelector('tbody.drag_container'),
		'dragEnd':resort,
		'sortable':{
			'rootElem':document.querySelector('tbody.drag_container'),
			'gagClass':'sort',
		}
	});


	var slideRoot=$('.slide_type');
	if(managerPopupPro.type){
		slideRoot.find('a').each(function(){
			if($(this).hasClass('active')){
				return;
			}
			slideRoot.append($(this));
		});
	}
	slideRoot.skwb24Slider();
	sliderWorks();
	selectContactTab();

	$('.popuppro_detail').on('click', 'a.upload', function(){
		managerPopupPro.imgType=$(this).data('idupload');
		var popup=new BX.CDialog({
			'title':popupMessages.titlePopupImgBlock,
			'content':BX('popuppro_manager_files'),
			'width':800,
			'height':500
		});

		BX.addCustomEvent(popup, 'onWindowRegister',function(){
			$('#popuppro_manager_files').css('display','block');
			managerPopupPro.updateImgBox();
		});

		BX.addCustomEvent(popup, 'onWindowClose',function(){
			$('#popuppro_manager_files').css('display','none');
			managerPopupPro.updateImgBox();
		});
		popup.Show();
	});

	//fix file uploader
	$('#popuppro_manager_files').find('a.file-selectdialog-switcher').trigger('click');

	$('#popuppro_img_list').on('click', 'img', function(){
		managerPopupPro.updatePreview({'src':this.src, 'id':$(this).data('id')});
		managerPopupPro.renderIframeContent();
	});

	$('input[name=cancel]').click(function(){
		location.reload();
	});

	$('.add_product_field').click(function(){
		var tmpFields=$(this).closest('td').find('.button_add');
		$('<div class="button_add"><input name="saleIDProdInBasket[]" id="saleIDProdInBasket_'+tmpFields.length+'" value="" size="5" type="text"> <input type="button" value="..." onclick="jsUtils.OpenWindow(\'/bitrix/admin/iblock_element_search.php?lang=ru&amp;n=saleIDProdInBasket_'+tmpFields.length+'&amp;k=n&amp;\', 900, 700);"> <span id="sp_saleIDProdInBasket_'+tmpFields.length+'"></span></div>').insertBefore($(this));
	});

	$('.select_block').on('change', 'select[name=contact_iblock]', function(){
		var checked=($(this).val()!='')?true:false;
		$(this).closest('div').find('input[name=contact_save_to_iblock]').prop('checked', checked);
	});

	$('.select_block').on('click', '.addCustomTemplate', function(){
		var ajaxData={
			type:managerPopupPro.type,
			template:managerPopupPro.templatesListArea.find('select').val()
		};
		$(this).parent().append('<div class="setnewtemplate"> <span>'+popupMessages.enterName+':</span> <input type="text" placeholder="'+popupMessages.nameIsRequired+'" name="template_name" value="'+$(this).find('span').text()+'_2" /> <a href="javascript:void(0);" class="adm-btn adm-btn-save">'+popupMessages.create+'</a></div>');
		$(this).remove();
		$('.select_block').on('click', '.setnewtemplate a', function(){
			var inputName=$(this).parent().find('input'),
				replaceBlock=$(this).parent();
			if(inputName.prop('disabled')==false && inputName.val()!==''){
				ajaxData.name=inputName.val();
				inputName.prop('disabled', true);
				$.ajax({
					url: '/bitrix/admin/skyweb24_popuppro.php?ajax=y&command=add_custom_template',
					type: "POST",
					data:ajaxData,
					dataType:'json',
					//dataType:'html',
					success: function(data){
						var successBlock='<span class="successBlock">'+popupMessages.customTemplateCreateSuccess+': <a href="/bitrix/admin/fileman_file_edit.php?path='+data.newPath+'" target="_blank">'+popupMessages.edit+'</a></span>';
						replaceBlock.replaceWith(successBlock);
						templatesPopup[ajaxData.type].push(data.popup);
						managerPopupPro.templatesListArea.find('select[name=template]').append('<option value="'+data.code+'">'+ajaxData.name+'['+data.id+']'+'</option>');
						managerPopupPro.templatesListArea.find('select[name=template] option').prop('selected', false);
						managerPopupPro.templatesListArea.find('select[name=template] option').last().prop('selected', true);
						//managerPopupPro.templatesListArea.find('select[name=template]').trigger('change');
					},
					error:function(data){
						console.log(data);
					}
				});
			}
		});
	});
	
	smartTipsTree=new BX.TreeConditions({
		'parentContainer': 'popupPropsCont',
		'form': 'popupPropsCont',
		'formName': 'detail_prop',
		'sepID': '__',
		'prefix': 'rule'
	},condPopupPros,popupProps);
	
	//insert hint to treeconditions
	var rootCondBlock=$('#popupPropsCont'),
		condDescriptions={};
	for(key in popupProps){
		if(popupProps[key].children){
			for(keyChildren in popupProps[key].children){
				var nextChildren=popupProps[key].children[keyChildren]
				if(nextChildren.description){
					condDescriptions[nextChildren.controlId]=nextChildren.description;
				}
			}
		}
	}
	
	$('#popupPropsCont').on('change', 'select[id*=popupPropsCont__]', updateHint);
	function updateHint(){
		var i=0;
		rootCondBlock.find('.condition-wrapper input[type=hidden]').each(function(){
			if(condDescriptions[$(this).val()]){
				colorizeCondition();
				var currentHint=$(this).parent().find('.skwb24-item-hint');
				if(currentHint.length==0){
					$('<span class="skwb24-item-hint" id="hint_condition_'+i+'">?</span>').insertAfter($(this));
					new top.BX.CHint({
						parent: top.BX("hint_condition_"+i),
						show_timeout:10,
						hide_timeout:200,
						dx:2,
						preventHide:true,
						min_width:400,
						hint:condDescriptions[$(this).val()]
					});
				}
			}
			i++;
		})
	}
	updateHint();
	
	function colorizeCondition(){
		let cSelect=$('#popupPropsCont__0_add_select'),
			styleList={};
		cSelect.find('optgroup').each(function(index){
			$(this).find('option').each(function(indexOption){
				styleList[$(this).val()]='color_'+index;
			})
		})
		for(key in styleList){
			$('input[value='+key+']').closest('.condition-simple-control').addClass(styleList[key]);
		}
	}
	
	$('.select_block').on('click', '.addCustomColorTheme', function(){
		var ajaxData={
			type:managerPopupPro.type,
			template:managerPopupPro.templatesListArea.find('select').val(),
			color_style:managerPopupPro.detailEditViewBlock.find('select[name=color_style]').val()
		};
		$(this).parent().append('<div class="setnewcolor"> <span>'+popupMessages.enterNameColor+':</span> <input type="text" placeholder="'+popupMessages.nameIsRequired+'" name="color_style_name" value="'+$(this).find('span').text()+'_2" /> <a href="javascript:void(0);" class="adm-btn adm-btn-save">'+popupMessages.create+'</a></div>');
		$(this).remove();
		$('.select_block').on('click', '.setnewcolor a', function(){
			var inputName=$(this).parent().find('input'),
				replaceBlock=$(this).parent();
			if(inputName.prop('disabled')==false && inputName.val()!==''){
				ajaxData.name=inputName.val();
				inputName.prop('disabled', true);
				$.ajax({
					url: '/bitrix/admin/skyweb24_popuppro.php?ajax=y&command=add_custom_colortheme',
					type: "POST",
					data:ajaxData,
					dataType:'json',
					success: function(data){
						var successBlock='<span class="successBlock">'+popupMessages.colorThemeCreateSuccess+': <a href="/bitrix/admin/fileman_file_edit.php?path='+data.newPath+'" target="_blank">'+popupMessages.edit+'</a></span>';
						replaceBlock.replaceWith(successBlock);
						for(var i=0; i<templatesPopup[ajaxData.type].length; i++){
							if(templatesPopup[ajaxData.type][i]['template']==ajaxData.template){
								templatesPopup[ajaxData.type][i]['color_styles'][data.code]=ajaxData.name+'['+data.id+']';
								break;
							}
						}
						managerPopupPro.detailEditViewBlock.find('select[name=color_style]').append('<option value="'+data.code+'">'+ajaxData.name+'['+data.id+']'+'</option>');
						managerPopupPro.detailEditViewBlock.find('select[name=color_style] option').prop('selected', false);
						managerPopupPro.detailEditViewBlock.find('select[name=color_style] option').last().prop('selected', true);
					},
					error:function(data){
						console.log(data);
					}
				});
			}
		});
	});

	$('form[name="detail_popup"]').on('click','.condition-simple-control a',function(e){
        if(e.currentTarget.id.indexOf('_time_')+1){
            var tmp_name=e.currentTarget.id.replace('_link','');
            if($('#'+tmp_name).val().indexOf(' ')+1)
                $('#'+tmp_name)[0].value=$('#'+tmp_name).val().split(' ')[1];
            if($('.popup-window .bx-calendar').length==0)
                $('#'+tmp_name+'_icon').on('click',managerPopupPro.hideCalendar);
            managerPopupPro.hideCalendar();
            $('#'+tmp_name).on('change',function(){
                if($(this).val().indexOf(' ')+1){
                    var tmp_val=$(this).val().split(' ')[1];
                    $(this).val(tmp_val);
                    e.currentTarget.innerHTML=tmp_val;
                    managerPopupPro.showCalendar();
                }
            });
        }else{
            var tmp_name=e.currentTarget.id.replace('_link','');
            if($('.popup-window .bx-calendar').length==1)
                $('#'+tmp_name+'_icon').on('click',managerPopupPro.showCalendar);
            managerPopupPro.showCalendar();
        }
    });

		$('.block.timer input:not(:hidden)').on('change', function(){
			if($(this).attr('name')=='timer_enable'){
				let modeBlock='none',
					modeLabel='none';
				if($(this).prop('checked')){
					modeBlock='block';
					modeLabel='flex';
				}
				$('.block.timer').find('label.toggle').css('display', modeLabel);
				$('.block.timer').find('div.toggle').css('display', modeBlock);
			}
			managerPopupPro.updatePreview(false);
			managerPopupPro.renderIframeContent();
		});

		$('.block.timer .positionTimer').on('click', () => {
			managerPopupPro.updatePreview(false);
			managerPopupPro.renderIframeContent();
		});
		
		
		$('input[name=contact_save_to_iblock]').on('change', function(){
			let listGroupArea=$('select[name=contact_iblock]').closest('label'),
				cDisplay=($(this).prop('checked'))?'flex':'none';
				listGroupArea.css('display', cDisplay);
		});
		
		$('input[name=contact_save_to_list]').on('change', function(){
			let listGroupArea=$('select[name=contact_groupmail]').closest('label'),
				cDisplay=($(this).prop('checked'))?'flex':'none';
				listGroupArea.css('display', cDisplay);
		});
});

function showHideImgs(direct){
	if(direct=='show_all'){
		$('#popuppro_img_list').find('figure').css('display', 'inline-block');
		$('#popuppro_img_list').find('.hide_all').css('display', 'block');
		$('#popuppro_img_list').find('.show_all').css('display', 'none');
	}else{
		$('#popuppro_img_list').find('figure').css('display', 'none');
		$('#popuppro_img_list').find('figure').slice(0,4).css('display', 'inline-block');
		$('#popuppro_img_list').find('.show_all').css('display', 'block');
		$('#popuppro_img_list').find('.hide_all').css('display', 'none');
	}
}

function delPopupImg(o){
	var _this=$(o);
	$.ajax({
		url: '/bitrix/admin/skyweb24_popuppro.php?ajax=y&command=del_img',
		type: "POST",
		data:{id:_this.data('id')},
		dataType:'json',
		success: function(data){
			_this.closest('figure').remove();
		},
		error:function(data){
			console.log(data);
		}
	});
}

BX.addCustomEvent('uploadFinish', function(result){
	var uploadImgId=0;
	if(result.element_id){
		uploadImgId=result.element_id;
	}
	managerPopupPro.updateImgBox(uploadImgId);
});
BX.addCustomEvent('stopUpload', function(result){
	setTimeout(function(){managerPopupPro.updateImgBox();}, 100);
});

jQuery.fn.skwb24Slider = function(options){
	var settings = $.extend({
		'orientation' : 'horizontal',//vertical
		'slides':3
	}, options);
	if(this.find('.wrapper').length>0){
		this.html(this.find('.wrapper').html());
	}
	if(settings.orientation=='horizontal'){
		var wrapper=$('<div class="wrapper"></div>');
		var slides=this.find('.slide');
		this.width(this.width());
		slidesWidth=Math.round(this.width()/settings.slides)-25;
		slides.width(slidesWidth);
		wrapper.height(slides.outerHeight()+2).css({'text-align':'center', 'position':'relative'}).append(slides);
		this.append(wrapper);
		this.append('<a href="javascript:void(0);" class="arrow horizontal left"></a><a href="javascript:void(0);" class="arrow horizontal right"></a>');
		settings.wrapper=wrapper;
	}
	this.find('a.arrow').click(function(){
		var block_width = settings.wrapper.find(".slide").outerWidth();
		if($(this).hasClass('left')){
			settings.wrapper.find(".slide").eq(-1).clone().prependTo(settings.wrapper);
			settings.wrapper.css({"left":"-"+block_width+"px"});
			settings.wrapper.find(".slide").eq(-1).remove();
			settings.wrapper.animate({left: "0px"}, 200);
		}else{
			settings.wrapper.animate({left: "-"+ block_width +"px"}, 200, function(){
				settings.wrapper.find(".slide").eq(0).clone().appendTo(settings.wrapper);
				settings.wrapper.find(".slide").eq(0).remove();
				settings.wrapper.css({"left":"0px"});
			});
		}
	})
};

function selectContactTab(){
	if(managerPopupPro && managerPopupPro.type=='contact'){
		$('.block.contacts').css('display','block');
	}else{
		//$('.block.contacts').find('.info').html('<h2 class="error">'+popupMessages.errorContactTabSetting+'</h2>');
		$('.block.contacts').css('display','none');
	}
	if(managerPopupPro && (managerPopupPro.type=='banner'||managerPopupPro.type=='video'||managerPopupPro.type=='action'||managerPopupPro.type=='contact'||managerPopupPro.type=='html'||managerPopupPro.type=='coupon'||managerPopupPro.type=='roulette'||managerPopupPro.type=='discount')){
		$('.block.timer').css('display','block');
	}else{
		$('.block.timer').css('display','none');
	}
	if(managerPopupPro && managerPopupPro.type=='roulette'){
		$('.block.roulette').css('display','block');
	}else{
		$('.block.roulette').css('display','none');
	}
}

function selectPreviewTab(){
	if($('.slide_type').width()==0){
		$('.slide_type').css('width', '');
		$('.slide_type').skwb24Slider();
		sliderWorks();
	}
}

function parseDateTime(val){
	var dateObj;
	try{
		var datetimeArr = val.split(" ");
		var dateArr = datetimeArr[0].split(".");
		var timeArr = datetimeArr[1].split(":");
		dateObj = new Date(dateArr[2], dateArr[1], dateArr[0], timeArr[0], timeArr[1], timeArr[2], 0);
	}
	catch(error){
		dateObj = new Date()
	}
	return dateObj;
}

let _timerObj;
//let _htmlTimer;
class PopupTimer{
	static get Timer(){
		if(_timerObj == undefined){
			_timerObj = new PopupTimer();
		}
		return _timerObj;
	}

	constructor(){
		$.ajax({
			type: "POST",
			url: "/bitrix/admin/skyweb24_popuppro.php?ajax=y&command=gettimertemplate",
			async: false,
			success: (data) => this._htmlTimer = data
		});
		this._timerSettingBlock = $('.block.timer');
		this._nameTimerClass = "skyweb24_popup_pro_timer";
	}

	get Html(){
		let html = ''
		if(this._isPopupSupport && this._isOnTimer){
			let jElement = $(this._htmlTimer);
			jElement.css('box-sizing', 'border-box');
			//position
			jElement[0].className = this._nameTimerClass;
			this._classesPos.forEach((v) => jElement[0].className += ' ' + v);
			//text
			jElement.find("span.title").text(this._text);
			html = jElement[0].outerHTML;
		}
		return html;
	}

	get IsTop(){
		return this._isTop;
	}

	get IsPopSuppot(){
		return this._isPopupSupport;
	}

	get IsOnTimer(){
		return this._isOnTimer;
	}

	UpdateData(){
		this._isPopupSupport = this._timerSettingBlock.css('display') != 'none';
		this._isOnTimer = this._timerSettingBlock.find('input[name="timer_enable"]').is(':checked');
		this._text = this._timerSettingBlock.find('input[name="timer_text"]').val();
		this._dateEnd = parseDateTime(this._timerSettingBlock.find('input[name="timer_date"]').val());
		this._dateServer = parseDateTime(this._timerSettingBlock.find('div.dateServer').text());
		var classesPos = this._timerSettingBlock.find('.positionTimer .active').prop('className').split(' ');
		this._classesPos = [];
		this._isTop = false;
		classesPos.forEach((v) => {
			if(v == "top" || "bottom" || "left" || "right"){
				this._classesPos.push(v);
				if(v == "top") this._isTop = true;
			}
		});
	}

	StartTimer(clockElem){
		this.StopTimer();
		if(!(this._isPopupSupport && this._isOnTimer))
			return;


		this._leftSeconds = Math.abs(this._dateEnd - this._dateServer) / 1000;
		this._interval = setInterval(() => {

			let days = Math.floor(this._leftSeconds / 86400);
			let hours = Math.floor((this._leftSeconds - 86400 * days) / 3600 % 3600);
			let mins = Math.floor(this._leftSeconds / 60 % 60);
			let seconds = Math.floor(this._leftSeconds % 60);

			if(this._leftSeconds < 0){
				this.StopTimer();
				days = 0;
				hours = 0;
				mins = 0;
				seconds = 0;
			}

			if(hours < 10) hours = '0' + hours;
			if(mins < 10) mins = '0' + mins;
			if(seconds < 10) seconds = '0' + seconds;

			let html = `<span>${days}</span>` + '<span class="sep">:</span>';
			html += `<span>${hours}</span>` + '<span class="sep">:</span>';
			html += `<span>${mins}</span>` + '<span class="sep">:</span>';
			html += `<span>${seconds}</span>`;

			clockElem.html(html);

			this._leftSeconds--;

		}, 1000);
	}

	StopTimer(){
		if(this._interval != undefined)
			clearInterval(this._interval);
		this._interval = undefined;
	}
}
