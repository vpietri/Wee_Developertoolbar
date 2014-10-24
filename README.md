Developertoolbar for magento professionals
====================================

## FORK

This toolbar is a fork from the well known [MGT Developer Toolbar](http://www.mgt-commerce.com/magento-developertoolbar.html)

## INSTALLATION

* copy all files to your magento installation
* Clear the cache in Admin > System > Cache Management 

Option
* edit index.php and enable the profiler Varien_Profiler::enable(); to fill profiling tab content
* have fun and give feedback :)

## PLUGIN

You can add you own tab and tab container using config file.

Sample in config.xml of your module:
```xml
<config>
   <default>
   ...
        <developertoolbar>
            <items>
              <tabcode>
                <sort_order>70</sort_order>
                <label>YourCompany_YourModule_Helper_Data::getLabelMyTab</label>
                <icon>images/mrsgto/checkup/tabcode.png</icon>
                <tab_container>
                  <container1>
                    <label>Container 1</label>
                    <class_tab>YourCompany_YourModule_Block_Wee_Tab_Container1</class_tab>
                  </container1>
                  <container2>
                    <label>Container 2</label>
                    <class_tab>YourCompany_YourModule_Block_Wee_Tab_Container2</class_tab>
                  </container2>                  
                </tab_container>                
              </tabcode>
          </items>
      </developertoolbar>
  ...
  </default>
</config>
```
with:

Tag | Value | Comment
--- | ----- | -------
items | | The tabs
 | tabcode | A unique key for your main tab
sort_order | 70 | Sort order for tab
label | YourCompany_YourModule_Helper_Data::getLabelMyTab | Class and method to generate a dynamic label
icon | images/mrsgto/checkup/tabcode.png | Icon for the tab
tab_container| | The sub containers
label | Container 1 | Label for the container
class_tab | YourCompany_YourModule_Block_Wee_Tab_Container1 | Class block which should extends Wee_DeveloperToolbar_Block_Tab


## FEATURES

* Professional toolbar for frontend and backend

* Requests: involved controller classes, modules, actions and request parameters

* General Info: website id, website name, store id, store name, storeview id, storeview code, storeview name and configured caching method

* Handles: Displays all handles

* Events/Observer: Shows all events with it's observers

* Blocks: overview of block nesting

* Config: enable/disable frontend hints, inline translation and cache clearing

* PHP-Info: output of phpinfo()

* Profiling: output of Varien_Profiler with function execution time, function count and memory usage

* Additional Information: version information, page execution time and overall memory usage

* DB-Profiler: Number of executed queries, average query length, queries per second, longest query length, longest query and detailed query listing including simple syntax highlighting of SQL

## CHANGELOG

2.5.0.0

*  add plugin mechanism
*  delete jquery dependency
*  delete unused class
*  delete unused module Wee_Base

2.0.0.0

*  add handles and events/observers in info block
*  developer toolbar now available for backend

1.5.0.0

* fixed bug in profiler sort order
