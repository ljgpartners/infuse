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

		var self = $(this),
				input = $("<input class='filterInput"+filterCount+"' type='text' >");

		$(".appendFilters").append(input.wrap("<div class='control-group'>"));

		var magicSugggest = $(".filterInput"+filterCount).magicSuggest({
				width: 495,
				name: 'filter_'+filterCount,
				maxSelectioninteger: 3,
				emptyText: "Type here",
				displayField: 'value',
		    value: [self.data("filter-column"), 'equals'],
		    data: [
		    		{ id: self.data("filter-column"), value: self.text()}, 
		    		{ id: 'equals', value: 'equals' }, 
		    		{ id: 'less than', value: 'less than'}, 
		    		{ id: 'greater than', value: 'greater than'}, 
		    		{ id: 'not equal to', value: 'not equal to'}
		      ]
			});

		$(".filtersContainer").slideDown();
		$(".filterCount").val(filterCount);
		self.parent().addClass("hide");
		$(".downloadCSVLink").attr("href", $(".downloadCSVLink").data("filter-download"));
	});



	$(".rebuildFiltersThroughJs div").each(function() {
		filterCount++;
		var self  = $(this),
				first = self.data("filter-"+filterCount+"-first"),
				second = self.data("filter-"+filterCount+"-second"),
				third  = self.data("filter-"+filterCount+"-third"),
				display = self.data("filter-"+filterCount+"-first-display");
		
		var input = $("<input class='filterInput"+filterCount+"' type='text' >");
		$(".appendFilters").append(input.wrap("<div class='control-group'>"));
		var magicSugggest = $(".filterInput"+filterCount).magicSuggest({
				width: 495,
				name: 'filter_'+filterCount,
				maxSelectioninteger: 3,
				emptyTextstring: "Type here",
				displayField: 'value',
		    value: [first, second, third],
		    data: [
		    		{ id: first, value: display}, 
		    		{ id: 'equals', value: 'equals' }, 
		    		{ id: 'less than', value: 'less than'}, 
		    		{ id: 'greater than', value: 'greater than'}, 
		    		{ id: 'not equal to', value: 'not equal to'},
		    		{ id: third, value: third }
		      ]
			});

		$(".filtersContainer").slideDown();
		$(".filterCount").val(filterCount);
		$(".filter"+first).parent().addClass("hide");
		$(".downloadCSVLink").attr("href", $(".downloadCSVLink").data("filter-download"));
	});

	$(".clearFilters").click(function(event) {
		$(".appendFilters").empty(); 
		$(".filtersForm").submit();
	});



	function setInfo(img, selection) {
		var theImage 		 = new Image();
		theImage.src 	   = $(img).attr("src");
		

		var	originalImgWidth = theImage.width,
				scaledImgWidth   = img.width,
				scale  					 = (originalImgWidth >= scaledImgWidth)? (originalImgWidth/scaledImgWidth) : 1,
				orignalUploadId  = $(img).data("id");

		$('#'+orignalUploadId+'x').val(selection.x1*scale);
		$('#'+orignalUploadId+'y').val(selection.y1*scale);
		$('#'+orignalUploadId+'w').val(selection.width*scale);
		$('#'+orignalUploadId+'h').val(selection.height*scale);
	}

	// prepare instant preview
	$(".livePreviewCrop").change(function(){
		var id = $(this).attr("id"),
				p  = $("#"+id+"Preview").parent();

		// fadeOut or hide preview
		p.fadeOut();

		// prepare HTML5 FileReader
		var oFReader = new FileReader();
		oFReader.readAsDataURL(document.getElementById(id).files[0]);

		oFReader.onload = function (oFREvent) {
	   		p.attr('src', oFREvent.target.result).fadeIn();
		};
	});

	// implement imgAreaSelect plug in (http://odyniec.net/projects/imgareaselect/)
	$('img.imgAreaSelect').imgAreaSelect({
		// set crop ratio (optional)
		aspectRatio: '632:360',
		onSelectEnd: setInfo,
		handles: true
	});

	$('.childOrderColumn').on('click', ".childUpOrder", function(event){
		event.preventDefault();
		var self   = $(this),
				prevId = self.data("previous-id"),
				id 		 = self.data("id"),
				url 	 = self.data("url"),
				model  = self.data("model"),
				column = self.data("column");

		if (prevId != 0) {

			$.ajax({
				type: 'POST',
				url: url,
				data: {
					action: "swap_order",
					column: column,
					prevId: prevId, 
					id: id, 
					model: model, 
					column: column
				},
				success: function (data) { 
				 
				 if (data.success) {
					 	var current  		 = self.parent().parent(),
							 previous 		 = current.prev(),
							 currentNewId  = previous.find(".childUpOrder").data("previous-id"),
							 previousNewId = current.find(".childUpOrder").data("id");
							 currentNewOrder	= previous.find(".childOrderColumn").find("span").text(),
							 previousNewOrder	= current.find(".childOrderColumn").find("span").text();

						
						current.find(".childUpOrder").data("previous-id", currentNewId);
						previous.find(".childUpOrder").data("previous-id", previousNewId);

				    previous.insertAfter(previous.next());

						current.find("span").text(currentNewOrder);
						previous.find("span").text(previousNewOrder);
				 } else {
				 	alert("Failed to reorder entries.");
				 }
				 
				},
				error: function (jqXHR, textStatus, errorThrown) {
				  //console.log('AJAX call failed: ' + textStatus + ' ' + errorThrown);
				}
			}); // End of Ajax 

		}

		return false;
	});

	$('.childOrderColumn').on('click', ".childDownOrder", function(event){
		event.preventDefault();
		var self = $(this).parent().parent(),
				next = self.next();

		if (next.length > 0) {
			next.find(".childUpOrder").trigger("click");
		}
		return false;
	});


	var multiSelectCount = 0,
			multiSelects		 = [];

	$(".multiSelect").each(function() {
		multiSelectCount++;
		var self = $(this),
				data = self.data("data"),
				name = self.data("name"),
				value = String(self.data("value"));

		if(value.indexOf(",") !== -1) {
			var tempArray = new Array();
			$.each(value.split(","), function(index, value) {
				tempArray.push(value);
			});
			value = tempArray;
		} else {
			var tempArray = new Array();
			tempArray[0] = value;
			value = tempArray;
		}
				
		multiSelects[name] = self.magicSuggest({
				width: 495,
				allowFreeEntries: false,
				name: name+multiSelectCount,
				emptyText: "Click arrow to add",
				displayField: 'value',
				value: value,
		    data: data
		});
		
		$(multiSelects[name]).on('selectionchange', function(event, combo, selection){
			$(".multiSelect"+name).val(multiSelects[name].getValue());
		});
	});
	

	
});
})(jQuery);


