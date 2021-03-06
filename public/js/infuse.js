// @codekit-prepend infuse.util.js
// @codekit-prepend infuse.pages.js

$(document).ready(function() {

	$.ajaxSetup({
	  headers: {
	    'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
	  }
	});

	/*******************************************************
   * Global
   ********************************************************/

	var animating = false;

	$("nav.center").each(function() {
		var activeClass = $(this).data("nav-level-active");
		if (activeClass != "") {
			var bootstrapCollapse = $('li.list-group-item[data-active="'+activeClass+'"]');
			bootstrapCollapse.addClass("active");
			bootstrapCollapse.parent().parent().parent().addClass("in");
			$(".sideNavSlideOut").toggle();
		}
	});

	$(".infuseManage").bind("click", function(event) {
		event.preventDefault();

		$(".sideNavSlideOut").toggle();
		/*
		var self = $(this);
		if (self.data("open") == true && !animating) {
			self.removeClass("active");
			animating = true;
			$(".sideNavSlideOut").animate({left:"-=300px"}, 500, function() {
				self.data("open", false)
				animating = false;
			});
		} else if (self.data("open") == false && !animating) {
			self.addClass("active");
			animating = true;
			$(".sideNavSlideOut").animate({left:"+=300px"}, 500, function() {
				self.data("open", true);
				animating = false;
			})
		} */
	});

	/*******************************************************
   * Login Page
   ********************************************************/

	$(".placeholder").placeholder();

	$(".focusPassword").focus(function() {
  	$(this).attr("type", "password");
  });

  $(".placeholder").placeholder();
  $(".infuseLogin form").submit( function() {
    var submitValidation = $(this).validate({errorClass: "errorInput"});
    return submitValidation.bool;
  });


	/*******************************************************
   * Twitter bootstrap - Collapse always one item open
   ********************************************************/
  $('.sideNavSlideOut .panel-heading a').on('click', function(event)
  {
    if($(this).parent().parent().find('.panel-collapse').hasClass('in'))	{
    	event.stopPropagation();
    	event.preventDefault();
    }
	});

	/*******************************************************
   * Twitter bootstrap - Let checkboxes in dropdowns work
   ********************************************************/
	$('.dropdown-menu-form .checkbox').on('click', function(event)
	{
    event.stopPropagation();
	});


	/*******************************************************
	* Twitter bootstrap - upload button functionality
	********************************************************/
	$(document).on('change', '.btn-file :file', function() {
		var input = $(this),
		numFiles = input.get(0).files ? input.get(0).files.length : 1,
		label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
		input.trigger('fileselect', [numFiles, label]);
	});

	$('.btn-file :file').on('fileselect', function(event, numFiles, label)
	{
		var self = $(this)
				textInput = self.parent().parent().siblings("input.form-control");
		textInput.val(label);
	});



/*******************************************************
 * InfuseController only actions
 ********************************************************/
if ($(".InfuseController").length > 0) {

	$(".infoPopOver").popover();


	$('.infuseCkeditor').each(function() {
		var self = $(this)
				config = self.data("config");
		if (typeof window[config] === "undefined") {
			self.ckeditor();
		} else {
			self.ckeditor(window[config]);
		}
	});

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
		    value: [self.data("filter-column"), 'contains'],
		    data: [
		    		{ id: self.data("filter-column"), value: self.text()},
		    		{ id: 'contains', value: 'contains' },
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
		    		{ id: 'contains', value: 'contains' },
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

	$('.orderColumn').on('click', ".upOrder", function(event){
		event.preventDefault();
		var self   = $(this),
				id 		 = self.data("id"),
				url 	 = self.data("url"),
				model  = self.data("model"),
				column = self.data("column"),
				cssClass = self.parent().parent().data("class"),
				row = self.closest('tr'),
				prevId = row.prev().find(".upOrder").data("id");


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
					 window.location.href = url;
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

	$('.orderColumn').on('click', ".downOrder", function(event){
		event.preventDefault();
		var self   = $(this),
				id 		 = self.data("id"),
				url 	 = self.data("url"),
				model  = self.data("model"),
				column = self.data("column"),
				row = self.closest('tr'),
				prevId = row.next().find(".upOrder").data("id");

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
					 	window.location.href = url;
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

		for (var i = 0; i < data.length; i++) {
			data[i].id = parseInt(data[i].id);
		}

		if(value.indexOf(",") !== -1) {
			var tempArray = new Array();
			$.each(value.split(","), function(index, value) {
				tempArray.push(parseInt(value));
			});
			value = tempArray;
		} else {
			var tempArray = new Array();
			tempArray[0] = parseInt(value);
			if (isNaN(tempArray[0])) {
				tempArray = new Array();
			}
			value = tempArray;
		}

		multiSelects[name] = self.magicSuggest({
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


	/*************************************************************************************
	* Below are the functions/events for importing from another table
	**************************************************************************************/

  function resizeImportModal(self)
 	{
 		var newheight = self.height() - (self.find(".modal-header").height() + 18 + self.find(".modal-footer").height() + 30);
  	self.find(".modal-body").height(newheight);
 	}




	var importModal = $(".importModal"),
			activeForm = "form.search";

	importModal.on('show.bs.modal', function () {
		var self = $(this);
		if (parseInt(self.data("first"))) {
			self.data("first", 0);
			getImportData("", 1);
		}
	});

	importModal.on('shown.bs.modal', function () {
		var self = $(this);
		resizeImportModal(self);
		$(window).resize(function() { resizeImportModal(self); });
	});

	importModal.on('hide.bs.modal', function () {
		var self = $(this);
		self.find(".modal-body .importModalAccordion, .modal-body .addedRow").remove();
		self.data("first", 1);
		self.find(" .searchBox .searchInput").val("")
	});


	importModal.find('.loadMoreResultsPrev').on("click", function(event)
	{
		var self = $(this);
		event.preventDefault();
		if (parseInt(self.data("allow")) == 1) {

			if (activeForm == "form.search") {
				var searchInput = importModal.find(" .searchBox form.search  .searchInput").val(); //AIzaSyAf3nSHLhtp3sscPuC27DOYdjcmC39ESvQ
				getImportData(searchInput, parseInt(self.data("active_page")) - 1);

			} else if (activeForm == "form.advancedSearch") {
				var point = {}, temp = null;
				point['search'] = importModal.find(".searchField").val(),
				point['distance'] = parseInt(importModal.find(".distance").val()),
				temp = importModal.find(".searchLatitudeLongitude").val();

				if (!Infuse.isInt(point['distance'])) {
					alert("Distance must be an integer.");
					return false;
				}

				if (temp.indexOf(",") != -1) {
					temp = temp.split(",");
					point['latitude'] = parseFloat(temp[0]);
					point['longitude'] = parseFloat(temp[1]);
					if (!Infuse.isFloat(point['latitude']) || !Infuse.isFloat(point['longitude']) ) {
						alert("Latitude & Longitude must be numbers.");
						return false;
					}
				} else {
					alert("Latitude & Longitude format inccorect don't forget the comma.");
					return false;
				}

				activeForm = "form.advancedSearch";
				getImportData(point, parseInt(self.data("active_page")) - 1);
			}
		}
	});

	importModal.find('.loadMoreResultsNext').on("click", function(event)
	{
		var self = $(this);
		event.preventDefault();
		if (parseInt(self.data("allow")) == 1) {

			if (activeForm == "form.search") {
				var searchInput = importModal.find(" .searchBox form.search  .searchInput").val();
				getImportData(searchInput, parseInt(self.data("active_page")) + 1);

			} else if (activeForm == "form.advancedSearch") {
				var point = {}, temp = null;
				point['search'] = importModal.find(".searchField").val(),
				point['distance'] = parseInt(importModal.find(".distance").val()),
				temp = importModal.find(".searchLatitudeLongitude").val();

				if (!Infuse.isInt(point['distance'])) {
					alert("Distance must be an integer.");
					return false;
				}

				if (temp.indexOf(",") != -1) {
					temp = temp.split(",");
					point['latitude'] = parseFloat(temp[0]);
					point['longitude'] = parseFloat(temp[1]);
					if (!Infuse.isFloat(point['latitude']) || !Infuse.isFloat(point['longitude']) ) {
						alert("Latitude & Longitude must be numbers.");
						return false;
					}
				} else {
					alert("Latitude & Longitude format inccorect don't forget the comma.");
					return false;
				}

				activeForm = "form.advancedSearch";
				getImportData(point, parseInt(self.data("active_page")) + 1);
			}
		}
	});

	// Import submits
	importModal.find(" .searchBox form").submit(function(event)
	{
		event.preventDefault();
		var self = $(this);

		// Regular search columns
		if (self.hasClass("search")) {
			var searchInput = self.find(".searchInput").val();
			activeForm = "form.search";
			getImportData(searchInput, 1);

			// Submit for lat & long distance
		} else if (self.hasClass("advancedSearch")) {
			var point = {}, temp = null;
			point['search'] = self.find(".searchField").val(),
			point['distance'] = parseInt(self.find(".distance").val()),
			temp = self.find(".searchLatitudeLongitude").val();

			if (!Infuse.isInt(point['distance'])) {
				alert("Distance must be an integer.");
				return false;
			}

			if (temp.indexOf(",") != -1) {
				temp = temp.split(",");
				point['latitude'] = parseFloat(temp[0]);
				point['longitude'] = parseFloat(temp[1]);
				if (!Infuse.isFloat(point['latitude']) || !Infuse.isFloat(point['longitude']) ) {
					alert("Latitude & Longitude must be numbers.");
					return false;
				}
			} else {
				alert("Latitude & Longitude format inccorect don't forget the comma.");
				return false;
			}

			activeForm = "form.advancedSearch";
			getImportData(point, 1);
		}

	});






  function getImportData(search, pg)
  {
  	search = typeof search !== 'undefined' ? search : "";
  	pg = typeof pg !== 'undefined' ? pg : 1;
  	var point = {};

  	if (typeof search == "object") {
  		point = search,
  		search = point.search;
  	} else {
  		point.latitude = "";
  		point.longitude = "";
  		point.distance = "";
  	}

  	// data-related-foriegn-key='{{$foriegnKey}}' data-related-parent-id

  	search = (search == "Search...")? "" : search;

  	var modal = $(".importModal"),
  			id = modal.attr('id');
				list = modal.data("list"),
				map = modal.data("map"),
				child = modal.data("child"),
				resource = modal.data("resource"),
				loading = modal.find(".loading"),
				modelImportingTo = modal.data("model-importing-to"),
				modelImportingToId = modal.data("model-importing-to-id"),
				foriegnKey = modal.data("related-foriegn-key"),
				parentId = modal.data("related-parent-id");

  	loading.show();

  	// Clear out old ones
  	modal.find(".modal-body .importModalAccordion, .modal-body .addedRow").remove();

  	// Refresh results
  	$.ajax({
			type: 'POST',
			url: window.location.pathname,
			data: {
				action: "fetch_import_batch",
				child: child,
				resource: resource,
				search: search,
				list: list,
				map: map,
				pg: pg,
				id: id,
				modelImportingToId: modelImportingToId,
				modelImportingTo: modelImportingTo,
				latitude: point.latitude,
				longitude: point.longitude,
				distance: point.distance,
				foriegnKey: foriegnKey,
				parentId: parentId
			},
			success: function (data, textStatus, jqXHR ) {

			 var successModal = $(".importModal"),
			 		 list = successModal.data("list");

			 if (data.status == "success") {
				 successModal.find(".loading").hide();
				 if (data.entries.length > 0) {
				 	successModal.find("table").append(data.entries_html);
				 	displayMoreResultsLink(data.pagination, successModal);
				 } else {
				 	displayNoResultsImportData(successModal, list);
				 }

			 } else if(data.status == "error") {
			 	successModal.find(".loading").hide();
			 	alert(data.flash.message)
			 }

			 // Free up memory
			 jqXHR = null, textStatus = null;

			},
			error: function (jqXHR, textStatus, errorThrown) {
			  //console.log('AJAX call failed: ' + textStatus + ' ' + errorThrown);
			}
		}); // End of Ajax

		// Free up memory
		id = null, list = null, map = null, child = null, resource = null, search = null,
		loading = null, modal = null;

  }

  function displayMoreResultsLink(pagination, modal)
  {
  	var times = Math.ceil(parseInt(pagination.count)/parseInt(pagination.limit));
  	modal.find(".loadMoreResultsPrev, .loadMoreResultsNext").data("active_page", pagination.active_page);

  	if (parseInt(pagination.active_page) != 1)
  		modal.find(".loadMoreResultsPrev").data("allow", 1).parent().removeClass("disabled");
  	else
  		modal.find(".loadMoreResultsPrev").data("allow", 0).parent().addClass("disabled");

  	if (pagination.active_page != times)
  		modal.find(".loadMoreResultsNext").data("allow", 1).parent().removeClass("disabled");
  	else
  		modal.find(".loadMoreResultsNext").data("allow", 0).parent().addClass("disabled");

  	times = null, loadMoreResults = null, pagination = null, modal = null;
  }

  function displayNoResultsImportData(modal, list)
  {
  	modal.find(".modal-body .importModalAccordion, .modal-body .addedRow").remove();
  	var tr = $("<tr>").addClass('addedRow'),
  			td = $("<td>").attr("colspan", list.length + 1).text("No Results.");
		tr.append(td);
		modal.find("table").append(tr);
		tr = null, td = null, modal = null, pagination = null;
  }


  $(document).on("click", ".addedRow", function()
  {
  	var self = $(this);
  	$(".iconImportAccordion").removeClass("glyphicon-chevron-down").addClass("glyphicon-chevron-right")
  	self.find(".iconImportAccordion").addClass("glyphicon-chevron-down")
  	$(".importModalAccordion").hide();
  	self.next(".importModalAccordion").slideDown();
  });

  $(document).on("click", ".importEntry", function(event)
  {
  	event.preventDefault();
  	var self = $(this),
  			modalId = self.data("modal-id"),
  			checkboxes = self.closest(".importModalAccordion").find("input:checked"),
  			originalEntryForm = $(".create-edit-form"),
  			combine = new Array();


  	$.each(checkboxes, function() {
  		var self = $(this),
  				value = self.data("value"),
  				overiteColumn = self.data("overite-column"),
  				attachment = self.data("attachment");
  		if (attachment == "image") {
  			var tempColumn = overiteColumn.split("@");
  					overiteColumn = tempColumn[0];
  			originalEntryForm.find(".importReplace"+overiteColumn).attr("type", "text").val(value);
  		} else if (String(attachment).indexOf("combine_") > -1) {
  			var tempColumn = overiteColumn.split("@");
  					overiteColumn = tempColumn[0],
  					combineColumn = tempColumn[1].split("_"),
  					combineColumn = combineColumn[1];

  			if (combineColumn in combine) {
  				combine[combineColumn].push(overiteColumn+"#"+value);
  			} else {
  				combine[combineColumn] = new Array(overiteColumn+"#"+value);
  			}


  		} else if (attachment == "convert_text") {
  			var tempColumn = overiteColumn.split("@");
  					overiteColumn = tempColumn[0],
  					newInput = null,
  					oldInput = originalEntryForm.find(".importReplace"+overiteColumn);



  			// Transform from one input to text input
  			$(".importRemove"+overiteColumn).remove();

  			if (overiteColumn in combine) {
  				value = overiteColumn+"#"+value;
  				for (var x in combine[overiteColumn]) {
  					value = value+"|"+combine[overiteColumn][x];
  				}
  			}

  			if (oldInput.is("input")) {
  				originalEntryForm.find(".importReplace"+overiteColumn).attr("type", "text").val(value);
  			} else if (oldInput.is("select")) {
  				originalEntryForm.find(".importReplace"+overiteColumn).find('option').remove().end().append('<option value="'+value+'">'+value+'</option>').val(value);
  			}

  		} else {
  			originalEntryForm.find(".importReplace"+overiteColumn).val(value);
  		}

  	});

  	$("#"+modalId).modal('hide');

		var div = $("<div>").addClass("alert alert-success"),
				buton = $("<button>").addClass("close").attr("type","button").data("dismiss", 'alert').html("&times;").on("click", function() {
					$(this).parent().remove();
				}),
				h4 = $("<h4>").text("Successfully imported fields.");
		div.append(buton).append(h4);
  	$(".page-header").append(div);
  });


	$(".oneRolePerUser").change(function(){
		var self = $(this);
	  if(this.checked){
	      //unchecked to checked
	      $(".oneRolePerUser").not(self).prop('checked', false);
	  }	else	{
	  	//checked to unchecked
	  }
  });


  $('.create-edit-form').submit(function(event){
		var inputs = $(".saveSubmitButton");
		inputs.attr("disabled", true);
	});

	$(".saveSubmitButton").click(function() {
		var typeSubmit = $(this).data('type-submit');
		$("input[data-type-submit="+typeSubmit+"]").val("SAVING..");
		//document.getElementById("typeSubmit").value = typeSubmit;
		var typeSubmitElement = $("#typeSubmit");
		if (typeSubmitElement.length > 0) {
			typeSubmitElement.val(typeSubmit);
		}
	});

  $(document).on("click", ".importCheckAll", function(event)
  {
  	event.preventDefault();
		var self = $(this),
				on = (self.data("all-on") == 1)? true : false,
				inputs = self.parent().parent().parent().find(".checkAll");

		if (on) {
			inputs.prop('checked', false);
			self.data("all-on", 0);
			self.text("Check All");
		} else {
			inputs.prop('checked', true);
			self.data("all-on", 1);
			self.text("Uncheck All");
		}
	});


}
/*******************************************************
 * END OF Infuse resource action
 ********************************************************/

});
