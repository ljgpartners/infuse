
(function($){
 
    $.fn.extend({ 



/*************************************
 * Placeholder plugin by Bryan Perez 
 *  Ex: 
 *  
 *  $(".placeholder").placeholder();
 *
 *  <input type="text" name="email" value="Email*" data-reset-name="Email*" data-reset="1" class="placeholder">
 *
 *
 ************************************/
        placeholder: function(options) {
 
 
            //Set the default values, use comma to separate the settings
            var defaults = {
                
            }
                 
            var options =  $.extend(defaults, options);
 
            return this.each(function() {
                var opt  = options,
                    self = $(this);
                
                self.focus(function () {
                  var self = $(this); 
                  
                  if(self.data("reset") ==  1) {
                    self.data("reset", 0);
                    self.val("");
                  }
                });

                self.blur(function() {
                  var self = $(this); 
                  if(self.data("reset") ==  0) {
                    if(self.val() == "") {
                      self.val(self.data("reset-name"));
                      self.data("reset", 1);
                    }
                  }
                });
             
            });
        }, // end of placeholder







/*************************************
 * Validate plugin by Bryan Perez 
 *  Ex: 
 *  
 *  $(".validateForm").validate();
 *
 *  <input type="text" name="email" value="Email*" class="validate" data-reset-name="Email*" data-validate='["presence","otherOption","otherOption"]'>
 *
 * $("form").submit( function() {
 *     var submitValidation = $(this).validate({errorClass: "errorInput"});
 *     return submitValidation.bool;
 *  });
 * 
 *  Current Validation options: presence, email
 *
 ************************************/
        validate: function(options) {
 
 
            //Set the default values, use comma to separate the settings
            var defaults = {
                errorClass: false
            }
                 
            var options =  $.extend(defaults, options);

            this.each(function() {
                var self = $(this)
                    inputs = self.find(".validate"),
                    submit = true,
                    invalidInputs = new Array();

                if (options.errorClass)
                  inputs.removeClass(options.errorClass);

                $.each(inputs, function(index, input) {
                  var input = $(input),
                      rules = input.data("validate");
                  $.each(rules, function(indexRule, rule) {

                    if (rule == "presence") {
                      if (input == "" || input.val() == input.data("reset-name")) {
                        if (options.errorClass)
                          input.addClass(options.errorClass);
                        submit = false;
                      }
                    }

                    if (rule == "email") {
                      var emailRegEx = /^([a-zA-Z0-9])(([a-zA-Z0-9])*([\._-])?([a-zA-Z0-9]))*@(([a-zA-Z0-9\-])+(\.))+([a-zA-Z]{2,4})+$/,
                          email      = input.val();
                      if (email.search(emailRegEx)==-1) {
                        if (options.errorClass)
                          input.addClass(options.errorClass);
                        submit = false;
                      }
                    }

                    // Add more rules here later
                  });

                  if (!submit) {
                    invalidInputs.push(input);
                  }
                });
                
                
             
            });
 
            return {bool: submit, elements: invalidInputs};
        }, // end of validate






/*************************************
 * checkboxCaptcha plugin by Bryan Perez 
 *  Ex: 
 *  
 *  $(".validateForm").checkboxCaptcha("after");
 *
 * options: placement ("append", "prepend", "after", "before" )
 ************************************/

/*
  .checkboxWrapper {  min-height: 20px; padding-left: 20px; display: block; margin-bottom: 5px; font-size: 16px; }
  .checkboxWrapper input[type="checkbox"]{ float: left; margin-left: -20px; }
*/

        checkboxCaptcha: function(options) {
 
 
            //Set the default values, use comma to separate the settings
            var defaults = {
                placement: "append",
                text: "I am not a spambot*"
            }
                 
            var options =  $.extend(defaults, options);

            return this.each(function() {
                var self     = $(this)
                    checkbox = document.createElement("input"),
                    label    = $("<label>").addClass("checkboxWrapper").text(options.text);

                checkbox.type = "checkbox"; 
                checkbox.name = "verify_checkbox"; 
                checkbox.value= "1";

                label.prepend(checkbox);

                if (options.placement == "append") {
                  self.append(label); 
                } else if (options.placement == "prepend") {
                  self.prepend(label); 
                } else if (options.placement == "before") {
                  self.before(label); 
                } else if (options.placement == "after") {
                  self.after(label);
                }
                checkbox.checked = false;
                // Check box classes
             
            });
 
        } // end of validate




    });
     
})(jQuery);