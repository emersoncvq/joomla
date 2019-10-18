<?php
/**
 * @author
 *  @Copyright
 *  @package     GPXTrackMap - GPX track display on maps using the OpenLayers API - Content Plugin for Joomla 2.5 and 3.x
 *  @author      Frank Ingermann - info@frankingermann.de
 *  @version     1.4.1 - 06-Sept-2017
 *  @link        http://software.frankingermann.de/gpxtrackmap
 *
 *  @license GNU/GPL
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *
 *  For the GNU General Public License, see <http://www.gnu.org/licenses/>.
 *
 *  Code and idea are based on the great "Simple Image Gallery Extended" (SIGE) plugin
 *  by Viktor Vogel: http://joomla-extensions.kubik-rubik.de/sige-simple-image-gallery-extended
 *  Thanks, Viktor!
 */

defined('_JEXEC') or die;

// V1.2.2 fix for J 3.1.4: 
jimport('joomla.filesystem.file');

class plgContentGPXTrackMap extends JPlugin {

  protected $_params;
  protected $_absolute_path;
  protected $_rootfolder; // WITH / at end
  protected $_rootpath; // WITHOUT / at end
  protected $_live_site;
  protected $_plugin_dir;
  protected $_markers_dir;
  protected $_warnings;

  function __construct(&$subject, $config)
  {
    $this->_gtmversion = 'V1.4.1'; 
    
    $version = new JVersion();

    // test for Joomla 2.5 version. The plugin may also work on pre-2.5, but that's untested.

    // i'll leave out this check for the time being phase - otherwise nobody could test it under J != 2.5...
    //if($version->PRODUCT == 'Joomla!' AND $version->RELEASE != '2.5')
    //{ JError::raiseWarning(100, JText::_('PLG_CONTENT_GPXTRACKMAP_NEEDJ25'));
    //  return;
    // }

    parent::__construct($subject, $config);

    if (isset($_SESSION["gtmcount"])) { unset($_SESSION["gtmcount"]); }
    if (isset($_SESSION["gtmcountarticles"])) { unset($_SESSION["gtmcountarticles"]); }
  }

