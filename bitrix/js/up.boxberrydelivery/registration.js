
function c_function (result){
	console.log(result);return false;
	$.ajax({
		url: './ajax.php',
		type: "POST",
		data: {'result':result},
		success: function(data){
			$('.js-get-api_token').hide();
			$( "input[name$='token']" ).val( result.token );
		}
	});
}