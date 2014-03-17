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
	

	$(".imageCrop").click(function() {
		var self = $(this),
				parent = self.parent();
				id = self.data("id"),
				path = self.data("path"),
				width = self.data("width"),
				height = self.data("height"),
				loaderHtml = '<div class="loader bubblingG"><span id="bubblingG_1">' +
				'</span><span id="bubblingG_2"></span><span id="bubblingG_3"></span></div> ',
				imagePreviewCrop = self.next(".imagePreviewCrop");

		imagePreviewCrop.fadeIn();
		parent.find(".imagePreviewCropOn").prop('disabled', true).hide();
		self.hide();

			

		var croppicContainerModalOptions = {
			uploadUrl: path,
			uploadData: {"action":"upload_temp_image"},
			cropUrl: path,
			cropData: {"action":"crop_image_send_back_url"}, 
			modal: true,
			imgEyecandyOpacity:0.4,
			loaderHtml: loaderHtml,
			width: width, 
			height: height,
			onAfterImgCrop: function() { 
				imagePreviewCrop.height(200);
				$("#"+id+"CroppedImage").val(parent.find("#"+id+" .croppedImg").attr("src"));
			},
			afterCropControlRemoveCroppedImage: function() {
				imagePreviewCrop.height(30);
				imagePreviewCrop.hide();
				self.show();
				parent.find(".imagePreviewCropOn").prop('disabled', false).show();
				$("#"+id+"CroppedImage").val("");
			}
		}
		var cropContainerModal = new Croppic(id, croppicContainerModalOptions);
		
		$("."+id+"_imgUploadForm").find('input[type="file"]').trigger('click');
	});

	

	$('.childOrderColumn').on('click', ".childUpOrder", function(event){
		event.preventDefault();
		var self   = $(this),
				id 		 = self.data("id"),
				url 	 = self.data("url"),
				model  = self.data("model"),
				column = self.data("column"),
				cssClass = self.parent().parent().data("class"),
				row = self.closest('tr'),
				prevId = row.prev().find(".childUpOrder").data("id");
			
		
		if ($("tr."+cssClass).index(self.parent().parent()) != 0) {
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
				 
				 if (data.success ) {
					 row.prev().insertAfter(row);
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
		var self   = $(this),
				id 		 = self.data("id"),
				url 	 = self.data("url"),
				model  = self.data("model"),
				column = self.data("column"),
				row = self.closest('tr'),
				prevId = row.next().find(".childUpOrder").data("id");
				
		if (row.next().length > 0) {
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
				 
				 if (data.success ) {
					 	row.insertAfter(row.next());
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
	
	$(".placeholder").placeholder();

	$(".focusPassword").focus(function() {
  	$(this).attr("type", "password");
  });
  
  $(".placeholder").placeholder();
  $(".infuseLogin form").submit( function() {
    var submitValidation = $(this).validate({errorClass: "errorInput"});
    return submitValidation.bool;
  });
  

  // Infuse check if clean up of temp folder needed
  setTimeout(function() {
  	$.ajax({
				type: 'POST',
				url: window.location.href,
				data: {
					action: "clean_temp_folder",
				},
				success: function (data) { 
				 if (data.status == "success") {
					 	//console.log(data.message);
				 }
				},
				error: function (jqXHR, textStatus, errorThrown) {
				  //console.log('AJAX call failed: ' + textStatus + ' ' + errorThrown);
				}
			}); // End of Ajax
  }, 1000);
	
});
})(jQuery);