  public function onContentPrepare($context, &$article, &$params, $limitstart)
  {
    // check for our trigger token. If it's not on this page: get outta here, don't waste valuable CPU time!
    if (!preg_match("@{gpxtrackmap}(.*){/gpxtrackmap}@Us", $article->text))
    { return true; }

    if (!isset($_SESSION["gtmcountarticles"]))
    { $_SESSION["gtmcountarticles"] = -1; }

    $this->_absolute_path = JPATH_SITE;
    $this->_live_site     = JURI::base();
    $this->_plugin_dir    = $this->_live_site.'plugins/content/gpxtrackmap/';
    $this->_markers_dir   = $this->_plugin_dir.'markers/';

    if (substr($this->_live_site, -1) == '/')
    { $this->_live_site = substr($this->_live_site, 0, -1); }

    // find all plugin calls on the page
    if(preg_match_all("@{gpxtrackmap}(.*){/gpxtrackmap}@Us", $article->text, $matches, PREG_PATTERN_ORDER) > 0)
    {

      // pull in our language file (atm we only need it to localize the map layer names)
      $lang = JFactory::getLanguage();
      $extension = 'plg_content_gpxtrackmap';
      $base_dir = JPATH_ADMINISTRATOR;
      $language_tag = $lang->getTag();
      $reload = true;
      $lang->load($extension, $base_dir, $language_tag, $reload);

      $_SESSION["gtmcountarticles"]++;

      $enablegooglemaps = $this->params->get('enablegooglemaps','0');

      // where are we?
      $plginpath = JPATH_PLUGINS.DIRECTORY_SEPARATOR.'content'.DIRECTORY_SEPARATOR.'gpxtrackmap'.DIRECTORY_SEPARATOR;

      // load script template that will build the OpenLayer script part
      $scriptfn = $plginpath.'gpxtrackmap.js';

      if (!file_exists($scriptfn))
      { echo('<h2 style="color: red;"><em>GPXTrackMap Plugin error: Template script file '.$scriptfn.' not found!</em></h2>');
        return true;
      }

      if (!isset($_SESSION["gtmcount"]))
      { $_SESSION["gtmcount"] = -1; }

      if ($_SESSION["gtmcountarticles"] == 0) // first call on page
      {
        // the call to the openlayers api and the osm layer definitions: will be put in <head>,
        // but only once. it's a BIG chunk of code!

        // V1.1: the script urls are configurable for those that have OpenLayers.js on their server
        $scriptsrc_ol = $this->params->get('scriptsrc-ol');
        $scriptsrc_osm = $this->params->get('scriptsrc-osm');
        $scriptsrc_gm = $this->params->get('scriptsrc-gm');
        //TODO: fix URL on non-local servers!
        // V1.3.2 no longer needed: $scriptsrc_hbl = $this->_plugin_dir."hikebikelayer.js";

        // revert to defaults if blank:
        // V1.4.0 use scripts deployed with the plugin as default
        //OLD: if ($scriptsrc_ol=="")  { $scriptsrc_ol  = "http://www.openlayers.org/api/OpenLayers.js"; }
        if ($scriptsrc_ol=="")  { $scriptsrc_ol  = "/plugins/content/OpenLayers.2.13.2.full.js"; }
        //OLD: if ($scriptsrc_osm=="") { $scriptsrc_osm = "http://www.openstreetmap.org/openlayers/OpenStreetMap.js"; }
        if ($scriptsrc_osm=="") { $scriptsrc_osm = "/plugins/content/OpenStreetMap.js"; }
        if ($scriptsrc_gm=="")  { $scriptsrc_gm  = "http://maps.google.com/maps/api/js?v=3&amp;sensor=false"; }

        $head_js = '<script src="'.$scriptsrc_ol.'"></script>'."\n".
                   '<script src="'.$scriptsrc_osm.'"></script>'."\n";
        // V1.3.2 no longer needed:            '<script src="'.$scriptsrc_hbl.'"></script>'."\n";

        // only if Google Maps are explicitly enabled: link in their API
        // this is also configurable in case the Google guys should change their url...
        if ($enablegooglemaps==1)
        { $head_js .= '<script src="'.$scriptsrc_gm.'"></script>'."\n"; }

        $document = JFactory::getDocument();

        if ($document instanceof JDocumentHTML)
        { $document->addCustomTag($head_js);  // put the calls in the <head> tag of the page

          // CSS fix for Joomla 3 and some J2.5 templates: setting width and height for img tags can distort
          // the map and diagram displays; disable those stylings for the track map:

          $head_css = '<style type="text/css"> div.gpxtrack div.olMap img, div.gpxtrack div.olMap svg {max-width: inherit ! important;} '.
                      'div.gpxtrack img.olTileImage {max-width: 1000px ! important;} '.
                      'div.gpxtrack div.olControlLayerSwitcher label {display: inline; font-size: 11px; font-weight: bold; border-top: 2px; } '.
                      'div.gpxtrack input.olButton {margin-right: 3px; margin-top: 0px;}</style>'."\n";
          $document->addCustomTag($head_css);
        }
      }

      if (!$this->params->get('gpxroot'))
      { $this->_rootfolder = '/images/gpxtracks/'; }
      else
      {  $this->_rootfolder = $this->params->get('gpxroot');
         if (substr($this->_rootfolder, -1) !== '/') {
           $this->_rootfolder = $this->_rootfolder . '/'; }
      }

      $this->_rootpath = substr($this->_rootfolder,0,strlen($this->_rootfolder)-1);

      // now loop through all invocations we found:
      foreach($matches[0] as $match)
      {
        $scripttext = file_get_contents( $scriptfn );

        // increase our session's invocation counter.
        $_SESSION["gtmcount"]++;

        $gtmcode=''; // this will make up all of the code to replace the {gpxtrackmap}...{/gpxtrackmap} 'invocation' code in the end

        $gpx_code  = preg_replace("@{.+?}@", "", $match);
        $gpx_array = explode(",", $gpx_code);  // syntax is filename[,param1=value1[,param2=value2[...]]]

        $gpx_file  = $gpx_array[0]; // first param must be the gpx filename or url
    
        // V1.2.3 gpx files via URL support
        if (substr_compare($gpx_file,'http://',0,7) == 0) {
          $externalgpx = 1; 
          $gpx_path  = $gpx_file;
          
          $gpx_file  = parse_url($gpx_file,PHP_URL_PATH);   
          $localpath = parse_url($this->_live_site,PHP_URL_PATH);
          $gpx_file  = substr($gpx_file,strlen($localpath));
          $gpx_filepath = $this->_absolute_path.$gpx_file;
          $path_parts = pathinfo($gpx_file);
          $gpx_file = $path_parts['basename'];
          $gpx_dir = $this->_absolute_path.DIRECTORY_SEPARATOR.$path_parts['dirname'].'/';          
          $gpx_basepath = $this->_live_site.$path_parts['dirname'].'/';
        } else {
          $externalgpx = 0; 
          $gpx_dir = $this->_absolute_path.DIRECTORY_SEPARATOR.$this->_rootfolder;
          $gpx_basepath = $this->_live_site.$this->_rootfolder;
          $gpx_path  = $gpx_basepath.$gpx_file;
          $gpx_filepath = $this->_absolute_path.$this->_rootfolder.$gpx_file;
        }       
        
        if (! JFile::exists($gpx_filepath))
        {
          $this->_warnings .= '<h2 style="color: red;"><em>GPXTrackMap Plugin error: GPX file "'.$this->_rootfolder.$gpx_file.'" not found!</em></h2>';
        }
        else
        {
          unset($this->_params);    // if this is the 2nd, 3rd... call: clear previous params
          $this->_params = array(); // we want a fresh, empty array of params for this run

          if(count($gpx_array) >= 2) //more params then just the gpx filename passed
          {  for($i = 1; $i < count($gpx_array); $i++) //starting with the first one AFTER the filename...
              {   $parameter_temp = explode("=", $gpx_array[$i]);

              if(count($parameter_temp) >= 2) //the params should have item=value syntax, lowercase their names:
                  {  $this->_params[strtolower(trim($parameter_temp[0]))] = trim($parameter_temp[1]); }
              }
          }

          $this->collectParams(); // see below - gets all params, either from the current invocation or the defaults from the Backend-params

          $tpldir = substr($plginpath,0,-1);

          if (!isset($this->_params['tpl'])) { $this->_params['tpl'] = $this->params->get('tpldefault'); }
          $tpl = $this->_params['tpl'];

          switch ($tpl)
          {
            case (1): { $tplfn = $this->params->get('tpl1'); break; }
            case (2): { $tplfn = $this->params->get('tpl2'); break; }
            case (3): { $tplfn = $this->params->get('tpl3'); break; }
            case (4): { $tplfn = $this->params->get('tpl4'); break; }
            case (5): { $tplfn = $this->params->get('tpl5'); break; }
            case (6): { $tplfn = $this->params->get('tpl6'); break; } // V1.1.4 added
            default:  {
              // V1.2.2: if $tpl is not an integer, assume it's a directly supplied path to the template file:
              if (!ctype_digit($tpl)) {
                $tplfn = $tpl; }
              }
          }

          $templateDir = JPATH_SITE.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR. JFactory::getApplication()->getTemplate();

          // localize template dir -> /plugins/content/gpxtrackmap/de-DE/
          $lngtag = $lang->getTag();

          $tpldir_local = $tpldir.DIRECTORY_SEPARATOR.$lngtag;

          $dirs = array($tpldir_local,JPATH_SITE/*$this->_live_site*/.$this->_rootfolder.$lngtag,$templateDir.DIRECTORY_SEPARATOR.$lngtag);
          $phs = array('%PLUGINDIR%','%GPXDIR%','%TEMPLATEDIR%');

          // replace %TEMPLATES% placeholder in filenames with the plugin dir path
          $tplfn_local = str_replace($phs,$dirs,$tplfn);

          //TODO: add placeholder %GPXDIR% with the /images/gpxtracks directory? could be an easy way for security risks, though...

          // if template file is not found in the localized dir, try the plugin root dir as default
          if (!file_exists($tplfn_local)) {
           $dirs = array($tpldir,JPATH_SITE/*$this->_live_site*/.$this->_rootpath,$templateDir);
           $tplfn = str_replace($phs,$dirs,$tplfn);
          } else {
             $tplfn = $tplfn_local;
          }

          $tplfn = strtr( $tplfn , '\\', '/');

          if (!file_exists($tplfn))
          {
            $this->_warnings .= '<h2 style="color: red;"><em>GPXTrackMap Plugin error: Layout template file "'.$tplfn.'" not found, using default layout.</em></h2>';
            $tpltext =
              '<div class="gpxtrack">'.
              '<div class="gpxtrackinfo"><p>%TRACKPOINTS% trackpoints; distance: %DISTANCE-KM% km; time: %DURATION%</p></div>'.
              '<div class="gpxtrackmap">%TRACKMAP%</div>'.
              '<div class="gpxtrackdiagram">%TRACKDIAGRAM%</div>'.
              '<div class="gpxtracklink">%TRACKLINK%</div>'.
              '</div>';
          } else {
            $tpltext = file_get_contents( $tplfn );
          }

          $mapvar = 'map'.$_SESSION["gtmcount"]; // all maps need a unique var and ID

          // this should translate the "Base layer" caption in the layer switcher, but it just translates the hint... oh well...
          $baselayer = "{'title': ".'"'.JTEXT::_("PLG_CONTENT_GTM_BASELAYER").'"}';

          // Map controls for Zooming/panning/switching layers
          $mapcontrols = '';

          // another option would be 'new OpenLayers.Control.ZoomPanel(),' ...

          if ($this->_params['mapnav']       == 1) { $mapcontrols .= 'new OpenLayers.Control.Navigation({documentDrag: true}),'."\n"; } // documentDrag allows dragging outside map box
          if ($this->_params['mappan']       == 1) { $mapcontrols .= 'new OpenLayers.Control.PanZoomBar(),'."\n"; } // with pan arrows and zoom bar
          if ($this->_params['mapzoombtns']  == 1) { $mapcontrols .= 'new OpenLayers.Control.Zoom(),'."\n"; } // simple + - buttons
          if ($this->_params['mapswitch']    == 1) { $mapcontrols .= 'new OpenLayers.Control.LayerSwitcher('.$baselayer.'),'."\n"; }
          if ($this->_params['mapscale']     == 1) { $mapcontrols .= 'new OpenLayers.Control.ScaleLine({geodesic:true,maxWidth:150}),'."\n"; } // , bottomOutUnits: "", bottomInUnits: "" ill hide lower display in [mi]
          if ($this->_params['mapoverview']  == 1) { $mapcontrols .= 'new OpenLayers.Control.OverviewMap(),'."\n"; }
          if ($this->_params['mapmousepos']  == 1) { $mapcontrols .= 'new OpenLayers.Control.MousePosition(),'."\n"; }
          if ($this->_params['mapgraticule'] == 1) { $mapcontrols .= 'new OpenLayers.Control.Graticule({displayInLayerSwitcher: true, targetSize: 300,'."\n".
            'layerName: "'.JTEXT::_("PLG_CONTENT_GTM_MAPGRATICULE_LABEL").'",'."\n".
            'intervals: [ 45, 30, 20, 10, 5, 2, 1, 0.5, 0.2, 0.1, 0.05, 0.01 ] }),'."\n"; }

          // code to disable zooming with the mouse wheel
          if ($this->_params['mapwheelzoom'] == 1)
          { $nomousewheelzoom = ''; }
          else
          { $nomousewheelzoom = 'controls = '.$mapvar.'.getControlsByClass(\'OpenLayers.Control.Navigation\');'."\n".
                                'for(var i = 0; i < controls.length; ++i) controls[i].disableZoomWheel();'."\n"; }

          // layer definitions:
          // OSM Mapnik (default)
          $ldmapnik =   ' var layerMapnik = new OpenLayers.Layer.OSM.Mapnik("'.
                        JTEXT::_("PLG_CONTENT_GTM_MAPLAYER_OPENSTREETMAP_MAPNIK").
                        '"); '.$mapvar.'.addLayer(layerMapnik); '."\n";

          // OSM Cyclemap

          // v1.4.1 configurable API key for Thunderforest map layers
          $ldTfApiKey = '';
          
          if ($this->_params['tfapikey'] != '') {
            $ldTfApiKey = '?apikey='.$this->_params['tfapikey'];
          }  
                
          
// v1.4.0 removed http from all tile server URLs that allow access via https if available

          $ldcycle  =   "\n".
                        ' var layerCycleMap = new OpenLayers.Layer.OSM("'.
                        JTEXT::_("PLG_CONTENT_GTM_MAPLAYER_OPENSTREETMAP_CYCLEMAP").'", ['."\n".
                        '"//a.tile.thunderforest.com/cycle/${z}/${x}/${y}.png'.$ldTfApiKey.'",'."\n".
                        '"//b.tile.thunderforest.com/cycle/${z}/${x}/${y}.png'.$ldTfApiKey.'",'."\n".
                        '"//c.tile.thunderforest.com/cycle/${z}/${x}/${y}.png'.$ldTfApiKey.'"],'."\n".
                        '{ attribution : \'maps &copy; <a href="http://thunderforest.com/">Thunderforest</a>,'.
                        ' data &copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a> contributors <a href="http://opendatacommons.org/licenses/odbl/">ODbl</a>\' '.
                        '});'."\n".
                        $mapvar.'.addLayer(layerCycleMap); '."\n"."\n";


          // OSM Mapnik DE, with colors that are more common in german map renderings:
          // V1.3.2 added crossOriginKeyword: null due to CORS issues
          $ldmapnikde = ' var layerMapnikDE = new OpenLayers.Layer.OSM("'.
                        JTEXT::_("PLG_CONTENT_GTM_MAPLAYER_OPENSTREETMAP_MAPNIK_DE").'", ['."\n".
                        '"http://a.tile.openstreetmap.de/tiles/osmde/${z}/${x}/${y}.png",'."\n".
                        '"http://b.tile.openstreetmap.de/tiles/osmde/${z}/${x}/${y}.png",'."\n".
                        '"http://c.tile.openstreetmap.de/tiles/osmde/${z}/${x}/${y}.png"],'."\n".
                        '{sphericalMercator: true, tileOptions : {crossOriginKeyword: null}}); '.$mapvar.'.addLayer(layerMapnikDE); '."\n";

          // Hike & Bike map - V1.3.2 hikebike url has changed, old URL: http://toolserver.org/tiles/hikebike/
          $ldhikebike = 'var layerHikeBike = new OpenLayers.Layer.TMS("'.
                        JTEXT::_("PLG_CONTENT_GTM_MAPLAYER_OPENSTREETMAP_HIKEBIKE").'", "http://a.tiles.wmflabs.org/hikebike/",'."\n".
                        '{ type: \'png\', getURL: osm_getTileURL,'."\n".
                        ' displayOutsideMaxExtent: true, isBaseLayer: true, '."\n".
                        ' attribution: \'Map Data &copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors <a href="http://opendatacommons.org/licenses/odbl/">ODbl</a>\','."\n".
                        ' transitionEffect: \'resize\' } ); '.$mapvar.'.addLayer(layerHikeBike); '."\n";
            
            // V1.3.2 added: 
          $ldtransport = ' var layerTransport = new OpenLayers.Layer.OSM("'.
                        JTEXT::_("PLG_CONTENT_GTM_MAPLAYER_THUNDERFOREST_TRANSPORT").'", ['."\n".
                        '"//a.tile.thunderforest.com/transport/${z}/${x}/${y}.png'.$ldTfApiKey.'",'."\n".
                        '"//b.tile.thunderforest.com/transport/${z}/${x}/${y}.png'.$ldTfApiKey.'",'."\n".
                        '"//c.tile.thunderforest.com/transport/${z}/${x}/${y}.png'.$ldTfApiKey.'"],'."\n".
                        '{sphericalMercator: true, numZoomLevels: 19, '.
                        'attribution : \'maps &copy; <a href="http://thunderforest.com/">Thunderforest</a>,'.
                        ' data &copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a> contributors <a href="http://opendatacommons.org/licenses/odbl/">ODbl</a>\' '.
                        '}); '.$mapvar.
                        '.addLayer(layerTransport); '."\n";
                            
            // V1.3.2 added: 
          $ldlandscape = ' var layerLandscape = new OpenLayers.Layer.OSM("'.
                        JTEXT::_("PLG_CONTENT_GTM_MAPLAYER_THUNDERFOREST_LANDSCAPE").'", ['."\n".
                        '"//a.tile.thunderforest.com/landscape/${z}/${x}/${y}.png'.$ldTfApiKey.'",'."\n".
                        '"//b.tile.thunderforest.com/landscape/${z}/${x}/${y}.png'.$ldTfApiKey.'",'."\n".
                        '"//c.tile.thunderforest.com/landscape/${z}/${x}/${y}.png'.$ldTfApiKey.'"],'."\n".
                        '{sphericalMercator: true, numZoomLevels: 19,'.
                        'attribution : \'maps &copy; <a href="http://thunderforest.com/">Thunderforest</a>,'.
                        ' data &copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a> contributors <a href="http://opendatacommons.org/licenses/odbl/">ODbl</a>\' '.
                        '}); '.$mapvar.
                        '.addLayer(layerLandscape); '."\n";
                        
            // V1.3.2 added: 
          $ldoutdoors = ' var layerOutdoors = new OpenLayers.Layer.OSM("'.
                        JTEXT::_("PLG_CONTENT_GTM_MAPLAYER_THUNDERFOREST_OUTDOORS").'", ['."\n".
                        '"//a.tile.thunderforest.com/outdoors/${z}/${x}/${y}.png'.$ldTfApiKey.'",'."\n".
                        '"//b.tile.thunderforest.com/outdoors/${z}/${x}/${y}.png'.$ldTfApiKey.'",'."\n".
                        '"//c.tile.thunderforest.com/outdoors/${z}/${x}/${y}.png'.$ldTfApiKey.'"],'."\n".
                        '{sphericalMercator: true, numZoomLevels: 19,'.
                        'attribution : \'maps &copy; <a href="http://thunderforest.com/">Thunderforest</a>,'.
                        ' data &copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a> contributors <a href="http://opendatacommons.org/licenses/odbl/">ODbl</a>\' '.
                        '}); '.$mapvar.
                        '.addLayer(layerOutdoors); '."\n";

            // V1.3.2 added: 
          $ldmapquest = ' var layerMapQuest = new OpenLayers.Layer.OSM("'.
                        JTEXT::_("PLG_CONTENT_GTM_MAPLAYER_MAPQUEST").'", ['."\n".
                        '"http://otile1.mqcdn.com/tiles/1.0.0/osm/${z}/${x}/${y}.png",'."\n".
                        '"http://otile2.mqcdn.com/tiles/1.0.0/osm/${z}/${x}/${y}.png",'."\n".
                        '"http://otile3.mqcdn.com/tiles/1.0.0/osm/${z}/${x}/${y}.png"],'."\n".
                        '{sphericalMercator: true, numZoomLevels: 19, tileOptions : {crossOriginKeyword: null},'.
                        'attribution : \'Tiles Courtesy of <a href="http://www.mapquest.com/" target="_blank">MapQuest</a> <img src="http://developer.mapquest.com/content/osm/mq_logo.png">,'.
                        ' data &copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a> contributors <a href="http://opendatacommons.org/licenses/odbl/">ODbl</a>\' '.                        
                        '}); '.$mapvar.
                        '.addLayer(layerMapQuest); '."\n";
                        
            // V1.3.2 added: 
          $ldopentopo = ' var layerOpenTopo = new OpenLayers.Layer.OSM("'.
                        JTEXT::_("PLG_CONTENT_GTM_MAPLAYER_OPENTOPOMAP").'", ['."\n".
                        '"//a.tile.opentopomap.org/${z}/${x}/${y}.png",'."\n".
                        '"//b.tile.opentopomap.org/${z}/${x}/${y}.png",'."\n".
                        '"//c.tile.opentopomap.org/${z}/${x}/${y}.png"],'."\n".
                        '{sphericalMercator: true, numZoomLevels: 18, '.
                        'attribution : \'map data &copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a> contributors '.
                        '<a href="http://opendatacommons.org/licenses/odbl/">ODbl</a>, '.
                        '<a href="http://viewfinderpanoramas.org">SRTM</a> | '.
                        'map style: &copy; <a href="https://opentopomap.org">OpenTopoMap</a> '.
                        '<a href="https://creativecommons.org/licenses/by-sa/3.0/">CC-BY-SA</a>\','.
                        'tileOptions : {crossOriginKeyword: null}}); '.$mapvar.
                        '.addLayer(layerOpenTopo); '."\n";
                        
            // V1.3.2 added:                        
          $ldopnv     = ' var layerOPNV = new OpenLayers.Layer.OSM("'.
                        JTEXT::_("PLG_CONTENT_GTM_MAPLAYER_MEMOMAPS_OPNV").'", ['."\n".
                        '"http://a.tile.memomaps.de/tilegen/${z}/${x}/${y}.png",'."\n".
                        '"http://b.tile.memomaps.de/tilegen/${z}/${x}/${y}.png",'."\n".
                        '"http://c.tile.memomaps.de/tilegen/${z}/${x}/${y}.png"],'."\n".
                        '{sphericalMercator: true, numZoomLevels: 19, tileOptions : {crossOriginKeyword: null},'.
                        'attribution : \'tiles &copy; <a href="http://memomaps.de/">MeMoMaps.de</a> <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>,'.
                        ' map data &copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a> contributors <a href="http://opendatacommons.org/licenses/odbl/">ODbl</a>'.
                        '\','.
                        '}); '.$mapvar.
                        '.addLayer(layerOPNV); '."\n";
                        
/* nice, but license status unknown...
          $ldcomic = ' var layerComicSans = new OpenLayers.Layer.OSM("'.
                        JTEXT::_("PLG_CONTENT_GTM_MAPLAYER_OPENSTREETMAP_COMICSANS").'", ['."\n".
                        '"http://a.tiles.mapbox.com/v3/examples.bc17bb2a/${z}/${x}/${y}.png",'."\n".
                        '"http://a.tiles.mapbox.com/v3/examples.bc17bb2a/${z}/${x}/${y}.png",'."\n".
                        '"http://a.tiles.mapbox.com/v3/examples.bc17bb2a/${z}/${x}/${y}.png"],'."\n".
                        '{sphericalMercator: true, numZoomLevels: 19}); '.$mapvar.
                        '.addLayer(layerComicSans); '."\n";
*/          

                        // Google layers
          $ldgooglephy =' var gphy = new OpenLayers.Layer.Google("'.
                        JTEXT::_("PLG_CONTENT_GTM_MAPLAYER_GOOGLE_MAPS_PHYSICAL").
                        '",{type: google.maps.MapTypeId.TERRAIN}); '.$mapvar.'.addLayer(gphy); '."\n";
          $ldgooglestr =' var gmap = new OpenLayers.Layer.Google("'.
                        JTEXT::_("PLG_CONTENT_GTM_MAPLAYER_GOOGLE_MAPS_STREET").
                        '", {numZoomLevels: 20}); '.$mapvar.'.addLayer(gmap); '."\n";
          $ldgooglehyb =' var ghyb = new OpenLayers.Layer.Google("'.
                        JTEXT::_("PLG_CONTENT_GTM_MAPLAYER_GOOGLE_MAPS_HYBRID").
                        '",  {type: google.maps.MapTypeId.HYBRID, numZoomLevels: 20}); '.$mapvar.'.addLayer(ghyb); '."\n";
          $ldgooglesat =' var gsat = new OpenLayers.Layer.Google("'.
                        JTEXT::_("PLG_CONTENT_GTM_MAPLAYER_GOOGLE_MAPS_SATELLITE").
                        '",{type: google.maps.MapTypeId.SATELLITE, numZoomLevels: 22}); '.$mapvar.'.addLayer(gsat); '."\n";
   
          // map metrics and layer
          $maplayer  = $this->_params['maplayer'];
          $maplayers = $this->_params['maplayers'];

          // V1.3.1 support for up to three custom map layers
          $ldcustom1 = '';
          $ldcustom2 = '';
          $ldcustom3 = '';
          $ldcustom1fn = $plginpath.'CustomMapLayer1.js';
          $ldcustom2fn = $plginpath.'CustomMapLayer2.js';
          $ldcustom3fn = $plginpath.'CustomMapLayer3.js';

          if (JFile::exists($ldcustom1fn))
          { $ldcustom1 = file_get_contents($ldcustom1fn); 
            $ldcustom1 = str_replace('%MAPVAR%',$mapvar,$ldcustom1);
          }
          
          if (JFile::exists($ldcustom2fn))
          { $ldcustom2 = file_get_contents($ldcustom2fn); 
            $ldcustom2 = str_replace('%MAPVAR%',$mapvar,$ldcustom2);
          }
          
          if (JFile::exists($ldcustom3fn))
          { $ldcustom3 = file_get_contents($ldcustom3fn); 
            $ldcustom3 = str_replace('%MAPVAR%',$mapvar,$ldcustom3);
          }
          
          // add the map layers, default layer first
          switch ($maplayer)
          { case(1): $maplayerscode = $ldcycle; break;
            case(2): $maplayerscode = $ldmapnikde; break;
            case(3): $maplayerscode = $ldhikebike; break;
            case(4): { if ($enablegooglemaps==1) { $maplayerscode = $ldgooglestr; } break; }
            case(5): { if ($enablegooglemaps==1) { $maplayerscode = $ldgooglephy; } break; }
            case(6): { if ($enablegooglemaps==1) { $maplayerscode = $ldgooglehyb; } break; }
            case(7): { if ($enablegooglemaps==1) { $maplayerscode = $ldgooglesat; } break; }
            case(8): { if ($ldcustom1=='') { $this->_warnings .= '<h2 style="color: red;"><em>'.$ldcustom1fn.' does not exist or is empty.</em></h2>'."\n"; } 
                       else { $maplayerscode = $ldcustom1; } break; }
            case(9): { if ($ldcustom2=='') { $this->_warnings .= '<h2 style="color: red;"><em>'.$ldcustom2fn.' does not exist or is empty.</em></h2>'."\n"; } 
                       else { $maplayerscode = $ldcustom2; } break; }
            case(10): { if ($ldcustom3=='') { $this->_warnings .= '<h2 style="color: red;"><em>'.$ldcustom3fn.' does not exist or is empty.</em></h2>'."\n"; } 
                        else { $maplayerscode = $ldcustom3; } break; }
            case(11): $maplayerscode = $ldtransport; break;
            case(12): $maplayerscode = $ldlandscape; break;
            case(13): $maplayerscode = $ldoutdoors; break;
            case(14): $maplayerscode = $ldmapquest; break;
            case(15): $maplayerscode = $ldopentopo; break;
            case(16): $maplayerscode = $ldopnv; break;
            default: $maplayerscode = $ldmapnik; break;
          }
      
          if (is_array($maplayers))
          { foreach ($maplayers as $ml)
            { switch ($ml)
              { case(0): { if (!($maplayer==0)) { $maplayerscode .= $ldmapnik;  } break; }
                case(1): { if (!($maplayer==1)) { $maplayerscode .= $ldcycle;   } break; }
                case(2): { if (!($maplayer==2)) { $maplayerscode .= $ldmapnikde;} break; }
                case(3): { if (!($maplayer==3)) { $maplayerscode .= $ldhikebike;} break; }
                case(4): { if (!($maplayer==4) and ($enablegooglemaps==1)) { $maplayerscode .= $ldgooglestr; } break; } // 2013-02-16 fix - str & hyb exchanged
                case(5): { if (!($maplayer==5) and ($enablegooglemaps==1)) { $maplayerscode .= $ldgooglephy; } break; }
                case(6): { if (!($maplayer==6) and ($enablegooglemaps==1)) { $maplayerscode .= $ldgooglehyb; } break; }
                case(7): { if (!($maplayer==7) and ($enablegooglemaps==1)) { $maplayerscode .= $ldgooglesat; } break; }
                case(8): { if (!($maplayer==8)) 
                          { if ($ldcustom1=='') { $this->_warnings .= '<h2 style="color: red;"><em>'.$ldcustom1fn.' does not exist or is empty.</em></h2>'."\n"; } 
                            else { $maplayerscode .= $ldcustom1; } } break; 
                         }
                case(9): { if (!($maplayer==9)) 
                          { if ($ldcustom2=='') { $this->_warnings .= '<h2 style="color: red;"><em>'.$ldcustom2fn.' does not exist or is empty.</em></h2>'."\n"; } 
                            else { $maplayerscode .= $ldcustom2; } } break; 
                          }
                case(10): { if (!($maplayer==10)) 
                           { if ($ldcustom3=='') { $this->_warnings .= '<h2 style="color: red;"><em>'.$ldcustom3fn.' does not exist or is empty.</em></h2>'."\n"; } 
                             else { $maplayerscode .= $ldcustom3; } } break; 
                          }
                case(11): { if (!($maplayer==11)) { $maplayerscode .= $ldtransport;} break; }
                case(12): { if (!($maplayer==12)) { $maplayerscode .= $ldlandscape;} break; }
                case(13): { if (!($maplayer==13)) { $maplayerscode .= $ldoutdoors;} break; }
                case(14): { if (!($maplayer==14)) { $maplayerscode .= $ldmapquest;} break; }
                case(15): { if (!($maplayer==15)) { $maplayerscode .= $ldopentopo;} break; }
                case(16): { if (!($maplayer==16)) { $maplayerscode .= $ldopnv;} break; }
              }
            }
          }

          $hillshading = '';

// V1.3.2 2014-12-21: Hillshading / HikeBikeMap on new server, numZoomLevels added
// so hillshading layer will be hidden on large zoom instead of showing pink tiles.
// This does not work in combination with Bing maps - the x/y/z get messed up, no idea why...         
// OLD URL: http://toolserver.org/~cmarqu/hill/       
          if ($this->_params['maphillshading'] >= 1) {
            $hillshading = 'var hillshading = new OpenLayers.Layer.TMS("'.
                           JTEXT::_("PLG_CONTENT_GTM_MAPHILLSHADING_LABEL").'",
                            "http://a.tiles.wmflabs.org/hillshading/",'."\n".
                           '{type: \'png\', getURL: osm_getTileURL, displayOutsideMaxExtent: true, '."\n".
                           'isBaseLayer: false, numZoomLevels: 16, transparent: true, "visibility": true}); '.$mapvar.'.addLayer(hillshading); '."\n";

            // adding a second hillshading layer makes the effect more intense.
            if ($this->_params['maphillshading'] >= 2) {
              $hillshading .= 'var hillshading2 = new OpenLayers.Layer.TMS("'.
                           JTEXT::_("PLG_CONTENT_GTM_MAPHILLSHADING_LABEL").' (2)",
                              "http://a.tiles.wmflabs.org/hillshading/",'."\n".
                              '{type: \'png\', getURL: osm_getTileURL, displayOutsideMaxExtent: true, '."\n".
                              'isBaseLayer: false, numZoomLevels: 16, transparent: true, "visibility": true}); '.$mapvar.'.addLayer(hillshading2); '."\n";
            }
          }
 
          $markerpath = $this->_markers_dir;

          // optional markers at the start and end of the track
          $startmarkerfn = $this->markerFilename($this->_params['startmarker'],$this->_params['markerset']);
          $endmarkerfn   = $this->markerFilename($this->_params['endmarker'  ],$this->_params['markerset']);

          // if we have a marker, add the openlayer code to show it:
          // V1.2.3 fix: ...OR if waypoint marker symbols are enabled, they are on the same layer!
          if (($startmarkerfn=='') and ($endmarkerfn=='') and ($this->_params['wpshow'] == 0) and ($this->_params['wpsymbols'] == 0))
          { $markerlayer = '';
            $markercode  = ''; }
          else
          { $markerlayer = ' layerMarkers'.$_SESSION["gtmcount"].' = new OpenLayers.Layer.Markers("Marker"); '.
                            $mapvar.'.addLayer(layerMarkers'.$_SESSION["gtmcount"].'); ';

            $markercode = '';

            if (!($startmarkerfn==''))
            { $markercode .=
                ' var startpoint = this.features[0].geometry.components[0];'."\n".
                ' var startsize = new OpenLayers.Size(21, 25);'."\n".
                ' var startoffset = new OpenLayers.Pixel(-(startsize.w/2), -startsize.h);'."\n".
                ' var starticon = new OpenLayers.Icon("'.$markerpath.$startmarkerfn.'",startsize,startoffset);'."\n".
                ' layerMarkers'.$_SESSION["gtmcount"].'.addMarker(new OpenLayers.Marker(new OpenLayers.LonLat(startpoint.x, startpoint.y),starticon));'."\n";
            }

            if (!($endmarkerfn==''))
            { $markercode .=
                ' var endpoint = this.features[0].geometry.components[this.features[0].geometry.components.length-1];'."\n".
                ' var endsize = new OpenLayers.Size(21, 25);'."\n".
                ' var endoffset = new OpenLayers.Pixel(-(endsize.w/2), -endsize.h);'."\n".
                ' var endicon = new OpenLayers.Icon("'.$markerpath.$endmarkerfn.'",endsize,endoffset);'."\n".
                ' layerMarkers'.$_SESSION["gtmcount"].'.addMarker(new OpenLayers.Marker(new OpenLayers.LonLat(endpoint.x, endpoint.y),endicon));'."\n";
            }
          }

          switch ($this->_params['trackstyle'])
          { case(1): $trackdashstyle = '"dot"'; break;
            case(2): $trackdashstyle = '"dash"'; break;
            case(3): $trackdashstyle = '"dashdot"'; break;
            case(4): $trackdashstyle = '"longdash"'; break;
            case(5): $trackdashstyle = '"longdashdot"'; break;
            default: $trackdashstyle = '"solid"';
          }

          switch ($this->_params['wpshow'])
          { case(1): $extractcode = 'extractWaypoints: true, extractRoutes: true, extractAttributes: true'; break;
            default: $extractcode = 'extractWaypoints: false, extractRoutes: true, extractAttributes: true';
          }

          // V1.4.0: added option for fixed zoom level. 
          if (($this->_params['zoomlevel'] > 0) and ($this->_params['zoomlevel'])<=20)
          {  
            $zoomcode = ' this.map.zoomToExtent(this.getDataExtent(),true);'; // needed to center the map
            $zoomcode .= ' this.map.zoomTo('.$this->_params['zoomlevel'].');'."\n";
           
          } else {
          
            // old behaviour (zoomlevel=0): This one will always auto-zoom on the track's extent:
            $zoomcode = ' this.map.zoomToExtent(this.getDataExtent(),true);'; // 2nd param= zoom to closest level

            // option to zoom back out one level when track is to "tight" in the boundaries of the auto-zoomed map.
            // zoomout is actually a zoomDELTA param, where zoomout=2 will zoom out twice, while zoomout=-1 will zoom IN once.
            // V1.1.4: fixed zoomout = 0 and 2nd for loop as provided by Amos.
            if (($this->_params['zoomout'] != 0) and (($this->_params['zoomout'])>-15) and ($this->_params['zoomout'])<15) // sanity check
            { $lvl = $this->_params['zoomout'];
              if ($lvl > 0)
              { for ($i=1; $i <= $lvl; $i++)
                { $zoomcode .= ' this.map.zoomOut();'."\n"; } }
              else
              { for ($i=-1; $i >= $lvl; $i--)
                { $zoomcode .= ' this.map.zoomIn();'."\n"; }
              }
            }
          }

          $ticode= '';
          $edurl = '';
          $spdurl = '';

          // init
          $this->_params['haseledata'] = 0;
          $this->_params['hasspddata'] = 0;

          $wptcode = '';

          if (($this->_params['ti'] == 1) or ($this->_params['ed'] == 1) or ($this->_params['spd'] == 1) or ($this->_params['wpshow'] != 0))
          {
            $tivars = $this->getGpxFileInfo($gpx_dir,$gpx_file);

            // V1.2.1 fix: if parsing found no <trk>, clear marker code:
            if ($tivars[2] == 0) {
              $markercode = '';
            }
                    
            if (($this->_params['wpshow'] == 1) and ($tivars['wptcount'] > 0)) { // V1.2.2 fix: waypoints would show even when turned off
              $wptcode = $this->makeWptCode($tivars['wptcount'],$tivars['wpts'],$mapvar,$_SESSION["gtmcount"]);
            }

            $edurl = $this->_live_site.$this->_rootfolder.$tivars[0];
            $spdurl = $this->_live_site.$this->_rootfolder.$tivars[1];
            $edurl = $gpx_basepath.$tivars[0];
            $spdurl = $gpx_basepath.$tivars[1];

          } else {
            $tivars = array();
          }

          // V1.3.0 allow selection of mootools or jQuery way of adding OnLoad events:
          if ($this->params->get('usejquery',0) == 1) {
            $windoweventcode = "jQuery(window).on('load',function()"; // jQuery
          } else {
            $windoweventcode = "window.addEvent('load',function()"; // mootools
          }  

          $mapclass = 'gpxtrackmap'; // CSS class for the DIV tag the map is shown in
          $gpxlayername = 'GPX Track';
          // TODO: make mapclass and gpxlayername configurable

          // V1.2.1 beta 3 fix: replace blanks in gpx path with %20 for openlayers URL to the track
          $gpx_path = str_replace(' ', '%20', $gpx_path);

          // V1.3.3: fullscreen controls    
          $fsctrls = '';    
          $fsctrlsfn = '';
          if ( $this->_params['mapfullscreen'] == 1 ) {
            if ($this->_params['mappan'] == 1) { 
              $fsctrlsfn = $plginpath.'fullscreencontrols_navbar.html';
            } else {
              $fsctrlsfn = $plginpath.'fullscreencontrols_buttons.html';
            }
            if (file_exists($fsctrlsfn))
            {
              $fsctrls = file_get_contents($fsctrlsfn);
              $scripttext = $scripttext."\n".$fsctrls;
            }
          } 

          // placeholders in the script template:
          $srch = array('%MAPVAR%','%GPXPATH%','%NOMOUSEWHEELZOOM%','%MAPLAYERS%','%MARKERLAYER%','%GPXLAYERNAME%',
                        '%MAPCONTROLS%','%ZOOMCODE%',
                        '%TRACKCOLOR%','%TRACKWIDTH%','%TRACKOPACITY%','%TRACKDASHSTYLE%','%MARKERCODE%','%MAPCLASS%',
                        '%WPCOLOR%','%WPRADIUS%','%EXTRACTCODE%','%HILLSHADINGLAYER%',
                        '%WPTCODE%', 
                        '%WINDOWEVENTCODE%',
                        '%MAPWIDTH%','%MAPHEIGHT%','%MAPFULLSCREEN_ENTER%','%MAPFULLSCREEN_EXIT%'); // V1.3.3 fullscreen toggle

          // what they will be replaced with:
          $repl = array($mapvar,$gpx_path,$nomousewheelzoom,$maplayerscode,$markerlayer,$gpxlayername,
                        $mapcontrols,$zoomcode,
                        $this->_params['trackcolor'],$this->_params['trackwidth'],$this->_params['trackopacity'],$trackdashstyle,
                        $markercode,$mapclass,
                        $this->_params['wpcolor'],$this->_params['wpradius'],$extractcode,$hillshading,
                        $wptcode,
                        $windoweventcode,
                        $this->_params['mapwidth'],
                        $this->_params['mapheight'],
                        JTEXT::_("PLG_CONTENT_GTM_MAPFULLSCREEN_ENTER"),
                        JTEXT::_("PLG_CONTENT_GTM_MAPFULLSCREEN_EXIT")); // V1.3.3 fullscreen toggle

          // replace all the placeholders in the openlayer script template with the params:
          $mapcode = str_replace($srch,$repl,$scripttext);

          $mapcode .= '<div class="'.$mapclass.'"';
          if (($this->_params['mapwidth']!='0') OR ($this->_params['mapheight']!='0')) {
            $mapcode .= ' style="';
            if ($this->_params['mapwidth']!='0') {
              $mapcode .= 'width:'.$this->_params['mapwidth'].'; ';
            }
            if ($this->_params['mapheight']!='0') {
              $mapcode .= 'height:'.$this->_params['mapheight'].'; ';
            }
            $mapcode .= '"';
          }
          $mapcode .= ' id="'.$mapvar.'"></div>';

          // optional track infos
          $tiplaceholders = array(// placeholders for the track info
                                '%ELEDIAGURL%','%SPDDIAGURL%','%TRACKPOINTS%','%DISTANCE-KM%','%DISTANCE-MI%','%DISTANCE-NM%',
                                '%ELE-UP-M%','%ELE-DOWN-M%','%ELE-MIN-M%','%ELE-MAX-M%','%ELE-DELTA-M%',
                                '%ELE-UP-FT%','%ELE-DOWN-FT%','%ELE-MIN-FT%','%ELE-MAX-FT%','%ELE-DELTA-FT%',
                                '%STARTTIME%','%ENDTIME%','%DURATION%','%DURATIONMOVING%','%DURATIONPAUSED%',
                                '%AVGSPEED-KMH%','%AVGSPEED-MPH%','%AVGSPEED-KN%',
                                '%AVGSPEEDUP-KMH%','%AVGSPEEDUP-MPH%','%AVGSPEEDUP-KN%',
                                '%AVGSPEEDDOWN-KMH%','%AVGSPEEDDOWN-MPH%','%AVGSPEEDDOWN-KN%',
                                '%AVGSPEEDMOVING-KMH%','%AVGSPEEDMOVING-MPH%','%AVGSPEEDMOVING-KN%',
                                '%MAXSPEED-KMH%','%MAXSPEED-MPH%','%MAXSPEED-KN%',
                                '%MAXSPEEDUP-KMH%','%MAXSPEEDUP-MPH%','%MAXSPEEDUP-KN%',
                                '%MAXSPEEDDOWN-KMH%','%MAXSPEEDDOWN-MPH%','%MAXSPEEDDOWN-KN%',
                                '%TRACKPOINTDISTANCE-M%','%TRACKPOINTDISTANCE-FT%');


          // elevation diagram

          $edcode = '';

          if (($this->_params['ed'] == 1) and ($this->_params['haseledata'] == 1)) {
            $edwidth = $this->_params['edwidth'];
            $edheight = $this->_params['edheight'];

            $edcode = '<img class="gpxtrackdiagram" src="'.$edurl.'"';
            if (($edwidth!='0') OR ($edheight!='0')) {
              $edcode .= ' style="';
              if ($edwidth!='0') {
                $edcode .= 'width:'.$edwidth.' ! important; ';
              }
              if ($edheight!='0') {
                $edcode .= ' height:'.$edheight.' ! important;';
              }
             $edcode .= '"';
            }
            $edcode .=  "/>"."\n";
          }

          // speed diagram

          $spdcode = '';

          if (($this->_params['spd'] == 1) and ($this->_params['hasspddata'] == 1)) {
            $spdwidth = $this->_params['spdwidth'];
            $spdheight = $this->_params['spdheight'];

            $spdcode = '<img class="gpxspeeddiagram" src="'.$spdurl.'"';
            if (($spdwidth!='0') OR ($spdheight!='0')) {
              $spdcode .= ' style="';
              if ($spdwidth!='0') {
                $spdcode .= ' width:'.$spdwidth.' ! important;';
              }
              if ($spdheight!='0') {
                $spdcode .= ' height:'.$spdheight.' ! important;';
              }
              $spdcode .= '"';
            }
            $spdcode .=  "/>"."\n";
          }

          // optional download link for the gpx file:
          $dlcode = '';

          if ($this->_params['dl'] == 1)
          {
            if ($this->_params['dlzip']==1)
            { $zip_fn = $this->ziptrackfile($this->_rootfolder,$gpx_file);

              if ($zip_fn)
              { $gpx_path = $this->_live_site.$zip_fn; } // otherwise (sthg went wrong zipping) leave .gpx file...
            }

            $dltext = str_replace('%s',basename($gpx_path),$this->_params['dltext']);

            $cssstyle = '';

            if (!($this->_params['dlstyle']==''))
            { $cssstyle = ' style="'.$this->_params['dlstyle'].'"'; }

            // problem if you don't use the zip option: click on button will leave the page and show the gpx file contents in the browser.
            // if text link is used, it will at least open it in a new tab, but still display the file contents. hmmm.

            if ($this->_params['dltype']==0) // text link
            { $dlcode = '<div class="'.$this->_params['dlclass'].'"'.$cssstyle.'>'.
                        '<a href="'.$gpx_path.'" type="application/gpx+xml" download="'.$gpx_file.'" target="_blank">'.$dltext.'</a></div>'; 
              //v1.2.3 the MIMEtype and download clause _should_ force browsers to download the gpx file instead of displaying it,
              // but this still doesn't work in all browsers. So it's still best to use the ZIP option...
            } else { 
            $dlcode = '<form class="'.$this->_params['dlclass'].'"'.$cssstyle.'><input type="button" value="'.$dltext.'" '.
                          'onclick="window.location.href='."'".$gpx_path."'".'" /></form>';

            }
          }

          // now place the different parts (map, trackinfos, elevation diagram, download link) into the html template:

          $gtmcode = $tpltext;

          // replace placeholders for the track info elements
          $gtmcode = str_replace($tiplaceholders,$tivars,$gtmcode );

          // replace placeholders for the map, elevation diagram, speed diagram and download link
          $placeholders = array('%TRACKMAP%','%TRACKDIAGRAM%','%SPEEDDIAGRAM%','%TRACKDOWNLOAD%');
          $vars = array($mapcode,$edcode,$spdcode,$dlcode);

          $gtmcode = str_replace($placeholders,$vars,$gtmcode );

          $gtmcode = '<!-- GPXTrackmap '.$this->_gtmversion.' #'.$_SESSION["gtmcount"].' START -->'."\n".$gtmcode."\n".'<!-- GPXTrackmap #'.$_SESSION["gtmcount"].' END -->'."\n";

        } // if !gpxfile exists..else

        // ok - ready for injection:

        if ((!$this->_warnings=='') AND ($this->_params['showwarnings']==1)) // V1.3.1 option to turn off warnings
        { $gtmcode = '<p style="color:yellow; background:red;">'.
                           'GPXTrackMap plugin warnings:<br />'.
                            $this->_warnings.'</p>'.$gtmcode; }

        $regex = "@(<p>)?{gpxtrackmap}".$gpx_code."{/gpxtrackmap}(</p>)?@s";

        // replace the invoking code with the openlayer js code
        // and the DIV for the map in the article text:

        $article->text = preg_replace($regex, $gtmcode, $article->text);

        unset($gpx_array); // for next loop if we have multiple maps on the page

        $this->_warnings = '';
      } // foreach match
    } // if <matches found>
  }

