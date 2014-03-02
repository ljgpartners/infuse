define(['require'], function(require){

    var animating   = false;
    		


    return {

    	init: function () { console.log("global");

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

    	} // end if init

    }
});