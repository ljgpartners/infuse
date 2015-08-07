window.InfusePages  = {

		// Page data object from database of current page
		pageData: null,

		init: function() {
			var self = this;

			// create ckeditors first because of speed
			$(".infusePagesCkeditor").each(function() {
				var ckeditor = $(this).ckeditor();
				// control + e capture
				ckeditor.editor.on('contentDom', function(e) {
					ckeditor.editor.document.on('keydown', function (event) {
						if (event.data.$.ctrlKey && event.data.$.which == 69) {
							var infuseCkeditor = $(ckeditor.editor.container.$).parent().find(".infusePagesCkeditor");
							sessionStorage.setItem("infuse_pages_edit_description", infuseCkeditor.val());
							infuseCkeditor.text('');
							infuseCkeditor.attr("placeholder", "enter description and return");
							$(ckeditor.editor.container.$).hide();
							infuseCkeditor.show().css("visibility", "visible");
						}
					});
				});
			});

			if ($("body").hasClass("developer")) {
				self.developerMode();
			}

			// Get json Page data object from database of current page
			self.pageData = $(".pageDataPassToJs").data("page-data");

			// Change out page values
			self.pageData.originalPageValues = self.pageData.pageValues;
			self.pageData.pageValues = [];


			$(".infusePageForm").submit(function(event) {
				var inputs = $(".saveSubmitButton");
				inputs.attr("disabled", true);
				// Grab new values
				self.infusePageSerialize();
				//console.log(self.pageData);
				$("textarea.pageData").val(JSON.stringify(self.pageData));
				return true;
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

			$(".infuseSubPageCreateId").val(chance.hash({length: 15}));

			$('[data-method]').append(function(){
				return "\n" +
				"<form action='" + $(this).attr('href') + "' method='POST' style='display:none'>\n" +
				"   <input type='hidden' name='_method' value='" + $(this).attr('data-method') + "'>\n" +
				"   <input type='hidden' name='_token' value='" + $('meta[name="csrf-token"]').attr('content') + "'>\n" +
				"</form>\n";
			})
			.removeAttr('href')
			.attr('style','cursor:pointer;')
			.attr('onclick','if (confirm(\'Confirm delete? All nested pages will be deleted also.\')) $(this).find("form").submit();');
		},

		developerMode: function() {
			var self = this,
					form = $(".infusePage form"),
					jumbotron = $(".jumbotron");

			if (form.find(".form-group").length == 0) {
				jumbotron.removeClass("hide");
			}

			$(".draggable").draggable({
				helper: 'clone',
				opacity: 0.35,
				zIndex: 200,
				connectToSortable: '.sortable',
				start: function( event, ui ) {}
			});

			var sortableStop = function (event, ui) {
				if (ui.item.is('a.draggable')) {

	      	if (!jumbotron.hasClass("hide")) {
	      		jumbotron.addClass("hide");
	      	}

	      	if (ui.item.hasClass("addText")) {
	      		ui.item.replaceWith(self.createText());
	      	}

	      	if (ui.item.hasClass("addTextBox")) {
	      		ui.item.replaceWith(self.createTextBox());
	      	}

	      	if (ui.item.hasClass("addUpload")) {
	      		ui.item.replaceWith(self.createUpload());
	      	}

	      	if (ui.item.hasClass("addDivider")) {
	      		ui.item.replaceWith(self.createDivider());
	      	}

	      	if (ui.item.hasClass("addGroup")) {
	      		ui.item.remove();
	      		var newGroup = self.createGroup();
	      		newGroup.find(".formBlock.sortable").sortable({
							placeholder: "ui-state-highlight",
							stop: sortableStop,
							items: ".form-group:not(.unsortable)"
						}).droppable({greedy: true});
						$(".form-horizontal").append(newGroup);
	      	}

	      }
			}


			$(".formBlock.sortable").sortable({
				placeholder: "ui-state-highlight",
				stop: sortableStop,
				items: ".form-group:not(.unsortable)"
			}).droppable({greedy: true});



			$("html").droppable({
				drop: function (event, ui) {
		    	ui.draggable.remove();

		    	if (form.find(".form-group").length == 0) {
						jumbotron.removeClass("hide");
					} else {
						jumbotron.addClass("hide");
					}
		    }
			});

			var windowHeight = $(window).height();
			$("body").css("min-height", ($(window).height()-25)+"px");

			$(window).resize(function() {
				var windowHeight = $(this).height();
				$("body").css("min-height", ($(this).height()-25)+"px");
			});

			var client = new ZeroClipboard($(".infusePage .form-group .glyphicon.glyphicon-paperclip"));


			self.inputEditEvents();

		},

		inputEditEvents: function() {

			$(document).on("keypress", ".quickEdit input", function(event) {
					var self = $(this);
					// Return captures
					if (event.which == 13) {
						var span = self.parent().find("span");
						if (span.data("serialize-tag") == "pageTitle") {
							span.text(self.val());
						} else if (span.data("serialize-tag") == "pageDescription") {
							span.text(self.val());
						}
						self.toggle();
						span.toggle();
						event.preventDefault();
					}
			});

			$(document).on("dblclick", ".quickEdit", function(event) {
					var self = $(this);
					self.find("input").toggle();
					self.find("span").toggle();
			});

			$(document).on("keypress", ".inputTextEditEvent", function(event) {
					var self = $(this);

					// Return captures
					if (event.which == 13) {
						if (self.attr("placeholder") == "enter title and return") {
							self.siblings(".input-group-addon").text(self.val());
							self.data("serialize-page-value-name", self.val());
							self.val(null);
							self.attr("placeholder", "enter description and return");
						} else if (self.attr("placeholder") == "enter description and return") {
							self.parent().parent().find(".infuseLabels .label").text(self.val());
							self.data("serialize-page-value-description", self.val());
							var val = (window.Infuse.isBlank(sessionStorage.getItem("infuse_pages_edit_description")))? "" : sessionStorage.getItem("infuse_pages_edit_description");
							sessionStorage.setItem("infuse_pages_edit_description", null);
							self.val(val);
							self.attr("placeholder", "value");
							self.data("serialize-page-value", 1);
						}
						event.preventDefault();
					}

					// control + e capture
					if (event.ctrlKey && event.which == 101) {
						sessionStorage.setItem("infuse_pages_edit_description", self.val());
						self.val(null);
						self.attr("placeholder", "enter description and return");
					}
			});

			$(document).on("keypress", ".groupEditEvent", function(event) {
				var self = $(this);
				// Return captures
				if (event.which == 13) {
					if (self.attr("placeholder") == "enter title and return") {
						self.parent().find("span").text(self.val());
						self.parent().parent().attr("data-serialize-page-value-name", self.val());
						self.val(null);
						self.attr("placeholder", "enter description and return");
					} else if (self.attr("placeholder") == "enter description and return") {
						self.parent().parent().find("p").text(self.val());
						self.parent().parent().attr("data-serialize-page-value-description", self.val());
						self.val(null);
						self.attr("placeholder", "enter title and return");
						self.hide();
						self.parent().parent().data("serialize-page-value", 1);
					}
					event.preventDefault();
				}

				// control + e capture
				if (event.ctrlKey && event.which == 101) {
					sessionStorage.setItem("infuse_pages_edit_description", self.val());
					self.val(null);
					self.attr("placeholder", "enter description and return");
				}
			});

			$(document).on("dblclick", ".groupEditActivateEvent", function(event) {
				var self = $(this);
				self.parent().find("input").toggle();
			});


			$(document).on("keypress", ".textareaEditEvent", function(event) {
				var self = $(this);

				if (event.which == 13) {
					self.parent().parent().find(".infuseLabels .label").text(self.context.value);
					self.data("serialize-page-value-description", self.context.value);
					var val = (window.Infuse.isBlank(sessionStorage.getItem("infuse_pages_edit_description")))? "" : sessionStorage.getItem("infuse_pages_edit_description");
					sessionStorage.setItem("infuse_pages_edit_description", null);
					self.val(val);
					self.attr("placeholder", "value");
					self.hide().css("visibility", "hidden");
					self.parent().find(".cke").show();
					event.preventDefault();
				}

			});




			// Do ckeditor added after load
			$(document).on("keypress", ".initialTextareaEditEvent", function(event) {
				var self = $(this);

				// Return captures
				if (event.which == 13) {
					if (self.attr("placeholder") == "enter title and return") {
						self.siblings(".input-group-addon").text(self.val());
						self.val(null);
						self.attr("placeholder", "enter description and return");
					} else if (self.attr("placeholder") == "enter description and return") {
						var textarea = $("<textarea>").addClass("infusePagesCkeditor form-control infusePageSerialize textareaEditEvent")
							.attr("data-serialize-page-value-id", chance.hash({length: 15}))
							.attr("data-serialize-page-value-name", self.siblings(".input-group-addon").text())
							.attr("data-serialize-page-value-description", self.val())
							.attr("data-serialize-page-value", 0);

						self.parent().parent().find(".infuseLabels .label").text(self.val());
						var val = (window.Infuse.isBlank(sessionStorage.getItem("infuse_pages_edit_description")))? "" : sessionStorage.getItem("infuse_pages_edit_description");
						sessionStorage.setItem("infuse_pages_edit_description", null);
						self.val(val);
						self.attr("placeholder", "value");

						self.replaceWith(textarea);
						var ckeditor = textarea.ckeditor();


						// control + e capture
						ckeditor.editor.on('contentDom', function(e) {
							$(ckeditor.editor.container.$).parent().find(".infusePagesCkeditor").data("serialize-page-value", 1);

							ckeditor.editor.document.on('keydown', function (event) {
								if (event.data.$.ctrlKey && event.data.$.which == 69) {
									var infuseCkeditor = $(ckeditor.editor.container.$).parent().find(".infusePagesCkeditor");
									sessionStorage.setItem("infuse_pages_edit_description", infuseCkeditor.val());
									infuseCkeditor.text('');
									infuseCkeditor.attr("placeholder", "enter description and return");
									$(ckeditor.editor.container.$).hide();
									infuseCkeditor.show().css("visibility", "visible");
								}
							});
						});


					}
					event.preventDefault();
				}
			});


			$(document).on("keypress", ".initialUploadInputEvent", function(event) {
				var self = $(this);

				if (event.which == 13) {

					if (self.attr("placeholder") == "enter title and return") {
						self.siblings(".input-group-addon").text(self.val());
						self.val(null);
						self.attr("placeholder", "enter description and return");


					} else if (self.attr("placeholder") == "enter description and return") {
						var description = self.val();
						self.parent().parent().find(".infuseLabels .label").text(description);
						self.siblings(".input-group-btn").find(".infusePageSerialize").data("serialize-page-value-description", description);
						var val = (window.Infuse.isBlank(sessionStorage.getItem("infuse_pages_edit_description")))? "" : sessionStorage.getItem("infuse_pages_edit_description");
						sessionStorage.setItem("infuse_pages_edit_description", null);
						self.val(val);


						self.attr("placeholder", "no file");

						// Only Add browse once
						if (self.siblings(".input-group-btn").length == 0) {

							var inputgroupbtn = $("<div>").addClass("input-group-btn"),
								btnbtndefaultbtnfile = $("<span>").addClass("btn btn-default btn-file").attr("tabindex", "-1").text("Browse…"),
								input = $("<input>").attr("type","file")
									.addClass("infusePageSerialize")
									.attr("data-serialize-page-value-id", chance.hash({length: 15}))
									.attr("data-serialize-page-value-name", self.siblings(".input-group-addon").text())
									.attr("data-serialize-page-value-description", description)
									.attr("data-serialize-page-value", 1)
									.on('fileselect', function(event, numFiles, label) {
										var self = $(this)
												textInput = self.parent().parent().siblings("input.form-control");
										textInput.val(label);
									});

							self.val("");

							btnbtndefaultbtnfile.append(input);
							inputgroupbtn.append(btnbtndefaultbtnfile);
							self.after(inputgroupbtn);
						}

						self.attr("readonly", "readonly");
						self.blur();
					}
					event.preventDefault();
				}


				// control + e capture
				if (event.ctrlKey && event.which == 101) {
					sessionStorage.setItem("infuse_pages_edit_description", self.val());
					self.val(null);
					self.removeProp("readonly");
					self.attr("placeholder", "enter description and return");
				}

			});

		},


		createUpload: function() {
			var self = this;
			var frontAddOn = $("<span>").addClass("input-group-addon");
			var input = $("<input>").attr("type", "text")
				.attr("name", "name")
				.addClass("form-control initialUploadInputEvent")
				.attr("placeholder", "enter title and return");
			var inputgroup = $('<div>').addClass("input-group").append(frontAddOn).append(input);
			return self.createTemplate(inputgroup);

		},


		createDivider: function() {
			var infuseDivider = $("<div>").addClass("infuseDivider infusePageSerialize")
				.attr("data-serialize-tag", "divider")
				.attr("data-serialize-page-value-id", chance.hash({length: 15}))
				.attr("data-serialize-page-value-name", "")
				.attr("data-serialize-page-value-description", "")
				.attr("data-serialize-page-value", 1);

			var colsm12colxs12 = $("<div>").addClass("col-sm-12 col-xs-12")
				.append(infuseDivider);
			var formgroup = $('<div>').addClass("form-group")
				.append(colsm12colxs12);
			return formgroup;
		},



		createTemplate: function(inputgroup) {
			var pushpin = $("<span>").addClass("glyphicon glyphicon-paperclip");
			var client = new ZeroClipboard(pushpin);

			var span = $("<span>").addClass("label label-default-bryan");
			var label = $("<div>").addClass("infuseLabels")
				.append(span);

			var colsm12colxs12 = $("<div>").addClass("col-sm-12 col-xs-12")
				.append(inputgroup)
				.append(pushpin)
				.append(label);
			var formgroup = $('<div>').addClass("form-group")
				.append(colsm12colxs12);
			return formgroup;
		},


		createText: function() {
			var self = this;

			var frontAddOn = $("<span>").addClass("input-group-addon");
			var input = $("<input>").attr("type", "text")
				.attr("name", "name")
				.addClass("form-control infusePageSerialize inputTextEditEvent")
				.attr("placeholder", "enter title and return")
				.attr("data-serialize-page-value-id", chance.hash({length: 15}))
				.attr("data-serialize-page-value-name", "")
				.attr("data-serialize-page-value-description", "")
				.attr("data-serialize-page-value", 0);

			var inputgroup = $('<div>').addClass("input-group")
				.append(frontAddOn)
				.append(input);
			return self.createTemplate(inputgroup);
		},



		createGroup: function() { // formBlock sortable"
			var self = this;

			var input = $("<input>").attr("type", "text")
				.attr("name", "name")
				.addClass("form-control groupEditEvent")
				.attr("placeholder", "enter title and return");
			var span = $("<span>").addClass("groupEditActivateEvent");
			var panelHeading = $('<div>').addClass("panel-heading")
				.append(span)
				.append(input);
			var block = $('<div>').addClass("formBlock sortable");
			var p = $("<p>");
			var panelBody = $('<div>').addClass("panel-body")
				.append(p)
				.append(block);
			var panel = $('<div>').addClass("panel panel-default infusePageSerialize")
				.attr("data-serialize-tag", "group")
				.attr("data-serialize-page-value-id", chance.hash({length: 15}))
				.attr("data-serialize-page-value-name", "")
				.attr("data-serialize-page-value-description", "")
				.attr("data-serialize-page-value", 0)
				.append(panelHeading)
				.append(panelBody);

			return panel;
		},



		createTextBox: function() {
			var self = this;
			var frontAddOn = $("<span>").addClass("input-group-addon");
			var input = $("<input>").attr("type", "text")
				.attr("name", "name")
				.addClass("form-control initialTextareaEditEvent")
				.attr("placeholder", "enter title and return");
			var inputgroup = $('<div>').addClass("input-group")
				.append(frontAddOn)
				.append(input);
			return self.createTemplate(inputgroup);

		},




		/*
		pageData:{}

			pageTopLevel: 0,
		pageDepth: 0,




		 */

		infusePageSerialize: function() {
			var self = this,
					pageItems = $(".formBlock:nth-child(1) .infusePageSerialize, .sectionInfo .infusePageSerialize");
					//console.log(pageItems.length);
			$.each(pageItems, function() {
				var pageItem = $(this);
				//console.log(pageItem);
				switch(pageItem.data("serialize-tag")) {
					case "pageTitle":
						self.pageData.pageProperties.pageTitle = pageItem.text();
						break;
					case "pageDescription":
						self.pageData.pageProperties.pageDescription = pageItem.text();
						break;
					default:
						var temp = self.infusePageSerializePrepItem(pageItem);
						if (temp) {
							self.pageData.pageValues.push(temp);
						}
				}
			});

			$(".panel.infusePageSerialize").each(function() {
				var panel = $(this),
						tempItems = [];

				panel.find(".panel-body .infusePageSerialize").each(function() {
					var subPageItem = $(this),
							temp = self.infusePageSerializePrepItem(subPageItem);
					if (temp) {
						tempItems.push(temp);
					}
				});

				if (tempItems.length > 0 && panel.data("serialize-page-value") == 1) {
					var group = {
						"id": panel.data("serialize-page-value-id"),
						"type": "group",
						"name": panel.data("serialize-page-value-name"),
						"value": tempItems,
						"description": panel.data("serialize-page-value-description"),
					};
					self.pageData.pageValues.push(group);
				}
			});

		},

		infusePageSerializePrepItem: function(pageItem) {
			var tempPageObject = {"id": "", "type": "", "name":"", "value": "", "description": ""};

			if (pageItem.data("serialize-page-value") == 1) {
				tempPageObject.id = pageItem.data("serialize-page-value-id");
				tempPageObject.name = pageItem.data("serialize-page-value-name");
				tempPageObject.description = pageItem.data("serialize-page-value-description");
				tempPageObject.value = pageItem.val();

				if (pageItem.is("input")) {
					if (pageItem.attr("type") == "text") {
						tempPageObject.type = "string";
					} else if (pageItem.attr("type") == "file") {
						tempPageObject.type = "upload";
					}
				} else if (pageItem.is("textarea")) {
					tempPageObject.type = "text";
				} else if (pageItem.has("infuseDivider")) {
					tempPageObject.type = "divider";
					tempPageObject.value = "";
				} else {
					tempPageObject = false;
				}
			} else {
				tempPageObject = false;
			}

			//console.log(tempPageObject);
			return tempPageObject;
		}


		 ///chance.hash({length: 15});
};