  private function collectParams()
  {
    $names = array(
      'mapwidth','mapheight','maplayer','mapnav','mappan','mapzoombtns','mapfullscreen','mapswitch','maplayers','mapgraticule','maphillshading',
      'mapscale','mapwheelzoom','mapoverview','mapmousepos','zoomout',
      'trackcolor','trackwidth','trackopacity','trackstyle',
      'wpshow','wpradius','wpcolor','wppopups','wppopupwidth','wppopupheight','wppopupele','wppopuptimefmt','wppopupdesc','wppopupdescbb','wppopuplinkfmt','wpsymbols',
      'startmarker','endmarker','markerset',
      'ti','tidecimalsep','tidatefmt','titimefmt','titimeshift','timovespeed',
      'dl','dltext','dltype','dlzip','dlclass','dlstyle',
      'ed','edwidth','edheight','edlinecolor','edlinewidth','edbgcolor','edfilterorder','edfillmode','edupcolor','eddowncolor','edunits',
      'edxgrid','edxgridunits','edxgridlimit','edxgridcolor','edxgridwidth','edygridlines','edygridwidth','edygridcolor',
      'spd','spdwidth','spdheight','spdlinecolor','spdlinewidth','spdbgcolor','spdfilterorder','spdfillmode','spdupcolor','spddowncolor','spdunits',
      'spdxgrid','spdxgridunits','spdxgridlimit','spdxgridcolor','spdxgridwidth','spdygridlines','spdygridwidth','spdygridcolor',
      'cache','tpl','usejquery','showwarnings','zoomlevel','tfapikey');

    // expand any preset in the call to its full set of params
    $this->expandPresets($this->_params,$this->params->get('presets'),$names);

    // add in everything that was not in the invocation OR in any of the templates from the back-end params - that is, apply defaults:
    foreach ($names as $name)
    { if (!array_key_exists($name, $this->_params))
      { $this->_params[$name] = $this->params->get($name); }
    }
  }

