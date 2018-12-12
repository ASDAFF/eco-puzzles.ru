$(function(){
	$('input[type="radio"], input[type="checkbox"]').each(function(i, nextElement){
		var $nextElement = $(nextElement);
		$nextElement.after($("<label/>", {"for": $nextElement.attr("id")}));
	});
});