window.InfusePages  = {

		init: function() {
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

			$(".sortable").sortable({
				placeholder: "ui-state-highlight",
				stop: function(event, ui)	{
		      if (ui.item.is('a.draggable')) {

		      	if (!jumbotron.hasClass("hide")) {
		      		jumbotron.addClass("hide");
		      	}

		      	if (ui.item.hasClass("addText")) {
		      		ui.item.replaceWith(self.createText());
		      	}

		      	if (ui.item.hasClass("addTextBox")) {
		      		ui.item.replaceWith(self.createTextBox());
		      		$("textarea").ckeditor();
		      	}
		      }
		         
		   }
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

		},

		createTemplate: function(inputgroup) {
			var pushpin = $("<span>").addClass("glyphicon glyphicon-pushpin");
			var colsm12colxs12 = $("<div>").addClass("col-sm-12 col-xs-12").append(inputgroup).append(pushpin);
			var formgroup = $('<div>').addClass("form-group").append(colsm12colxs12);
			return formgroup;
		},

		createText: function() {
			var self = this; 

			var span = $("<span>").addClass("caret");
			var button = $('<button>').attr("type", "button").addClass("btn btn-default dropdown-toggle").attr("data-toggle", "dropdown").text("Title").append(span);
			var ul = $('<ul>').addClass("dropdown-menu").attr("role", "menu");
			var a = $("<a>").text("Delete Row");
			var li = $("<li>").append(a);
			ul.append(li)
			
			var inputgroupbtn = $('<div>').addClass("input-group-btn");
			inputgroupbtn.append(button).append(li);

			var inputgroup = $('<div>').addClass("input-group").append(inputgroupbtn).append('<input type="text" name="name" class="form-control">');

			return self.createTemplate(inputgroup);
		},

		createTextBox: function() {
			var self = this; 
			var frontAddOn = $("<span>").addClass("input-group-addon").text("test"); 
			var inputgroup = $('<div>').addClass("input-group").append(frontAddOn).append('<textarea class="infuseCkeditor" ></textarea>');
			return self.createTemplate(inputgroup);
			
		}

		
		
};


/*

<div class="form-group">
		<div class="col-sm-12 col-xs-12">
			<div class="input-group">
				
				<span class="input-group-addon">Description</span>
				<textarea class="infuseCkeditor" ></textarea>
									
			</div> <!-- end of input-group --> 
	</div> <!-- end of col-sm-12 col-xs-12 --> 
</div>

<div class="form-group">
 	<div class="col-sm-12 col-xs-12">
 		<div class="input-group">

      <div class="input-group-btn">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Title <span class="caret"></span></button>
        <ul class="dropdown-menu" role="menu">
          <li><a href="#">Delete Row</a></li>
        </ul>
      </div><!-- /btn-group -->


      <input type="text" name="name" class="form-control">
    </div><!-- /input-group -->
 	</div>
</div>


 */