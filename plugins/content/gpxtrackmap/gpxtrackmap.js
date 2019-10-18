<script type="text/javascript">

  function osm_getTileURL(bounds) {
    var res = this.map.getResolution();
    var x = Math.round((bounds.left - this.maxExtent.left) / (res * this.tileSize.w));
    var y = Math.round((this.maxExtent.top - bounds.top) / (res * this.tileSize.h));
    var z = this.map.getZoom();
    var limit = Math.pow(2, z);

    if (y < 0 || y >= limit) {
        return OpenLayers.Util.getImagesLocation() + "404.png"; 
    } else {
        x = ((x % limit) + limit) % limit;
        return this.url + z + "/" + x + "/" + y + "." + this.type;
    }
  }
  
  function onWindowResize_%MAPVAR%()
  {
    setTimeout( function() { %MAPVAR%.updateSize();}, 200);
  }
  
  if (window.addEventListener) {    // most non-IE browsers and IE9
     window.addEventListener("resize", onWindowResize_%MAPVAR%, false);
  } else if (window.attachEvent) {  // Internet Explorer 5 or above
     window.attachEvent("onresize", onWindowResize_%MAPVAR%);
  }
  
  var %MAPVAR%; 		
  
  %WINDOWEVENTCODE%
  {  
  window.%MAPVAR% = new OpenLayers.Map ("%MAPVAR%", { 
    controls:[%MAPCONTROLS%
              new OpenLayers.Control.Attribution()],
    maxExtent: new OpenLayers.Bounds(-20037508.34,-20037508.34,20037508.34,20037508.34),
    maxResolution: 156543.0399, numZoomLevels: 19, units: 'm',
    projection: new OpenLayers.Projection("EPSG:900913"),
    displayProjection: new OpenLayers.Projection("EPSG:4326") } );       
  %NOMOUSEWHEELZOOM%
  %MAPLAYERS%     

  var lgpx = new OpenLayers.Layer.Vector("%GPXLAYERNAME%", {
    strategies: [new OpenLayers.Strategy.Fixed()],
    protocol: new OpenLayers.Protocol.HTTP({url: "%GPXPATH%",
      format: new OpenLayers.Format.GPX({%EXTRACTCODE%})
    }),
    style: {strokeColor: "%TRACKCOLOR%", strokeWidth: %TRACKWIDTH%, strokeOpacity: %TRACKOPACITY%, strokeDashstyle: %TRACKDASHSTYLE%, 
            pointRadius:%WPRADIUS%, fillColor: "%WPCOLOR%" },
    projection: new OpenLayers.Projection("EPSG:4326")
  });
  %MAPVAR%.addLayer(lgpx);
  %HILLSHADINGLAYER%  
  %MARKERLAYER%

  lgpx.events.register("loadend", lgpx, function() {    
    %ZOOMCODE%
    %MARKERCODE%        
    %WPTCODE%    
    });   

  OpenLayers.Util.onImageLoadError = function(){
     this.src = "/plugins/content/gpxtrackmap/markers/404.png";
    };

	
  })
  
  function switch_map_fullscreen_%MAPVAR%(onoroff) {

	  var growbtn = document.getElementById('gtm_fullscreen_on_%MAPVAR%');
	  var shrinkbtn = document.getElementById('gtm_fullscreen_off_%MAPVAR%');
	  var mapdiv = document.getElementById('%MAPVAR%');
	  var bgdiv = document.getElementById('gtm_fullscreen_bg_%MAPVAR%');
	  
	  if (bgdiv) {
		if (onoroff=="on") {
		  bgdiv.style.setProperty("display","inline",null);
		} else 	{
		  bgdiv.style.setProperty("display","none",null);
		} 	
      }
	  
	  if (mapdiv) {
		if (onoroff=="on") {
		  mapdiv.style.setProperty("position","fixed",null);
		  mapdiv.style.setProperty("top","0",null);
		  mapdiv.style.setProperty("left","0",null);
		  mapdiv.style.setProperty("width","100%",null);
		  mapdiv.style.setProperty("height","100%",null);
		  mapdiv.style.setProperty("z-index","1100",null);
		} else 	{
		  mapdiv.style.setProperty("position","relative",null);
		  mapdiv.style.setProperty("top","0",null);
		  mapdiv.style.setProperty("left","0",null);
		  mapdiv.style.setProperty("width","%MAPWIDTH%",null);
		  mapdiv.style.setProperty("height","%MAPHEIGHT%",null);
		  mapdiv.style.setProperty("z-index","auto",null);
		}
	  }
	  
	  window.%MAPVAR%.updateSize();
  
	  if (onoroff=="on") {
        window.%MAPVAR%.zoomIn();
	  } else 	{
      	window.%MAPVAR%.zoomOut();
	  }	  
	  
	  if (growbtn) {
		if (onoroff=="on") {
		  growbtn.style.setProperty("display","none",null);
		} else 	{
		  growbtn.style.setProperty("display","inline",null);
		} 	
	  }	
	  if (shrinkbtn) {
		if (onoroff=="on") {
		  shrinkbtn.style.setProperty("display","inline",null);
		} else 	{
		  shrinkbtn.style.setProperty("display","none",null);
		} 	
	  }
	  
  };
</script>
