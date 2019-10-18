// To use Bing Maps on your site, you need to get an API key from
// http://bingmapsportal.com/ . 
// Replace YourAPIKeyHere in the line below with your API key!
var apiKey = "YourAPIKeyHere";

var layerBingRoad = new OpenLayers.Layer.Bing({
                name: "Bing Road",
                key: apiKey,
                type: "Road"
            });

%MAPVAR%.addLayer(layerBingRoad);
