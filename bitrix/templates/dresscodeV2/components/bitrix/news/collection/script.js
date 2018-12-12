$(function(){

	var changeSortParams = function(){
		window.location.href = $(this).val();
	};

	$("#selectSortParams, #selectCountElements").on("change", changeSortParams);
});