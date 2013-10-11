// main.js is used only for settings and single-entry-point routing,

 
// Settings 
 
require.config({
  paths: {
    jquery: "jquery/jquery-1.10.2.min",
    crossroads: "crossroads/crossroads.min",
    signals: "crossroads/signals.min"
  }
});
 
 
// Init App
 
require(['jquery', 'require', 'crossroads', 'scaffold/global'], function ($, require, crossroads, global) {

  var route1 = crossroads.addRoute('/admin/resource/{resource}{?query}', loadSection);
  route1.rules = {
        //normalize value to return proper module path (which isn't an URL param)
        normalize_ : function(request, params){ 
            console.log(params);
            if ('query' in params && (params.query.action == "e" || params.query.action == "c")) {
              return [ 'scaffold/create_edit', params.resource ];
            } else if (('query' in params && params.query.action == "l") || !('query' in params)) {
              return [ 'scaffold/list', params.resource ];
            } else {  console.log("here");
              return [ 'scaffold/list', params.resource ];
            }
            
        }
    };




  // Methods
 
  function loadSection(path, rest_params){
    var params = Array.prototype.slice.call(arguments, 1);
    //I'm just assuming all sections modules are stored inside a folder
    //called "sections" and that each section/sub-section have a "main.js"
    //file.
    //It's important to note that r.js won't inline these dependencies
    //automatically since module names are generated dynamically, use the
    //"includes" build setting or optimize each section individually.
    require([path], function(mod){
      mod.init.apply(mod, params);
    });
  }


  // Init 

  global.init();
 
  //parse current URL to decide what to do
  crossroads.parse(document.location.pathname);
 

});