  private function expandPresets(&$syntaxparams,$backendpresets,$paramnames)
  {
    // preset expansion: presets are processed one by one, param by param
    // when multiple presets are used, the last found setting will "win" -
    // but individual settings in the call will always win over any preset setting(!)
    $presets = explode("\n",$backendpresets);

    foreach ($syntaxparams as $key => $value)
    {
      if (($key=='preset') or ($key=='ps')) // $value holds a single preset name OR "tname1-tname2-tname3"
      {
        $psetcalls = explode('-',$value); // preset=t1-t2-t3 -> [t1] [t2] [t3]

        foreach ($psetcalls as $psetcall) // apply each preset in the order of appearance
        {
          $psetcall = strtolower(trim($psetcall));

          $tfound = 0;

          foreach ($presets as $presetline) // test each preset call against each preset we have
          {
            if (trim($presetline) !== '') {
              $p = strpos($presetline,':'); // split preset line into preset name and params: where's the ":"??

              if ($p === false) // "presets" lines from the backend must have "<name>:<params>" syntax, or it all won't work!
              { $this->_warnings .= 'Syntax error in preset: '.$presetline.'<br />'."\n"; }
              else
              { $psetname = strtolower(trim(substr($presetline,0,$p))); //TODO: strtolower:OK, but trim+substr? REQUIRED??
                $psetparams = substr($presetline,$p+1,strlen($presetline)); // see above...

                if ($psetname==$psetcall) // gotcha! we found (one line of) our preset, apply its params!
                {
                  $tfound += 1;

                  $tparams_values = explode(',',$psetparams); // preset params into "key=value" strings

                  foreach($tparams_values as $tparam_value)
                  { $key_value = explode('=',$tparam_value); // "key=value" into array[2]
                    $spname = strtolower(trim($key_value[0]));

                    // V1.1.4 fix: individual params should win over preset params, so only use
                    // the preset param if there was no other setting in the invocation:
                    if (!array_key_exists($spname, $syntaxparams)) {
                      $syntaxparams[$spname] = trim($key_value[1]);
                    }
                  }
                }
              }
            }
          } //foreach presetline we have

          if ($tfound==0)
          { $this->_warnings .= 'unknown preset called: "'.$psetcall.'"<br />'."\n"; }

        } // foreach preset call we found
      } else
      { // $key was sthg. else than "preset" or "t":
        // reverse check here to see if there's any misspelled or unknown parameters in the params from the call:
        if (!in_array($key,$paramnames))
        { $this->_warnings .= 'unknown parameter: "'.$key.'"'."\n"; }
      }
    }
  }

