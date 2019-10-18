var layerSeamark = new OpenLayers.Layer.TMS(
  "OpenSeaMarks", 
  "http://tiles.openseamap.org/seamark/", 
  { numZoomLevels: 18, type: 'png', 
    getURL: osm_getTileURL, 
    isBaseLayer: false, 
	displayOutsideMaxExtent: true});
				
 %MAPVAR%.addLayer(layerSeamark);

