var layerHovedkartserien = 
new OpenLayers.Layer.XYZ(
    "Sjøkart hovedkartserien 2",
    "http://opencache.statkart.no/gatekeeper/gk/gk.open_gmaps?layers=sjo_hovedkart2&zoom=${z}&x=${x}&y=${y}",
    {isBaseLayer: true,
     attribution: '<a href="http://www.statkart.no">Statens kartverk</a>, <a href="http://www.statkart.no/nor/Land/Fagomrader/Geovekst/">Geovekst</a> og <a href="http://www.statkart.no/?module=Articles;action=Article.publicShow;ID=14194">kommuner</a>'  
    });

%MAPVAR%.addLayer(layerHovedkartserien); 