  private function getGpxFileInfo($filepath,$filename)
  {
    $lat = array();
    $lon = array();
    $ele = array();
    $dist = array();
    $distdelta = array(); // in km: distance delta between two trackpoints
    $tim = array();
    $speed = array();  // in km/h
    $hr = array();  // heart rate
    $cad = array(); // cadence
    $atemp = array(); // temperature
    $wpts = array(); // waypoints

    $n = 0;

    // get all the waypoint's data: min/max elevation, start and end time, coords

    $found_ele = 0;  // flags if we found any elevation tags
    $found_time = 0; // flags if we found any time tags
    $found_wpts = 0;

    // V1.2.1 fix: properly initialize speeds, the may be left undefined otherwise:
    $avgspeeddown_kmh = '';
    $avgspeeddown_mph = '';
    $avgspeeddown_kn = '';
    $avgspeedup_kmh = '';
    $avgspeedup_mph = '';
    $avgspeedup_kn = '';

/* work in progress: read heart rate, cadence and temperature curves
    $found_hr = 0; // flags if we found any heart rate (hr) tags in TrackPointExtension subtags
    $found_cad = 0; // flags if we found any cadence (cad) tags
    $found_atemp = 0; // flags if we found any cadence (cad) tags
*/
    date_default_timezone_set('UTC');

    $ts = 0;

    if (! JFile::exists($filepath.$filename))
    {
      return array ();
    }

    /* 2016-12-27 added "@" to suppress gpxx Namespace Warnings thrown by simplexml_load_file */
    $gpx = @simplexml_load_file($filepath.$filename);

    $starttime = 0;
    $endtime = 0;

    foreach ($gpx->trk as $trk) {

      //V1.2.5 auto track color from Garmin files
//    <extensions>
//      <gpxx:TrackExtension>
//        <gpxx:DisplayColor>DarkGray</gpxx:DisplayColor>
//      </gpxx:TrackExtension>
//    </extensions>
//
//      if ($this->_params['trackcolor']=='auto') {
//        foreach ($trk->extensions as $trkexts) {
//          foreach ($trkexts->gpxx:TrackExtension as $trkext) {
//echo($trkext);
//          }
//        }
//      }

      foreach ($trk->trkseg as $trkseg) {
        foreach ($trkseg->trkpt as $tpt) {
          $attrs = $tpt->attributes();
          $lat[$n] = (float)$attrs['lat'];
          $lon[$n] = (float)$attrs['lon'];

          if ((float)$tpt->ele != 0) {
            $ele[$n] = (float)$tpt->ele;
            $found_ele = 1;
          } else {
            $ele[$n] = 0;
          }

          $ts = $tpt->time;

          if ((string)$ts != '') {
            $found_time = 1;
            $tim[$n] = $this->getGPXTime($ts); // V1.1.4
          } else {
            $tim[$n] = 0;
          }

          if ($n == 0) {
            $minele_m = $ele[$n];
            $maxele_m = $ele[$n];
            $starttime = $this->getGPXTime($ts);
          } else {
            if ($ele[$n] < $minele_m) { $minele_m = $ele[$n]; }
            if ($ele[$n] > $maxele_m) { $maxele_m = $ele[$n]; }
          }
/*
          // V1.1.4 experimental: read temp, heart rate, cadence from Strava GPX file
          foreach ($tpt->extensions as $tpext) {
            foreach ($tpext->children('gpxtpx',TRUE) as $gpxtpx) { // <gpxtpx:TrackPointExtension>
              foreach ($gpxtpx->children('gpxtpx',TRUE) as $gpxtpxelement) {
                switch ($gpxtpxelement->getName())
                {
                 case ('hr')   : $hr[$n]    = (float)$gpxtpxelement; $found_hr = 1; break; // <gpxtpx:hr>
                 case ('cad')  : $cad[$n]   = (float)$gpxtpxelement; $found_cad = 1; break; // <gpxtpx:cad>
                 case ('atemp'): $atemp[$n] = (float)$gpxtpxelement; $found_atemp = 1; break; // <gpxtpx:atemp>
                }
              }
            }
          }
*/
          $n++;
        }
      }
    }

    if ($n > 0) {
      $endtime = $this->getGPXTime($ts);
    }

    // read Waypoint data if popups are activated

    $wptcount = 0;

    if ($this->_params['wppopups'] !== 0)
    {
      foreach ($gpx->wpt as $wpt) {
        $found_wpts = 1;

        $attrs = $wpt->attributes();
        $wptlat = (float)$attrs['lat'];
        $wptlon = (float)$attrs['lon'];
        if ((string)$wpt->time != '') {
          $wpttime = $this->getGPXTime($wpt->time);
        } else {
          $wpttime = '';
        }
        if ((string)$wpt->ele != '') {
          $wptele = (float)$wpt->ele;
        } else {
          $wptele = '';
        }
        if ((string)$wpt->sym != '') {
          $wptsym = (string)$wpt->sym; // 'Flag, Green' or similar - currently not used
        } else {
          $wptsym = '';
        }
        $wptname = (string)$wpt->name;
        $wptdesc = strtr((string)$wpt->desc,'"','\"');

        if ($wptdesc == '') {
          $wptdesc = strtr((string)$wpt->description,'"','\"');
        }

        //V1.2.3: since Basecamp doesn't allow HTML in waypoint descriptions,
        // BBCode can allow at least some basic formatting:
        
        if ($this->_params['wppopupdescbb'] == 1) {
          $bbcode = array(
            "/\[br\]/is" => "<br />",
            "/\[b\](.*?)\[\/b\]/is" => "<strong>$1</strong>",
            "/\[u\](.*?)\[\/u\]/is" => "<u>$1</u>",
            "/\[i\](.*?)\[\/i\]/is" => "<i>$1</i>",
            "/\[code\](.*?)\[\/code\]/is" => "<pre>$1</pre>",
            "/\[quote\](.*?)\[\/quote\]/is" => "<blockquote>$1</blockquote>",
            "/\[url\=(.*?)\](.*?)\[\/url\]/is" => "<a href='$1' target='_self'>$2</a>",
            "/\[img\](.*?)\[\/img\]/is" => "<img src='$1' alt='' />"
            );
                  
          $wptdesc = preg_replace(array_keys($bbcode), array_values($bbcode), $wptdesc);        
        }

        $wptlinks = '';
        $linkno = 1;
        
        foreach ($wpt->link as $wptlink) {
          $attrs = $wptlink->attributes();
          $wpthref = (string)$attrs['href'];
          $wptlinks .= $wpthref."\n";
          
          if ($this->_params['wppopupdescbb'] == 1) {
            $bbcode = array(
              "/\[link".$linkno."\](.*?)\[\/link".$linkno."\]/is" => "<a href='".$wpthref."' target='_blank'>$1</a>");
            
            $wptdesc = preg_replace(array_keys($bbcode), array_values($bbcode), $wptdesc);          
          }
          
          $linkno += 1;
        }
        
        $wpts[] = array('lat' => $wptlat,
                        'lon' => $wptlon,
                        'ele' => $wptele,
                        'time' => $wpttime,
                        'name' => $wptname,
                        'desc' => $wptdesc,
                        'sym' => $wptsym,
                        'links' => $wptlinks);

        $wptcount += 1;
      }
    }

    // calculate the track length
    // source: http://wiki.openstreetmap.org/wiki/User:Aighes/GPX-Track_auf_Website

    $r0 = 6371.0; // earth radius in [km]
    $distance_km = 0.0;

    $duration_moving = 0;
    $duration_paused = 0;

    if ($n > 0) {
      for ($i = 0; $i < ($n-1); $i++) {
        $a = 0;
        $b = 0;
        $c = 0;
        $distdelta[$i] = 0.0;
        $speed[$i] = 0.0;

        if (($lat[$i] !== $lat[$i+1]) and ($lon[$i] !== $lon[$i+1])) {
          $a = deg2rad(90.0 - $lat[$i]);
          $b = deg2rad(90.0 - $lat[$i+1]);
          $gamma = deg2rad(abs($lon[$i+1] - $lon[$i]));
          $c = $r0 * acos(cos($a)*cos($b) + sin($a)*sin($b)*cos($gamma));
          $distdelta[$i] = $c;
          $distance_km += $c;

          // the speed calculation is problematic when the trackpoint recording frequency is high,
          // since the timestamps only resolve to seconds.
          if ($found_time == 1) {
            if ((abs($tim[$i]-$tim[$i+1])>1) and ($c != 0.0)) {
              $speed[$i] = $c / ($tim[$i+1]-$tim[$i]) * 60 * 60;
            } else {
              // if distance is less or equal 1s, try to use the trackpoint before the current one to have better accuracy.
              // not an optimal solution either, but better than calculating with 1s intervals...
              if (($i>0) and (abs($tim[$i-1]-$tim[$i+1])>1) and ($c != 0.0)) {
                $speed[$i] = ($distdelta[$i-1]+$c) / ($tim[$i+1]-$tim[$i-1]) * 60 * 60;
              }
            }

            if ($speed[$i] > $this->_params['timovespeed']) { // default: 2.0 km/h
              $duration_moving += $tim[$i+1]-$tim[$i];
            } else {
              $duration_paused += $tim[$i+1]-$tim[$i];
            }
          }
        }
        $dist[$i] = $distance_km;
      }
      // fill up last array indexes:
      $dist[$n-1] = $distance_km;
      $distdelta[$n-1] = 0.0;
      $speed[$n-1] = 0.0;

      $dist[$n] = $distance_km;
      $distdelta[$n] = 0.0;
      $speed[$n] = 0.0;

      // anti-alias speed:
      if ($this->_params['spdfilterorder'] > 1) {
        $speed = $this->filterSignal($speed,$n,$this->_params['spdfilterorder']);

      }
    } // if n>0

    $m_ft = 1 / 0.3048;
    $kmh_mph = 1 / 1.609;
    $kmh_kn = 1 / 1.852;
    
    // average distance of two trackpoints
    // fix for error reported by Steve in the forum on 2013-02-13: GPX files w/o any waypoints
    // can report $n = 0, that again would cause a "div by 0" error in the following:
    if ($n>0) {
      $tpdistance_m = round($distance_km / $n * 1000,2);
    } else {
      $tpdistance_m = 0;
    }

    $tpdistance_ft = round($tpdistance_m * $m_ft,2);

    // round distances - more precision would be pretentious :-)
    $distance_mi = round($distance_km * $kmh_mph, 1);
    $distance_nm = round($distance_km * $kmh_kn, 1); // V1.1.4 added nautical miles
    $distance_km = round($distance_km, 1);

    $maxspeed_kmh = 0.0;

    for ($i=0; $i < ($n-1); $i++) {
      if ($speed[$i] > $maxspeed_kmh) {
        $maxspeed_kmh = $speed[$i];
      }
    }

    // V1.1.4: same for the max. speed
    $maxspeed_mph = round($maxspeed_kmh * $kmh_mph, 1);
    $maxspeed_kn = round($maxspeed_kmh * $kmh_kn, 1);
    $maxspeed_kmh = round($maxspeed_kmh, 1);

    // add up/down meters

    $maxspeedup_kmh = 0.0;
    $maxspeeddown_kmh = 0.0;

    if ($found_ele == 1)
    {
      $up_m   = 0.0;
      $down_m = 0.0;

      $timeup = 0; // seconds
      $timedown = 0;
      $distup = 0.0; // km
      $distdown = 0.0;

      // Note: summing up the up/down deltas is not a very good way to calculate the up/down meters, if the elevation data has
      // a lot of "jitter" or "ripple" - so the results will not be very exact.
      // one option is to use tools like GPSBabel to filter the GPX track to get better accuracy.

      // in a first attempt we can do some optional "anti-aliasing" of rippled elevation data with a filter of a given order:
      // (note: Garmin Basecamp e.g. doesn't do any filtering here and happily reports the same values as with order 0 or 1 (= no filtering).
      // Magic Maps even reports MORE up/down meters (?!?) than the simple delta addition... no idea how they come up with THAT...)

      if ($this->_params['edfilterorder'] > 1) {
        $ele = $this->filterSignal($ele,$n,$this->_params['edfilterorder']);
      }

      for ($i=0; $i < ($n-1); $i++) {
        if ($ele[$i] < $ele[$i+1]) {
          $up_m += $ele[$i+1] - $ele[$i];
          if ($found_time == 1) {
            if ($speed[$i] > $maxspeedup_kmh) {
             $maxspeedup_kmh = $speed[$i];
            }
            $timeup += $tim[$i+1]-$tim[$i];
            $distup += $distdelta[$i];
          }
        }
        if ($ele[$i] > $ele[$i+1]) {
          $down_m += $ele[$i] - $ele[$i+1];
          if ($found_time == 1) {
            if ($speed[$i] > $maxspeeddown_kmh) {
             $maxspeeddown_kmh = $speed[$i];
            }
            $timedown += $tim[$i+1]-$tim[$i];
            $distdown += $distdelta[$i];
          }
        }
      }

      if (($timedown>0) and ($distdown>0.0)) {
        $avgspeeddown_kmh = $distdown / $timedown * 60 * 60;

        $avgspeeddown_mph = round($avgspeeddown_kmh * $kmh_mph, 1);
        $avgspeeddown_kn = round($avgspeeddown_kmh * $kmh_kn, 1);
        $avgspeeddown_kmh = round($avgspeeddown_kmh, 1);
      }

      if (($timeup>0) and ($distup>0.0)) {
        $avgspeedup_kmh = $distup / $timeup * 60 * 60;

        $avgspeedup_mph = round($avgspeedup_kmh * $kmh_mph, 1);
        $avgspeedup_kn = round($avgspeedup_kmh * $kmh_kn, 1);
        $avgspeedup_kmh = round($avgspeedup_kmh, 1);
      }

      $maxspeedup_mph = round($maxspeedup_kmh * $kmh_mph, 1);
      $maxspeedup_kn = round($maxspeedup_kmh * $kmh_kn, 1);
      $maxspeedup_kmh = round($maxspeedup_kmh, 1);

      $maxspeeddown_mph = round($maxspeeddown_kmh * $kmh_mph, 1);
      $maxspeeddown_kn = round($maxspeeddown_kmh * $kmh_kn, 1);
      $maxspeeddown_kmh = round($maxspeeddown_kmh, 1);

      // round all elevations - don't pretend better accuracy than there actually is...
      $up_m   = round($up_m);
      $down_m = round($down_m);
      $minele_m = round($minele_m);
      $maxele_m = round($maxele_m);
      $deltaele_m = round($maxele_m-$minele_m);
      $up_ft   = round($up_m * $m_ft);
      $down_ft = round($down_m * $m_ft);
      $minele_ft = round($minele_m * $m_ft);
      $maxele_ft = round($maxele_m * $m_ft);
      $deltaele_ft = round( ($maxele_m-$minele_m) * $m_ft);
    } 

    //v1.2.3 fix: clear vars if ti=0
    
    if ( ($found_ele != 1) or ($this->_params['ti'] != 1) ) {
      $down_m = "n/a";
      $up_m   = "n/a";
      $minele_m = "n/a";
      $maxele_m = "n/a";
      $deltaele_m = "n/a";
      $down_ft = "n/a";
      $up_ft   = "n/a";
      $minele_ft = "n/a";
      $maxele_ft = "n/a";
      $deltaele_ft = "n/a";
      $avgspeedup_kmh = "n/a";
      $avgspeeddown_kmh = "n/a";
      $avgspeedup_mph = "n/a";
      $avgspeeddown_mph = "n/a";
      $avgspeedup_kn = "n/a";
      $avgspeeddown_kn = "n/a";
      $maxspeedup_kmh = "n/a";
      $maxspeeddown_kmh = "n/a";
      $maxspeedup_mph = "n/a";
      $maxspeeddown_mph = "n/a";
      $maxspeedup_kn = "n/a";
      $maxspeeddown_kn = "n/a";
    }

    if ($found_time == 1) {
      $starttimestr = strftime($this->_params['tidatefmt'],$starttime);
      $endtimestr = strftime($this->_params['tidatefmt'],$endtime);
      $durationstr = strftime($this->_params['titimefmt'],$endtime-$starttime);
      $durationmovingstr = strftime($this->_params['titimefmt'],$duration_moving);
      $durationpausedstr = strftime($this->_params['titimefmt'],$duration_paused);

      if ($endtime !== $starttime) { // avoids div by 0
        $avgspeed_kmh = round($distance_km / ($endtime-$starttime) * 60 * 60,1);
        $avgspeed_mph = round($distance_mi / ($endtime-$starttime) * 60 * 60,1);
        $avgspeed_kn = round($distance_nm / ($endtime-$starttime) * 60 * 60,1);
      } else {
        $avgspeed_kmh = "n/a";
        $avgspeed_mph = "n/a";
        $avgspeed_kn = "n/a";
      }

      if ($duration_moving > 0) { // avoids div by 0
        $avgspeedmoving_kmh = round($distance_km / ($duration_moving) * 60 * 60,1);
        $avgspeedmoving_mph = round($distance_mi / ($duration_moving) * 60 * 60,1);
        $avgspeedmoving_kn = round($distance_nm / ($duration_moving) * 60 * 60,1);
      } else {
        $avgspeedmoving_kmh = "n/a";
        $avgspeedmoving_mph = "n/a";
        $avgspeedmoving_kn = "n/a";
      }
    }

    if ( ($found_time != 1) or ($this->_params['ti'] != 1) ) {
      $starttimestr = "n/a";
      $endtimestr = "n/a";
      $durationstr = "n/a";
      $durationmovingstr = "n/a"; // V1.2.0 beta 2 fix
      $durationpausedstr = "n/a";
      $avgspeed_kmh = "n/a";
      $avgspeed_mph = "n/a";
      $avgspeed_kn = "n/a";
      $avgspeedmoving_kmh = "n/a"; // V1.2.0 beta 2 fix
      $avgspeedmoving_mph = "n/a";
      $avgspeedmoving_kn = "n/a";
      $maxspeed_kmh = "n/a";
      $maxspeed_mph = "n/a";
      $maxspeed_kn = "n/a";
    }

    if (($found_ele == 1) and ($this->_params['ed'] == 1)) {
      $this->_params['haseledata'] = 1;
      // V1.1.4: option to show units in m or ft in elevation diagram
      $elediagfn = $this->renderDiagram($dist,$ele,$n,$minele_m,$maxele_m,$distance_km,$this->_params['edunits'],$filepath,$filename,'',
                                        $this->_params['edbgcolor'],$this->_params['edlinecolor'],$this->_params['edfillmode'],$this->_params['edupcolor'],$this->_params['eddowncolor'],
                                        $this->_params['edlinewidth'],
                                        $this->_params['edxgrid'],$this->_params['edxgridunits'],$this->_params['edxgridlimit'],
                                        $this->_params['edxgridwidth'],$this->_params['edxgridcolor'],
                                        $this->_params['edygridlines'],$this->_params['edygridwidth'],$this->_params['edygridcolor']);
      if ($elediagfn == "") {
        $this->_warnings .= 'Unable to write svg file into folder '.$filepath.'. Please check write permissions!'."\n";
      }
    } else {
      $elediagfn = '';
    }

    // V1.1.4: option to show speed diagram
    if (($found_time == 1) and ($this->_params['spd'] == 1)) {
      $this->_params['hasspddata'] = 1;
      $spddiagfn = $this->renderDiagram($dist,$speed,$n,0.0,$maxspeed_kmh,$distance_km,$this->_params['spdunits'],$filepath,$filename,'_speed',
                                        $this->_params['spdbgcolor'],$this->_params['spdlinecolor'],$this->_params['spdfillmode'],$this->_params['spdupcolor'],$this->_params['spddowncolor'],
                                        $this->_params['spdlinewidth'],
                                        $this->_params['spdxgrid'],$this->_params['spdxgridunits'],$this->_params['spdxgridlimit'],
                                        $this->_params['spdxgridwidth'],$this->_params['spdxgridcolor'],
                                        $this->_params['spdygridlines'],$this->_params['spdygridwidth'],$this->_params['spdygridcolor']);
    } else {
      $spddiagfn = '';
    }

    $sep = $this->_params['tidecimalsep'];

    if ($sep !== '.') {
      $distance_km = str_replace('.',$sep,$distance_km);
      $distance_mi = str_replace('.',$sep,$distance_mi);
      $distance_nm = str_replace('.',$sep,$distance_nm);
      $up_m  = str_replace('.',$sep,$up_m);
      $down_m  = str_replace('.',$sep,$down_m);
      $minele_m  = str_replace('.',$sep,$minele_m);
      $maxele_m  = str_replace('.',$sep,$maxele_m);
      $deltaele_m  = str_replace('.',$sep,$deltaele_m);
      $up_ft  = str_replace('.',$sep,$up_ft);
      $down_ft  = str_replace('.',$sep,$down_ft);
      $minele_ft  = str_replace('.',$sep,$minele_ft);
      $maxele_ft  = str_replace('.',$sep,$maxele_ft);
      $deltaele_ft  = str_replace('.',$sep,$deltaele_ft);
      $avgspeed_kmh  = str_replace('.',$sep,$avgspeed_kmh);
      $avgspeed_mph  = str_replace('.',$sep,$avgspeed_mph);
      $avgspeed_kn  = str_replace('.',$sep,$avgspeed_kn);
      $avgspeedup_kmh  = str_replace('.',$sep,$avgspeedup_kmh);
      $avgspeedup_mph  = str_replace('.',$sep,$avgspeedup_mph);
      $avgspeedup_kn  = str_replace('.',$sep,$avgspeedup_kn);
      $avgspeeddown_kmh  = str_replace('.',$sep,$avgspeeddown_kmh);
      $avgspeeddown_mph  = str_replace('.',$sep,$avgspeeddown_mph);
      $avgspeeddown_kn  = str_replace('.',$sep,$avgspeeddown_kn);
      $avgspeedmoving_kmh  = str_replace('.',$sep,$avgspeedmoving_kmh);
      $avgspeedmoving_mph  = str_replace('.',$sep,$avgspeedmoving_mph);
      $avgspeedmoving_kn  = str_replace('.',$sep,$avgspeedmoving_kn);
      $maxspeed_kmh  = str_replace('.',$sep,$maxspeed_kmh);
      $maxspeed_mph  = str_replace('.',$sep,$maxspeed_mph);
      $maxspeed_kn  = str_replace('.',$sep,$maxspeed_kn);
      $maxspeedup_kmh  = str_replace('.',$sep,$maxspeedup_kmh);
      $maxspeedup_mph  = str_replace('.',$sep,$maxspeedup_mph);
      $maxspeedup_kn  = str_replace('.',$sep,$maxspeedup_kn);
      $maxspeeddown_kmh  = str_replace('.',$sep,$maxspeeddown_kmh);
      $maxspeeddown_mph  = str_replace('.',$sep,$maxspeeddown_mph);
      $maxspeeddown_kn  = str_replace('.',$sep,$maxspeeddown_kn);
      $tpdistance_m  = str_replace('.',$sep,$tpdistance_m);
      $tpdistance_ft = str_replace('.',$sep,$tpdistance_ft);
    }

    // v1.2.3 fix: clear track infos if ti=0
    if ($this->_params['ti'] != 1) {
      $tpdistance_m = "n/a";   
      $tpdistance_ft = "n/a";   
      $distance_km = "n/a";
      $distance_nm = "n/a";
      $distance_mi = "n/a";
      $n = "n/a";
    }
    
    return array (
      $elediagfn,
      $spddiagfn,
      $n,
      $distance_km,
      $distance_mi,
      $distance_nm,
      $up_m,
      $down_m,
      $minele_m,
      $maxele_m,
      $deltaele_m,
      $up_ft,
      $down_ft,
      $minele_ft,
      $maxele_ft,
      $deltaele_ft,
      $starttimestr,  // $starttime,
      $endtimestr,    // $endtime,
      $durationstr, // $endtime-$starttime
      $durationmovingstr,
      $durationpausedstr,
      $avgspeed_kmh,
      $avgspeed_mph,
      $avgspeed_kn,
      $avgspeedup_kmh,
      $avgspeedup_mph,
      $avgspeedup_kn,
      $avgspeeddown_kmh,
      $avgspeeddown_mph,
      $avgspeeddown_kn,
      $avgspeedmoving_kmh,
      $avgspeedmoving_mph,
      $avgspeedmoving_kn,
      $maxspeed_kmh,
      $maxspeed_mph,
      $maxspeed_kn,
      $maxspeedup_kmh,
      $maxspeedup_mph,
      $maxspeedup_kn,
      $maxspeeddown_kmh,
      $maxspeeddown_mph,
      $maxspeeddown_kn,
      $tpdistance_m,
      $tpdistance_ft,
      'wptcount' => $wptcount,
      'wpts' => $wpts);
  }

