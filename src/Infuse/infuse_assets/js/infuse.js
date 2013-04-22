(function($) {
$(document).ready(function() {
	
	$('.selectedDateTime').datetimepicker({ dateFormat: 'yy-mm-dd', timeFormat: 'hh:mm:ss' });

	$(".infuseBoolean").change(function(){
		var checked = $(this).is(':checked');

		var data {
			id : $(this).data("id"),
			$(this).attr("name") : checked
		};

		$.ajax({
			type: 'POST',
			url: $(this).data("url"),
			data: data,
			success: function (data) { 
			 console.log(data);
			},
			error: function (jqXHR, textStatus, errorThrown) {
			  //console.log('AJAX call failed: ' + textStatus + ' ' + errorThrown);
			}
		}); // End of Ajax 
	});
	
});
})(jQuery);


