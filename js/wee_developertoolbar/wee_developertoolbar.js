document.observe("dom:loaded", function() {
    
    var weeCookieName = 'weeDeveloperToolbar';
    
    if (Mage && Mage.Cookies.get(weeCookieName) && Mage.Cookies.get(weeCookieName)==0) {
        $("weeDeveloperToolbar").hide();  
    }
    
    $('weeDeveloperToolbarContainer').select('img').first().observe('click', function(){
        $$(".weeDeveloperToolbarDetails").invoke('hide');
        $("weeDeveloperToolbar").toggle();
        Mage.Cookies.set(weeCookieName,  (($("weeDeveloperToolbar").visible()) ? "1" : "0"));
    });
    
    
    $('weeDeveloperToolbar').select('li.content').each( function(element){
        element.observe("click",function(event){
            var id = extractId(Event.element(event));
            $$(".weeDeveloperToolbarDetails").each(function(elem) {
                if (elem.readAttribute("id") != "weeDeveloperToolbarDetails_"+id) {
                    elem.hide();     
                } else {
                    elem.toggle();
                }
              });
        }); 
    });
    
    $$('ul.tabContainer li').each( function(element){
        element.observe("click",function(event){
            var subTabElem = Event.element(event)
            var id = extractId(subTabElem);
            subTabElem.up('div').select('.tabContent').each(function(elem) {
                if (elem.readAttribute("id") != "tabContent_"+id) {
                    $('tab_'+extractId(elem)).removeClassName("active");
                    elem.hide();     
                } else {
                    $('tab_'+extractId(elem)).addClassName("active");
                    elem.show();
                }
              });
        });
    });
    
    
    $('tabContent_blocks').select('a.toggleBlogProperties').each( function(element){
        element.observe("click",function(event){
            Event.element(event).adjacent("ul.blockProperties").first().toggle(); 
        });
    });
    
    $('tabContent_blocks').select('a.toggleBlogProperties').each( function(element){
        element.observe("click",function(event){
            Event.element(event).adjacent("ul.eventProperties").first().toggle(); 
        });
    });
    
    $('tabContent_events').select('a.toggleBlogProperties').each( function(element){
        element.observe("click",function(event){
            Event.element(event).adjacent("ul.events").first().toggle(); 
        });
    });    
    
    
    extractId = function(elem) {
        if(elem.readAttribute("alt")) {
            return elem.readAttribute("alt");
        } else {
            return elem.readAttribute("id").split("_")[1];
        }
    }
    
});

/*

jQuery(document).ready(function(){

  if (Cookie.read("wee_developertoolbar") == 0)    {
      jQuery("#weeDeveloperToolbar").hide();  
      jQuery("#weeDeveloperToolbarPoweredBy").hide();  
  }

  jQuery("#weeDeveloperToolbarContainer img:first").click(function() {
    jQuery(".weeDeveloperToolbarDetails").hide();
    jQuery("#weeDeveloperToolbar").toggle();
    jQuery("#weeDeveloperToolbarPoweredBy").toggle();
    var display = jQuery("#weeDeveloperToolbar").attr("style");
    var toolbarHiddenExpression = /(none)/;
    if (toolbarHiddenExpression.exec(display)) {
      Cookie.write("wee_developertoolbar", 0);
    } else {
      Cookie.write("wee_developertoolbar", 1);    
    }
  });    
  
  jQuery("ul.tabContainer li").click(function() {
    var id = jQuery(this).attr("id").split("_");
    id = id[1];
    var parent = jQuery(this).parent().parent();
    parentContainerId = jQuery(parent).attr("id");
    jQuery("#"+parentContainerId+ " ul.tabContainer li").removeClass("active");
    jQuery(this).addClass("active"); 
    var index = jQuery("#"+parentContainerId+ " ul.tabContainer li").index(this);
    jQuery("#"+parentContainerId+ " .tabContent").hide();
    jQuery("#tabContent_"+id).show();
  });
    
  jQuery("#weeDeveloperToolbar li.content").click(function() {
    var id = jQuery(this).attr("id").split("_");
    id = id[1];
    jQuery(".weeDeveloperToolbarDetails").each(function(e) {
      var toolbarDetailContainer = jQuery(".weeDeveloperToolbarDetails").get(e);
      if (jQuery(toolbarDetailContainer).attr("id") != "weeDeveloperToolbarDetails_"+id) {
        jQuery(toolbarDetailContainer).hide();     
      }
    });
    if (jQuery("#weeDeveloperToolbarDetails_"+id)) {
      jQuery("#weeDeveloperToolbarDetails_"+id).toggle();    
    }
  });
  
  jQuery("#tabContent_blocks a.toggleBlogProperties").click(function() {
    jQuery(this).next("ul.blockProperties").toggle(); 
  });
  
  jQuery("#tabContent_blocks a.toggleBlogProperties").click(function() {
	    jQuery(this).next("ul.eventProperties").toggle(); 
  });
  
  jQuery("#tabContent_events a.toggleBlogProperties").click(function() {
	    jQuery(this).next("ul.events").toggle(); 
});
  
});
*/