  private function expandWptSymbolFn($symbolfn)
  {
    $templateDir = $this->_live_site.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR. JFactory::getApplication()->getTemplate();

    $dirs = array(substr($this->_plugin_dir,0,-1),
                  substr($this->_live_site.$this->_rootfolder,0,-1),
                  $templateDir);
//echo("PluginDir:".$dirs[0]."<br />");
//echo("GPXDir:".$dirs[1]."<br />");
//echo("TemplateDir:".$dirs[2]."<br />");

    $phs = array('%PLUGINDIR%','%GPXDIR%','%TEMPLATEDIR%');

    // replace %TEMPLATES% placeholder in filenames with the plugin dir path
    $symfn = str_replace($phs,$dirs,$symbolfn);

    $symfn = strtr( $symfn , '\\', '/');

    return $symfn;  
  }
  
  private function getWptSymbolFn($symbol,&$symbolwidth,&$symbolheight,&$offsetleft,&$offsettop)
  {
    //todo: make default icon size configurable
    $symbolwidth = 16;
    $symbolheight = 16;
    
    // find symbol in mappings
    $mappings = explode("\n",$this->params->get('wpsymbolmappings'));

    // mappings in this format: symbol name["|"symbol name["|"symbolname]]=image file[","image width[","image height]]
    // Example: Water source|Picninc area=%GPXDIR%/icons/myicon.png,24,24
    
    $defaultsymbolfn = '%PLUGINDIR%/markers/waypointmarker16.png';
    
    foreach ($mappings as $mapping) {
      $parts = explode('=',$mapping);   

      $syms = explode('|',$parts[0]);
      foreach ($syms as $sym) {     
        if ($sym == $symbol) {
          $syminfo = explode(',',$parts[1]);
          $symfn = $this->expandWptSymbolFn($syminfo[0]);
          if (isset($syminfo[1]) and ($syminfo[1] != '')) {
            $symbolwidth = $syminfo[1]; 
          } 
          if (isset($syminfo[2]) and ($syminfo[2] != '')) {
            $symbolheight = $syminfo[2]; 
          }
          if (isset($syminfo[3]) and ($syminfo[3] != '')) {
            $offsetleft = $syminfo[3]; 
          }
          if (isset($syminfo[4]) and ($syminfo[4] != '')) {
            $offsettop = $syminfo[4]; 
          }
          return $symfn;    
        } elseif ($sym == '*') { // "*" is used if no other match was found
          $syminfo = explode(',',$parts[1]);
          $defaultsymbolfn = $this->expandWptSymbolFn($syminfo[0]);
          if (isset($syminfo[1]) and ($syminfo[1] != '')) {
            $symbolwidth = $syminfo[1]; 
          } 
          if (isset($syminfo[2]) and ($syminfo[2] != '')) {
            $symbolheight = $syminfo[2]; 
          }
          if (isset($syminfo[3]) and ($syminfo[3] != '')) {
            $offsetleft = $syminfo[3]; 
          }
          if (isset($syminfo[4]) and ($syminfo[4] != '')) {
            $offsettop = $syminfo[4]; 
          }
        }
      } 
    }   
    
    return $this->expandWptSymbolFn($defaultsymbolfn);
  }
  
