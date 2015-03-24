var DevToolbar = Class.create();
DevToolbar.prototype = {
    initialize: function() {
        this.weeCookieName = 'weeDeveloperToolbar';
        
        this.selectedTab = '';
        this.selectedTabContainer = '';        
        
        
        $('weeDeveloperToolbarContainer').select('img').first().observe('click', function(){
            $$(".weeDeveloperToolbarDetails").invoke('hide');
            $("weeDeveloperToolbar").toggle();
            if(!$("weeDeveloperToolbar").visible()) {
                Mage.Cookies.set(this.weeCookieName,  "0");
            } else {
                Mage.Cookies.set(this.weeCookieName,  "1");
            }
            //Mage.Cookies.set(this.weeCookieName,  (($("weeDeveloperToolbar").visible()) ? "1" : "0"));
        }.bind(this));
        
        
        $('weeDeveloperToolbar').select('li.content').each( function(element){
            element.observe("click",function(event){
                var id = this.extractId(Event.element(event));
                this.showTabContainer(id);
            }.bind(this)); 
        }.bind(this));
        
        $$('ul.tabContainer li').each( function(element){
            element.observe("click",function(event){
                var id = this.extractId(Event.element(event));
                this.showTab(id);
            }.bind(this)); 
        }.bind(this));     
    
        $('tabContent_blocks').select('a.toggleBlogProperties').each( function(element){
            element.observe("click",function(event){
                Event.element(event).adjacent("ul.blockProperties").first().toggle(); 
            });
        });
        
        /*
        $('tabContent_blocks').select('a.toggleBlogProperties').each( function(element){
            element.observe("click",function(event){
                Event.element(event).adjacent("ul.eventProperties").first().toggle(); 
            });
        });
        */
        
        $('tabContent_events').select('a.toggleBlogProperties').each( function(element){
            element.observe("click",function(event){
                Event.element(event).adjacent("ul.events").first().toggle(); 
            });
        });
        
        this.restoreTab();
    },

    
    showTabContainer: function(id) {
        $$(".weeDeveloperToolbarDetails").each(function(elem) {
            if (elem.readAttribute("id") != "weeDeveloperToolbarDetails_"+id) {
                elem.hide();  
            } else {
                elem.toggle();
                this.selectedTabContainer = id;
                if ($('tabContainer_'+id).down('li.active')) {
                    this.selectedTab = this.extractId($('tabContainer_'+id).down('li.active'));
                } else {
                    this.selectedTab = '';
                }
            }
        }.bind(this));       
        this.memorizeTab();
    },
    
    showTab: function(id) {
        subTabElem = $('tab_'+id);
        if (subTabElem) {
            subTabElem.up('div').select('.tabContent').each(function(elem) {
                if (elem.readAttribute("id") != "tabContent_"+id) {
                    $('tab_'+this.extractId(elem)).removeClassName("active");
                    elem.hide();   
                } else {
                    $('tab_'+this.extractId(elem)).addClassName("active");
                    elem.show();
                    this.selectedTab = id;
                }
            }.bind(this));        
            this.memorizeTab();
        }
        
    },
    
    extractId: function(elem) {
        if(elem.readAttribute("alt")) {
            return elem.readAttribute("alt");
        } else {
            return elem.readAttribute("id").split("_")[1];
        }
    },
    
    memorizeTab: function() {
        var cookieVal = 0; 
        if($("weeDeveloperToolbar").visible()) {
            if(this.selectedTabContainer) {
                cookieVal =  this.selectedTabContainer + ':' + this.selectedTab;
            }
        }
        
        Mage.Cookies.set(this.weeCookieName,  cookieVal);
    },
    
    restoreTab: function() {
        
        if (Mage && Mage.Cookies.get(this.weeCookieName)) {
            var weeCookieValue = Mage.Cookies.get(this.weeCookieName);
            if (weeCookieValue==0) {
                $("weeDeveloperToolbar").hide();  
            } else if (weeCookieValue==1) {
                $("weeDeveloperToolbar").show();  
            } else {
                var toRestore = weeCookieValue.split(":");
                this.showTabContainer(toRestore[0]);
                this.showTab(toRestore[1]);
            }
        }
    }
}

document.observe("dom:loaded", function() {
    
    if($('weeDeveloperToolbarContainer')) {
        var toolbar = new DevToolbar();
    }
});