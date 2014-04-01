define(['require'], function(require){
    
    var filterCount = 0;

    return { console.log("list");

    	init: function () {
    		
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
							emptyTextstring: "Type here",
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

    	} // end of init 

    }
});