  private function makeWptCode($wptcount,$wpts,$mapvar,$mapno)
  {
    $s = "\n"."var toMercator = OpenLayers.Projection.transforms['EPSG:4326']['EPSG:3857'];"."\n".
         "var features = [];"."\n";

    if ((!is_numeric($this->_params['wppopupwidth'])) or ($this->_params['wppopupwidth'] <= 0)) {
      $this->_params['wppopupwidth'] = 300;
    }
    
    if ((!is_numeric($this->_params['wppopupheight'])) or ($this->_params['wppopupheight'] <= 0)) {
      $this->_params['wppopupheight'] = 300;
    }
    
    $popupsize = $this->_params['wppopupwidth'].','.$this->_params['wppopupheight'];
    
    if ($this->_params['wpsymbols'] != 0) 
    {
      // V1.2.3: add waypoint symbols     

      if ($this->_params['wppopups'] != 0) {
        // OnClick-handler for the Waypoint symbols
          $s .= 'var wptmarkerClick'.$mapno.' = function (evt) {'."\n";
          $s .= '    if (this.popup == null) {'."\n";
          $s .= '        this.popup = this.createPopup(this.closeBox);'."\n";
//todo: make max size configurable!       
          $s .= '        this.popup.maxSize = new OpenLayers.Size('.$popupsize.');'."\n";
          $s .= '        map'.$mapno.'.addPopup(this.popup);'."\n";
          $s .= '        this.popup.show();'."\n";
          $s .= '    } else {'."\n";
          $s .= '        this.popup.toggle();'."\n";
          $s .= '    }'."\n";
          $s .= '    currentPopup = this.popup;'."\n";
          $s .= '    OpenLayers.Event.stop(evt);'."\n";
          $s .= '};'."\n";
      }
                   
      for ($i=0; $i < $wptcount; $i++)
      {
      //    if ($wpts[$i]['sym'] !== '') {
        {
          // assemble code for Waypoint Symbol marker and optional popup    
          $symbolwidth = 24;
          $symbolheight= 24;
          
          $offsetleft = 0; // offset to top-left corner of the "hot spot"
          $offsettop = 0;
          
          $wptsymbolfn = $this->getWptSymbolFn($wpts[$i]['sym'],$symbolwidth,$symbolheight,$offsetleft,$offsettop);

          $s .= "\n";
          $s .= ' var wptsize'.$i.' = new OpenLayers.Size('.$symbolwidth.','.$symbolheight.');'."\n";
          
          if (($offsetleft != 0) AND ($offsettop != 0)) {
            $s .= ' var wptoffset'.$i.' = new OpenLayers.Pixel(-('.$offsetleft.'), -('.$offsettop.'));'."\n";
          } else {
            // if no offsets supplied, use the center of the symbol like Basecamp does:          
            $s .= ' var wptoffset'.$i.' = new OpenLayers.Pixel(-(wptsize'.$i.'.w/2), -wptsize'.$i.'.h/2);'."\n";
          }
          
          $s .= ' var wpticon'.$i.' = new OpenLayers.Icon("'.$wptsymbolfn.'",'."\n";
          $s .= '       wptsize'.$i.',wptoffset'.$i.');'."\n";
            
          $s .= ' wptgeo'.$i.' = toMercator(new OpenLayers.Geometry.Point('.$wpts[$i]['lon'].','.$wpts[$i]['lat'].'));'."\n";
          $s .= ' wptll'.$i.' = new OpenLayers.LonLat.fromString(wptgeo'.$i.'.toShortString()); '."\n";
          $s .= ' var wptfeature'.$i.' = new OpenLayers.Feature(layerMarkers'.$mapno.', wptll'.$i.');'."\n";

          $s .= ' wptfeature'.$i.'.data.icon = wpticon'.$i.';'."\n";
          $s .= ' wptfeature'.$i.'.closeBox = true;'."\n";
          $s .= ' wptfeature'.$i.'.popupClass = OpenLayers.Popup.FramedCloud;'."\n";
          
          // now assemble the html code for the popup. Sadly, this works quite different then
          // with the Geometry layer (code below in ELSE branch, so a lot of this code is _almost_
          // duplicate compared to the code there...
            
          $wpthtml = ""; 
          
          $wptname = $wpts[$i]['name'];
    
        $wptele = '';
        
          if (($this->_params['wppopupele'] !== 0) AND ($wpts[$i]['ele'] != ''))
          {
            $e = $wpts[$i]['ele'];
    
            if ($this->_params['wppopupele'] == 'ft') {
              $m_ft = 1 / 0.3048;
              $e = $e * $m_ft;
              $wptele = ' ('.round($e).' ft)';
            } 
            if ($this->_params['wppopupele'] == 'm') {
              $wptele = ' ('.round($e).' m)';
          }
          }
    
          if (($this->_params['wppopuptimefmt'] != '0') AND ((integer)$wpts[$i]['time'] != 0))
          {
            $t = strftime($this->_params['wppopuptimefmt'],(integer)$wpts[$i]['time']);
            $wpttime= '<br />'.$t;
          } else {
            $wpttime= '';
          }
    
          if (($this->_params['wppopupdesc'] != '0') AND ((string)$wpts[$i]['desc'] != ''))
          {
            $d = nl2br((string)$wpts[$i]['desc']);
            // the attribute text may not contain carriage returns or linefeeds:
            $d = preg_replace('/\r\n|\r|\n/', '', $d);
    
            $wptdesc = '<br />'.$d;
          } else {
            $wptdesc = '';
          }
    
          if (($this->_params['wppopuplinkfmt'] != '0') AND ((string)$wpts[$i]['links'] != ''))
          {
            $wptlinks = '<br />';
            $l = explode("\n",(string)$wpts[$i]['links']);
            $n = 1;
            foreach ($l as $lnk) {
              if (trim($lnk) != '') {
                $d = str_replace('%N%',$n,$this->_params['wppopuplinkfmt']);
                if ($n>1) { $wptlinks .= '&nbsp;'; }
                $wptlinks .= '<a href=\''.$lnk.'\' target=\'_blank\'>'.$d.'</a>';
                $n += 1;
              }
            }
    
          } else {
            $wptlinks = '';
          }
          
          $wpthtml =
              "<div style='font-size:.8em'>".
              "<span class='gpxwptname'>".$wptname."</span>".
              "<span class='gpxwptele'>".$wptele."</span>".
              "<span class='gpxwpttime'>".$wpttime."</span>".
              "<span class='gpxwpdesc'>".$wptdesc."</span>".
              "<span class='gpxwplinks'>".$wptlinks."</span></div>";
          
          $wpthtml = '"'.$wpthtml.'"';
          
          $s .= ' wptfeature'.$i.'.data.popupContentHTML = '.$wpthtml.';'."\n";
          $s .= ' wptfeature'.$i.'.data.overflow = "auto";'."\n";
     
          $s .= ' var wptmarker'.$i.' = wptfeature'.$i.'.createMarker(); '."\n";
          
          if ($this->_params['wppopups'] == 1) {
            $evt = '"mouseover"'; 
          } else {
            $evt = '"mousedown"';
          }   
            if ($this->_params['wppopups'] != 0) {
            $s .= ' wptmarker'.$i.'.events.register('.$evt.', wptfeature'.$i.', wptmarkerClick'.$mapno.');'."\n";
            // V1.4.1 added for touchevents in mobile/tablet browsers
            $s .= ' wptmarker'.$i.'.events.register("touchstart", wptfeature'.$i.', wptmarkerClick'.$mapno.');'."\n";
          }
          
          $s .= ' layerMarkers'.$mapno.'.addMarker(wptmarker'.$i.');'."\n";

        }
      }
    } else { // no symbols (Graphics), but dots (Geometry layer)
    
        // add each waypoint as vector feature to separate vector layer
        for ($i=0; $i < $wptcount; $i++)
        {
          $w =  "\n".
                'features['.$i.'] = new OpenLayers.Feature.Vector(toMercator(new OpenLayers.Geometry.Point('.
                $wpts[$i]['lon'].','.$wpts[$i]['lat'].')),'."\n".
                '{ ';
    
          $w .= // attributes for the popup
                'wptname: "'.$wpts[$i]['name'].'", ';
          
        $wptele = '';
        
          if (($this->_params['wppopupele'] !== 0) AND ($wpts[$i]['ele'] != ''))
          {
            $e = $wpts[$i]['ele'];
    
            if ($this->_params['wppopupele'] == 'ft') {
              $m_ft = 1 / 0.3048;
              $e = $e * $m_ft;
              $wptele = ' ('.round($e).' ft)';
            } 
            if ($this->_params['wppopupele'] == 'm') {
              $wptele = ' ('.round($e).' m)';
          }
          }
        
          if ($wptele == '') {      
            $w .= 'wptele: "", ';
          } else {
            $w .= 'wptele: "'.$wptele.'", ';
          }
    
          if (($this->_params['wppopuptimefmt'] != '0') AND ((integer)$wpts[$i]['time'] != 0))
          {
            $t = strftime($this->_params['wppopuptimefmt'],(integer)$wpts[$i]['time']);
            $w .= 'wpttime: "<br />'.$t.'", ';
          } else {
            $w .= 'wpttime: "", ';
          }
    
          if (($this->_params['wppopupdesc'] != '0') AND ((string)$wpts[$i]['desc'] != ''))
          {
            $d = nl2br((string)$wpts[$i]['desc']);
            // the attribute text may not contain carriage returns or linefeeds:
            $d = preg_replace('/\r\n|\r|\n/', '', $d);
    
            $w .= 'wptdesc: "<br />'.$d.'", '."\n";
          } else {
            $w .= 'wptdesc: "", '."\n";
          }
    
          if (($this->_params['wppopuplinkfmt'] != '0') AND ((string)$wpts[$i]['links'] != ''))
          {
            $w .= 'wptlinks: "<br />';
            $l = explode("\n",(string)$wpts[$i]['links']);
            $n = 1;
            foreach ($l as $lnk) {
              if (trim($lnk) != '') {
                $d = str_replace('%N%',$n,$this->_params['wppopuplinkfmt']);
                if ($n>1) { $w .= '&nbsp;'; }
                $w .= '<a href=\''.$lnk.'\' target=\'_blank\'>'.$d.'</a>';
                $n += 1;
              }
            }
    
            $w .='", '."\n";
          } else {
            $w .= 'wptlinks: "", ';
          }
    
          $w .= 'wptnum : '.$i.' '. // v1.2.1 fix: remove trailing comma here
                '},'."\n".
                '{ '.
            'fillColor : \''.$this->_params['wpcolor'].'\', '.
            'fillOpacity : '.$this->_params['trackopacity'].', '.
            'strokeColor : "'.$this->_params['trackcolor'].'", '.
            'strokeOpacity : '.$this->_params['trackopacity'].', '.
            'strokeWidth : 1, '.
            'pointRadius : '.$this->_params['wpradius'].', '.
            'cursor: "pointer"} );'."\n\n";
    
          $s .= $w;
        }
    
        if ($this->_params['wppopups'] != 0) { // V1.2.2 fix: disabling popups didn't work
          $s .=
          // create the layer with listeners to create and destroy popups
          'var vector = new OpenLayers.Layer.Vector("Points",{'."\n".
          '  eventListeners:{'."\n".
          '    \'featureselected\':function(evt){'."\n".
          '    var feature = evt.feature;'."\n".
          '    var popup = new OpenLayers.Popup.FramedCloud("popup",'."\n". 
          '          OpenLayers.LonLat.fromString(feature.geometry.toShortString()),'."\n".
          '          null,'."\n";
    
          // contents for the popups:
          $s .=
          ' "<div style=\'font-size:.8em\'>'.
          '<span class=\'gpxwptname\'>" + feature.attributes.wptname + "</span>'.
          '<span class=\'gpxwptele\'>" + feature.attributes.wptele +  "</span>'.
          '<span class=\'gpxwpttime\'>" + feature.attributes.wpttime +  "</span>'.
          '<span class=\'gpxwpdesc\'>" + feature.attributes.wptdesc +  "</span>'.
          '<span class=\'gpxwplinks\'>" + feature.attributes.wptlinks +  "</span>"';
    
          $s .= '+ "</div>",'."\n";
    
          $s .=
          'null,true); '."\n".
//todo: make max size configurable!       
          'popup.maxSize = new OpenLayers.Size('.$popupsize.');'."\n".
          'feature.popup = popup; '."\n".
          $mapvar.'.addPopup(popup); }, '."\n".
          '    \'featureunselected\':function(evt){'."\n".
          '     var feature = evt.feature;'."\n".
          '     '.$mapvar.'.removePopup(feature.popup);'."\n".
          '     feature.popup.destroy();'."\n".
          '     feature.popup = null; } } });'."\n".
          'vector.addFeatures(features);'."\n";
            
          $s .= // create the select feature control
          'var selector = new OpenLayers.Control.SelectFeature(vector,{'."\n";
    
          if ($this->_params['wppopups'] == 1) {
            $s .= ' hover:true,'."\n";
          } else {
            $s .= ' toggle:true,'."\n";
          }
    
          $s .=
          ' autoActivate:true }); '."\n".
          $mapvar.'.addLayer(vector); '."\n".
          $mapvar.'.addControl(selector); '."\n";
       } 
    }
    return $s;
  }

