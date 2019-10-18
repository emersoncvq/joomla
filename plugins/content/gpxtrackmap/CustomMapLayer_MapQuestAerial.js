// MapQuest Aerial - free satellite image tiles
// Terms of Use: http://developer.mapquest.com/web/products/open/map#terms

var
 arrayAerial = ["http://otile1.mqcdn.com/tiles/1.0.0/sat/${z}/${x}/${y}.jpg",
                        "http://otile2.mqcdn.com/tiles/1.0.0/sat/${z}/${x}/${y}.jpg",
                        "http://otile3.mqcdn.com/tiles/1.0.0/sat/${z}/${x}/${y}.jpg",
                        "http://otile4.mqcdn.com/tiles/1.0.0/sat/${z}/${x}/${y}.jpg"];
                        
var layerMapQuestAerial =
  new OpenLayers.Layer.OSM("MapQuest Open Aerial Tiles", arrayAerial,
   {isBaseLayer: true,
     attribution: '<p>Tiles Courtesy of <a href="http://www.mapquest.com/" target="_blank">MapQuest</a> <img src="http://developer.mapquest.com/content/osm/mq_logo.png"></p>'  
    });                        
  
%MAPVAR%.addLayer(layerMapQuestAerial);