$(window).on("ready", function(){
	$(document).on("click", ".scrollElement ins", function(e){
		$this = $(this);
		$this.addClass("delete");
		$.get(ajaxPath + "?act=compDEL&id=" + $this.data("id"), function(data){
			if(data == 1){
				document.location.reload();
			}
		});
	});
	(function($){
		var propList = $("ul.propList");
		var checkList = $("ul.check");
		var checkListElements = $("ul.check > li");
		var propListElements = propList.find("li");
		var scrollTable = $("#scrollTable > ul, #fakeScroll");
		var topScroll = $("#topScroll");
		var scrollTableElements = $("#scrollTable > ul > li");
		var scrollTableElementsCount = $("ul.check li").length / $("ul.check").length; 
		var listIndex = 0;
		var hide = true;
		propListElements.hover(function(e){

			listIndex = $(this).index();
			propListElements.removeClass("selected");
			propListElements.filter(":nth-child(" + (listIndex + 1)  + ")").addClass("selected");

		},function(){
			propListElements.removeClass("selected");	
		});
		
		scrollTable.width((scrollTableElements.length * 310 - 8) + "px");


		if(scrollTable.width() < $("#scrollTable").width()){
			topScroll.hide();
			$("#compareBlock .left .propList").css("margin-top", "90px");
		}else{
			$("#compareBlock .left .propList").css("margin-top", 100 + $("#topScroll").height() +"px");	
		}
		
		$(window).resize(function(e){
			if(scrollTable.width() < $("#scrollTable").width()){
				topScroll.hide();
				$("#compareBlock .left .propList").css("margin-top", "90px");
			}else{
				$("#compareBlock .left .propList").css("margin-top", 100 + $("#topScroll").height() +"px");
				topScroll.show();
			}
		});

		topScroll.scroll(function(e){
			$("#scrollTable").scrollLeft($(this).scrollLeft());
		});
		
		$("#scrollTable").scroll(function(e){
			topScroll.scrollLeft($(this).scrollLeft());
		});


		$("#compareTools a.hide").on("click",function(e){
			$("#compareCheck input:checked").each(function(index){
				propList.find('[data-id="' + $(this).prop("id") + '"]').hide();
			})
			e.preventDefault();
		});
		
		$("#compareTools a.show").on("click",function(e){
			$("#compareCheck input:checked").each(function(index){
				propListElements.show();
			});
			e.preventDefault();
		});

		$(".leftTools a.all").on("click", function(e){
			propListElements.show();
			e.preventDefault();
		});

		$(".leftTools a.different").on("click", function(e){
			for (var i = 1; i <= scrollTableElementsCount; i++){
				hide = true;
				for (var c = 0; c <= checkList.length -1; c++){
					if(c + 1 <= checkList.length -1){
						if(checkList.eq(c).find('li:nth-child(' + i + ')').html() !=checkList.eq(c+1).find('li:nth-child(' + i + ')').html()){
							hide = false;
							break;
						}			
					}
				}
				
				if(hide == true){
					propList.find('li:nth-child(' + i + ')').hide();
				}		
			
			}
			e.preventDefault();
		});

		$(window).scroll(function(){
			if($(this).scrollTop() > $(".propList").offset().top -120){
				$("#compareLine").fadeIn();	
			}else{
				$("#compareLine").fadeOut();	
			}
		});
	}($));
});