  private function filterSignal($s,$n,$order)
  {
    $sn = array();

    if ($order < $n) {
      $sum = $s[0];
      $sn[0] = $s[0];

      for ($i=1; $i < $order; $i++) {
        $sn[$i] = $sum / $i;
        $sum = $sum + $s[$i];
      }

      for ($i=$order; $i<$n; $i++) {
        $sn[$i] = $sum / $order;
        $sum = $sum - $s[$i-$order] +  $s[$i];
      }
    }
    return $sn;
  }

  private function renderDiagram($dist,$data,$n,$mindata,$maxdata,$distance,$uom,$filepath,$filename,$filenamesuffix,
                                 $diabgcolor,$dialinecolor,$diafillmode,$diaupcolor,$diadowncolor,$dialinewidth,
                                 $xgrids,$xgridunits,$xgridlimit,$xgridwidth,$xgridcolor,
                                 $ygridlines,$ygridwidth,$ygridcolor)
  {
   $ext = pathinfo($filename,PATHINFO_EXTENSION);
   $pos = strrpos($filename,$ext);

   if ($pos !== false) {
     $destfile = substr_replace($filename, 'svg', $pos, strlen($filename)); // replace .gpx with .svg
   }

   if ($filenamesuffix != '') {
     $fn = pathinfo($filename,PATHINFO_FILENAME);
     $pos = strrpos($filename,$fn);

     if ($pos !== false) {
      $destfile = substr_replace($filename, $fn.$filenamesuffix, $pos, strlen($filename)).'.svg'; // turn track.svg into track-speed.svg
     }
   }

   if (JFile::exists($filepath.$destfile) AND ($this->_params['cache'] == 1)) {
     return $destfile;
   }

   $m_ft = 1 / 0.3048;
   $kmh_mph = 1 / 1.609;
   $kmh_kn = 1 / 1.852;

   $sep = $this->_params['tidecimalsep'];

   $e = '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="1000" height="500"'."\n".
        ' viewBox="0 0 10000 10000" preserveAspectRatio="none">'."\n";

   $e .= '<rect x="0" y="0" width="100%" height="100%" stroke="gray" stroke-width="0" fill="'.$diabgcolor.'" fill-opacity="1" />'."\n";

   // horizontal altitude lines

   // v1.2.2 configurable number of grid lines. Bottom coord=500, Top=9500
   if ($ygridlines>0) {
     for ($i=0; $i<$ygridlines; $i++) {
       if ($i==0) {
         $yco = 500;
       } else {
         $yco = 500 + ($i * (9000/($ygridlines-1)));
       }
       $e .= '<line x1="0"    y1="'.$yco.'" x2="10000" y2="'.$yco.'"  stroke-width="'.$ygridwidth.'" stroke="'.$ygridcolor.'" />'."\n";
     }
   }

   $d1 = $dist[0];
   $a1 = $data[0];

   $yrange = ($maxdata-$mindata) * 1.1; // 5% "headroom" above and below
   $yofs   = ($maxdata-$mindata) * 0.05;
   $xrange = $distance;

   if ($yrange==0) {
     return '';
   }

   // x axis grid

   if (($xgrids > '') AND ((string)$xgrids != '0')) {
     $xs = explode('/',trim($xgrids));

     foreach ($xs as $xgrid) {

       if ($xgrid > 0.0) {
         switch ($xgridunits) {
           case ( 'm') : $xgrid_m = $xgrid * 1.0; break;
           case ('km') : $xgrid_m = $xgrid * 1000.0; break;
           case ('ft') : $xgrid_m = $xgrid / $m_ft; break;
           case ('mi') : $xgrid_m = $xgrid * 1000.0 / $kmh_mph; break;
           case ('nm') : $xgrid_m = $xgrid * 1000.0 / $kmh_kn; break;
         }

         if ($distance*1000 / $xgrid_m <= $xgridlimit)
         { break; }
       }
     } // foreach

     // auto-correct if still to many grid lines
     while ($distance*1000 / $xgrid_m > $xgridlimit ) {
       $xgrid_m = $xgrid_m * 2;
       $xgrid   = $xgrid   * 2;
     }

     $xofs = $xgrid; // in display units
     $xofs_m = $xgrid_m;

     while ($xofs_m < $distance * 1000) { // $distance is [km] !
       $xcoord = $xofs_m / $xrange * 10;

       $e .= '<line x1="'.round($xcoord).'" y1="0" x2="'.round($xcoord).'" y2="10000"  stroke-width="'.$xgridwidth.'" stroke="'.$xgridcolor.'" />'."\n";

       $xdist = round($xofs,1).' '.$xgridunits;

       if ($sep !== '.') {
         $xdist = str_replace('.',$sep,$xdist);
       }

       $e .= '<g transform="scale(0.3,1)"><text x="'.round($xcoord*(1/0.3)+100).'" y="9950" font-family="Verdana" font-size="500" fill="black">'.$xdist.'</text></g>'."\n";

       $xofs   += $xgrid;
       $xofs_m += $xgrid_m;
     }
   }

   $x1 = 0;
   $y1 = (($a1-$mindata+$yofs) / $yrange )* 100;

   $x1coord = round($x1*100);
   $y1coord = 10000-round($y1*100);

   $lw = $dialinewidth * 10;

   // here goes line/polygon diagram...

   $polyline = '<polyline fill-opacity="0" stroke-width="'.$lw.'" stroke="'.$dialinecolor.'" points="'.$x1coord.','.$y1coord.' ';
   $polygons = '';

   if ($xrange==0) {
     return '';
   }
   for ($i=1; $i < ($n-1); $i++) {
     $d2 = $dist[$i];
     $a2 = $data[$i];

     $x2 = ($d2) / $xrange * 100;
     $y2 = (($a2-$mindata+$yofs) / $yrange) * 100;

     $x2coord = round($x2*100);
     $y2coord = 10000-round($y2*100);

     $fill = '';

     if ($diafillmode == 1) { // delta (up/down)
       if ($x2<>$x1) {
         $m = (($y2-$y1)/($x2-$x1));
         if ($m>0) {
          $fill = $diaupcolor;
         }
         else {
           $fill = $diadowncolor;
           $m = -$m;
         }
         $m = round(sqrt(sqrt($m))*0.4,3); // gives better "opacity/color distribution"
       } else {
         $m=0;
         $fill= "white";
       }
     }
     if ($diafillmode == 2) { // abs
       $m = ($a2 - $mindata) / ($maxdata-$mindata); // 0..1.0
       $fill = $diaupcolor;
     }

     if ($fill !== '') {
       $polygons .= '<polygon fill-opacity="'.$m.'" fill="'.$fill.'" stroke-width="0" points="'.
                     $x1coord.',9500 '.$x1coord.','.$y1coord.' '.$x2coord.','.$y2coord.' '.$x2coord.',9500" />'."\n";
     }

     $polyline .= $x2coord.','.$y2coord.' ';

     $x1 = $x2;
     $y1 = $y2;

     $x1coord = $x2coord;
     $y1coord = $y2coord;
   }

   $e .= $polygons;
   $e .= $polyline.'" />';

   // min/max elevation captions on y axis
   //TODO: remove "magic consts" in here with calculated offsets... y values go down from top/left origin.
   $e .= '<g transform="scale(0.3,1)">'."\n";

   if ($ygridlines>0) {
     for ($i=0; $i<$ygridlines; $i++) {
       if ($i==$ygridlines-1) {
         $yco = 9950;//500 - 50;
         $ecur = $mindata;
       } else {
         $yco = 500 -50 + ($i * (9000/($ygridlines-1)));
         $ecur = $maxdata - ( ($maxdata-$mindata) / ($ygridlines-1) * $i );
       }

       if ($uom == "m") { // elevation - data is in [m]
         $ecur = round($ecur,1);
       }
       if ($uom == "ft") { // elevation - data is in [m]
         $ecur = round($ecur * $m_ft,1);
       }
       if ($uom == "km/h") { // speed - data is in [km/h]
        $ecur = round($ecur,1);
       }
       if ($uom == "mph") { // speed - data is in [km/h]
        $ecur = round($ecur * $kmh_mph,1);
       }
       if ($uom == "kn") { // speed - data is in [km/h]
        $ecur = round($ecur * $kmh_kn,1);
       }

       if ($sep !== '.') {
         $ecur = str_replace('.',$sep,$ecur);
       }

       $e .= '<text x="100" y="'.$yco.'" font-family="Verdana" font-size="500" fill="black">'.$ecur.' '.$uom.'</text>'."\n";
     }
   }

   $e .= '</g>'."\n";

   // close surrounding translate() group tag
   $e .= '</svg>'."\n";

   // always re-create (for now), but: see cache setting above
   if (JFile::exists($filepath.$destfile)) { JFile::delete($filepath.$destfile); }

   // V1.2.2 check write permission
   if (! $this->is__writable($filepath.$destfile)) {
     return '';
   } else {
     $fh = fopen($filepath.$destfile,"c");
     fwrite($fh,$e);
     fclose($fh);

     return $destfile;
   }
  }

  private function getGPXTime($gpxtimestr)
  {
   // timestamps in GPX files have the following format:
   // 2012-12-03T09:08:43Z
   // 01234567890123456789 (offsets)

   $y = (int)substr($gpxtimestr, 0,4);
   $m = (int)substr($gpxtimestr, 5,2);
   $d = (int)substr($gpxtimestr, 8,2);
   $h = (int)substr($gpxtimestr,11,2);
   $n = (int)substr($gpxtimestr,14,2);
   $s = (int)substr($gpxtimestr,17,2);

   $ts = gmmktime($h,$n,$s,$m,$d,$y); // no UTC conversion fanciness, please! just as it appears in the file...

   if ($this->_params["titimeshift"] != 0) {
     $ts = $ts + $this->_params["titimeshift"] * 60 * 60;
   }
   return $ts;
  }

  private function ziptrackfile($filepath,$filename)
  {
    $ext = pathinfo($filename,PATHINFO_EXTENSION);
    $pos = strrpos($filename,$ext); // search from the right
    //maybe add in a check here if extension is really "gpx" ??

    if($pos !== false)
    { $destfile = substr_replace($filename, 'zip', $pos, strlen($filename)); // replace .gpx with .zip
    }

    $fullgpxpath = $this->_absolute_path.DIRECTORY_SEPARATOR.$filepath.$filename;
    $fullzippath = $this->_absolute_path.DIRECTORY_SEPARATOR.$filepath.$destfile;

    if (JFile::exists($fullzippath) AND ($this->_params['cache'] == 1))
    { return $filepath.$destfile; }

    jimport('joomla.filesystem.archive');
    jimport('joomla.filesystem.file');

    // V1.2.2 check write permission
    if (! $this->is__writable($fullzippath)) {
      $this->_warnings .= 'Unable to create zip file of the gpx file. Please check write permissions!'."\n";
      return false;
    }
    if (!JFile::exists($fullgpxpath))
    { return false; }

    $filesToZip[] = array(
    'data' => JFile::read($fullgpxpath),
    'name' => JFile::getName($filename));

    $zip = JArchive::getAdapter('zip');
    $zip->create($fullzippath, $filesToZip);

    if (JFile::exists($fullzippath)) // it should now!
    { return $filepath.$destfile; }
    else
    { return false; }
  }

  private function markerFilename($markertype, $markerset)
  {
    switch($markertype)
    { case(1): $clr = 'blue'; break;
      case(2): $clr = 'red'; break;
      case(3): $clr = 'green'; break;
      case(4): $clr = 'yellow'; break;
      case(5): $clr = 'white'; break;
      case(6): $clr = 'gray'; break;
      case(7): $clr = 'black'; break;
      case('blue'):
      case('red'):
      case('green'):
      case('yellow'):
      case('white'):
      case('gray'):
      case('black'):
        $clr = $markertype; // so one can put "startmarker=red" in the call instead of "startmarker=2"
      default: $clr = '';
    }

    if ($clr=='')
    { return ''; }
    else
    { return 'marker'.$markerset.'-'.$clr.'.png'; } // marker1-blue.png
  }

  private function is__writable($path) {
    // from www.php.net article on is_writeable:
    if ($path{strlen($path)-1}=='/') // recursively return a temporary file path
      return is__writable($path.uniqid(mt_rand()).'.tmp');
    else if (is_dir($path))
      return is__writable($path.'/'.uniqid(mt_rand()).'.tmp');
    // check tmp file for read/write capabilities
    $rm = file_exists($path);
    $f = @fopen($path, 'a');
    if ($f===false)
      return false;
    fclose($f);
    if (!$rm)
      unlink($path);
    return true;
  }
}
