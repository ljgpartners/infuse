(function($) {
$(document).ready(function() {

	var animating = false;
	
	$('.selectedDateTime').datetimepicker({ dateFormat: 'yy-mm-dd', timeFormat: 'HH:mm:ss', pickerTimeFormat: 'hh-mm-tt' });

	$('.selectedDate').datepicker({ dateFormat: 'yy-mm-dd'});

	$(".infuseBoolean").change(function(){
		var checked = $(this).is(':checked');
		var columnName = String($(this).attr("name"));

		var data = {
			"id" : $(this).data("id"),
			"value" : checked,
			"column_name" : columnName
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


	$(".infuseManage").bind("click", function(event) {
		event.preventDefault();
		var self = $(this);
		if (self.data("open") == true && !animating) {
			animating = true;
			$(".infuseSideMenu").animate({left:"-=360px"}, 500, function() {
				self.data("open", false)
				animating = false;
			});
		} else if (self.data("open") == false && !animating) {
			animating = true;
			$(".infuseSideMenu").animate({left:"+=360px"}, 500, function() {
				self.data("open", true);
				animating = false;
			})
		}
	});

	var filterCount = 0;

	$(".filterColumn").click(function(event) {
		event.preventDefault();
		filterCount++;
		var self = $(this);
		
		var input = $("<input class='filterInput"+filterCount+"' type='text' >");
		$(".appendFilters").append(input.wrap("<div class='control-group'>"));
		var magicSugggest = $(".filterInput"+filterCount).magicSuggest({
				width: 495,
				name: 'filter_'+filterCount,
				maxSelectioninteger: 3,
				emptyTextstring: "Type here",
				displayField: 'value',
		    value: [self.text(), 'equals'],
		    data: [
		    		{ id: self.text(), value: self.text()}, 
		    		{ id: 'equals', value: 'equals' }, 
		    		{ id: 'less than', value: 'less than'}, 
		    		{ id: 'greater than', value: 'greater than'}, 
		    		{ id: 'not equal to', value: 'not equal to'}
		      ]
			});

		$(".filtersContainer").slideDown();
		$(".filterCount").val(filterCount);
	});
	
});
})(jQuery);


