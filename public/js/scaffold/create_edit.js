define(['require'], function(require){

    return {

    	init: function () { console.log("edit");

    		$('.selectedDateTime').datetimepicker({ dateFormat: 'yy-mm-dd', timeFormat: 'HH:mm:ss', pickerTimeFormat: 'hh-mm-tt' });
				$('.selectedDate').datepicker({ dateFormat: 'yy-mm-dd'});
				
    	} // end if init

    }
});