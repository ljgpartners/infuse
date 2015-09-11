/*****************************
* Util functions
******************************/

window.Infuse  = {

  capitaliseFirstLetter: function (string) {
    return this.replaceUnderscore(string.charAt(0).toUpperCase() + string.slice(1));
  },

  replaceUnderscore: function (search) {
    return search.replace(/_/g, " ");
  },

  properName: function(string) {
    return this.replaceUnderscore(this.capitaliseFirstLetter(string));
  },

  isFloat: function (mixed_var) {
    //  discuss at: http://phpjs.org/functions/is_float/
    // original by: Paulo Freitas
    // bugfixed by: Brett Zamir (http://brett-zamir.me)
    // improved by: WebDevHobo (http://webdevhobo.blogspot.com/)
    // improved by: Rafał Kukawski (http://blog.kukawski.pl)
    //        note: 1.0 is simplified to 1 before it can be accessed by the function, this makes
    //        note: it different from the PHP implementation. We can't fix this unfortunately.
    //   example 1: is_float(186.31);
    //   returns 1: true

    return +mixed_var === mixed_var && (!isFinite(mixed_var) || !! (mixed_var % 1));
  },

  isInt: function (mixed_var) {
    //   example 1: is_int(23)
    //   returns 1: true
    //   example 2: is_int('23')
    //   returns 2: false
    //   example 3: is_int(23.5)
    //   returns 3: false
    //   example 4: is_int(true)
    //   returns 4: false

    return mixed_var === +mixed_var && isFinite(mixed_var) && !(mixed_var % 1);
  },

  // checking if a string is blank, null or undefined
  isBlank: function (str) {
    return (!str || /^\s*$/.test(str));
  },

  // trims if string is present otherwise return empty string
  trim: function (str) {
    return (this.isBlank(str)) ? "" : str.trim();
  },

  // Confirms action and block UI while page is loading
  confirmAndblockUI: function (displayName, cssClass) {
    if (confirm('Confirm '+displayName+'?')) {
      $.blockUI({ message: $("."+cssClass).html() });
      return true;
    } else {
      return false;
    }
  },

};
