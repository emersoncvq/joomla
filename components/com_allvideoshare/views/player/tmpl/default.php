<?php
/*
 * @version		$Id: default.php 3.3.0 2019-01-10 $
 * @package		All Video Share
 * @copyright   Copyright (C) 2012-2019 MrVinoth
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Video Sources
$sources = array();

switch ( $this->video->type ) {
    case 'youtube':
    case 'vimeo':
        $sources[] = array(
            'type' => 'video/' . $this->video->type,
            'src'  => $this->video->video
        );
        break;
    case 'hls':
        $sources[] = array(
            'type' => 'application/x-mpegurl',
            'src'  => $this->video->hls
        );
        break;
    case 'general':
    case 'url': 
    case 'upload':  
        // SD
        $parse_url = parse_url( $this->video->video );
        $ext = pathinfo( $parse_url['path'], PATHINFO_EXTENSION );
        if ( 'mov' == $ext ) $ext = 'mp4';

        $sources[] = array(
            'type'    => "video/$ext",
            'src'     => $this->video->video,
            'quality' => 'SD'
        );
        
        // HD
        if ( $this->hasQualitySwitcher() ) {
            $parse_url = parse_url( $this->video->hd );
            $ext = pathinfo( $parse_url['path'], PATHINFO_EXTENSION );
            if ( 'mov' == $ext ) $ext = 'mp4';
            
            $sources[] = array(
                'type'    => "video/$ext",
                'src'     => $this->video->hd,
                'quality' => 'HD'
            );
        }
        break;
}

// Video Attributes
$attributes = array(
    'controlsList' => 'nodownload',
    'playsinline'  => ''
);

if ( ! empty( $this->video->thumb ) ) {
    $attributes['poster'] = $this->video->thumb;
}

$_attributes = array();

foreach ( $attributes as $key => $value ) {
    if ( '' === $value ) {
        $_attributes[] = $key;
    } else {
        $_attributes[] = sprintf( '%s="%s"', $key, $value );
    }
}

$attributes = implode( ' ', $_attributes );

// Player Settings
$features = array();

if ( $this->player->controlbar ) {
    $features[] = 'playpause';

    if ( $this->player->timerdock ) {
        $features[] = 'current';
    }

    $features[] = 'progress';

    if ( $this->player->durationdock ) {
        $features[] = 'duration';
    }

    if ( $this->hasQualitySwitcher() ) {
        $features[] = 'quality';
    }

    $features[] = 'volume';

    if ( $this->player->fullscreendock ) {
        $features[] = 'fullscreen';
    }
}

if ( $this->hasAds() ) {
    $features[] = 'ima';
}

$features[] = 'avs';
$features[] = 'me';

$settings = array(
	'pluginPath'   => JURI::root() . 'components/com_allvideoshare/assets/mediaelement/',
    'features'     => $features,
    'startVolume'  => (int) $this->player->volumelevel / 100,
    'licenseKey'   => $this->license->licensekey,
	'hideLOGO'     => $this->license->displaylogo ? false : true,
    'logoImage'    => $this->license->logo,
	'logoPosition' => $this->license->logoposition,
	'logoOpacity'  => (int) $this->license->logoalpha / 100,
	'logoClickURL' => empty( $this->license->licensekey ) ? 'https://allvideoshare.mrvinoth.com' : $this->license->logotarget,
	'youtube'      => array( 
        'showinfo'       => 0, 
        'rel'            => 0, 
        'iv_load_policy' => 3
    )	
);

// Ads
if ( $this->hasAds() ) {
    $settings['imaAdTagUrl'] = $this->player->vast_url;

    if ( 'vast' == $this->player->ad_engine ) {
        $excerpt = '';
        if ( ! empty( $this->video->description ) ) {
            $excerpt = AllVideoShareUtils::Truncate( $this->video->description );
            $excerpt = str_replace( '...', '', $excerpt );
        }
        
        $settings['imaAdTagVariables'] = array(
			'siteUrl'     => JURI::root(),
			'videoID'     => $this->video->id,
			'videoTitle'  => $this->video->title,
			'postExcerpt' => $excerpt,
			'ipAddress'   => $this->getIpAddress()
		);
        $settings['imaVpaidMode'] = ! empty( $this->player->vpaid_mode ) ? $this->player->vpaid_mode : 'insecure';
        $settings['imaLiveStreamAdInterval'] = ! empty( $this->player->livestream_ad_interval ) ? $this->player->livestream_ad_interval : 300;
    }    
}

// Embed
$settings['embed'] = array(
    'enabled' => $this->player->embeddock ? true : false,
    'labels'  => array(
        'title'  => JText::_( 'EMBED_TITLE' ),
        'copy'   => JText::_( 'EMBED_BUTTON_LABEL_COPY' ),
        'copied' => JText::_( 'EMBED_BUTTON_LABEL_COPIED' )
    ),
    'code'    => '<iframe width="420" height="315" src="' . JURI::root() . 'index.php?option=com_allvideoshare&view=video&id=' . $this->video->id . '&tmpl=component" frameborder="0" allowfullscreen></iframe>'
);
    
// Share
$settings['share'] = array(
    'enabled'   => $this->player->sharedock ? true : false,
    'labels'    => array(
        'title' => JText::_( 'SHARE_TITLE' )
    ),
    'facebook'  => array(
        'icon' => JURI::root() . 'components/com_allvideoshare/assets/images/facebook.png',
        'url'  => 'https://www.facebook.com/sharer.php?u='. urlencode( $this->getURL() )
    ),
    'twitter'   => array(
        'icon' => JURI::root() . 'components/com_allvideoshare/assets/images/twitter.png',
        'url'  => 'https://twitter.com/share?url='. urlencode( $this->getURL() ) .'&text='. urlencode( $this->video->title )
    ),
    'linkedin'  => array(
        'icon' => JURI::root() . 'components/com_allvideoshare/assets/images/linkedin.png',
        'url'  => 'https://www.linkedin.com/shareArticle?mini=true&url=' . urlencode( $this->getURL() ) . '&title=' . urlencode( $this->video->title )
    ),
    'pinterest' => array(
        'icon' => JURI::root() . 'components/com_allvideoshare/assets/images/pinterest.png',
        'url'  => 'https://pinterest.com/pin/create/bookmarklet/?media='. urlencode( $this->video->thumb ) .'&url='. urlencode( $this->getURL() ) . '&is_video=0&description='. rawurlencode( $this->video->title )
    )
);

// GDPR Consent
if ( $this->showGdprConsent() ) {
    $settings['showGdprConsent'] = true;
    $settings['gdprConsentMessage'] = JText::_( 'GDPR_CONSENT_MESSAGE' );
    $settings['gdprConsentButtonLabel'] = JText::_( 'GDPR_CONSENT_BUTTON_LABEL' );

    $sources = [];
} else {
    $settings['showGdprConsent'] = false;
}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title><?php echo $this->getTitle(); ?></title>    
    <link rel="canonical" href="<?php echo $this->getURL(); ?>" />
    <meta property="og:url" content="<?php echo $this->getURL(); ?>" />
	<link rel="stylesheet" href="<?php echo JURI::root(); ?>components/com_allvideoshare/assets/mediaelement/mediaelementplayer.css?v=<?php echo ALLVIDEOSHARE_VERSION; ?>" />
    <?php if ( $this->hasQualitySwitcher() ) : ?>
        <link rel="stylesheet" href="<?php echo JURI::root(); ?>components/com_allvideoshare/assets/mediaelement/plugins/quality/quality.min.css?v=<?php echo ALLVIDEOSHARE_VERSION; ?>" />
    <?php endif; ?>
	<style type="text/css">
        html, 
        body, 
        video, 
        iframe {
            width: 100% !important;
            height: 100% !important;
            margin:0 !important; 
            padding:0 !important; 
            overflow: hidden;
        }
            
        video, 
        iframe {
            display: block;
        }

        .mejs__container, 
        .mejs__layer {
            width: 100% !important;
            height: 100% !important;
        }

        #mejs__embed-button,
        #mejs__share-button {
			position: absolute;
            width: 30px;
			height: 30px;			
			right: 10px;            
			background-color: rgba( 0, 0, 0, 0.6 );	
            background-position: center;		
			background-repeat: no-repeat;			
			z-index: 9;
			cursor:pointer;
		}

        #mejs__embed-button {
            top: 10px;
            background-image: url( '<?php echo JURI::root(); ?>components/com_allvideoshare/assets/images/embed.png' );
        }

        #mejs__share-button {
            background-image: url( '<?php echo JURI::root(); ?>components/com_allvideoshare/assets/images/share.png' );
        }

        #mejs__embed-box,
        #mejs__share-box {
            position: absolute;		
            width: 100%;
			height: 100%;
            top: 0;
			left: 0;
			background-color: #000;
			overflow: hidden;
			z-index: 999;		
		}

        #mejs__embed-box-inner,
        #mejs__share-box-inner {
            width: 100%;
			height: 100%;
			display: -webkit-box;
			display: -moz-box;
			display: -ms-flexbox;
			display: -webkit-flex;
			display: flex;
			align-items: center;
			justify-content: center;
			text-align:center;	
		}

        #mejs__embed-content,
		#mejs__embed-title,
		#mejs__embed-code,
        #mejs__share-content,
        #mejs__share-title {
			width: 100%;
		}

        #mejs__embed-content,
        #mejs__share-content {
			margin: 10px;
		}

        #mejs__embed-close-button,
        #mejs__share-close-button {
            position: absolute;
            width: 30px;
			height: 30px;
            top: 10px;
			right: 10px;
			background-image: url( '<?php echo JURI::root(); ?>components/com_allvideoshare/assets/images/close.png' );
			background-position: center;			
			background-repeat: no-repeat;
			z-index: 9;
			cursor: pointer;			
		}

        #mejs__embed-title,
        #mejs__share-title {
            color: #EEE;
    		font-size: 11px;
            text-transform: uppercase;
        }

		#mejs__embed-code {
            margin: 10px 0;
            resize: none;
		}

        #mejs__embed-copy-button {
			display: block;
            width: 75px;
            margin: 0 auto;
			padding: 7px 0;
			background: #444;
			border-radius: 2px;			
			color: #CCC;
			font-size: 11px;
			font-weight: bold;
			text-align: center;
            text-transform: uppercase;
			cursor: pointer;
		}
		
		#mejs__embed-copy-button:hover {
			background: #333;
		}

        #mejs__share-icons {
            margin: 10px 0;
        }

        #mejs__share-icons a {
            display: inline-block;
            padding: 5px;
            -webkit-transition: -webkit-transform .5s ease-in-out;
            transition: transform .5s ease-in-out;
        } 

        #mejs__share-icons a:hover {
		    -webkit-transform: rotate( 360deg );
		    transform: rotate( 360deg );
		}  

        #mejs__share-icons img {
            width: 30px;
        }    

        .mejs__ima {
            display: block;            
            cursor: pointer;
            pointer-events: none;
        }

        .ima-initialize  .mejs__ima,
        .ima-linear-display .mejs__ima,
        .ima-non-linear-display .mejs__ima {
            pointer-events: auto;
        }

        .ima-linear-display .mejs__overlay-play,
        .ima-linear-display .mejs__me,
        .ima-linear-display .mejs__controls {
            opacity: 0 !important;
            pointer-events: none;
        }

        .ima-non-linear-display .mejs__ima {
            margin-top: -50px;
            -webkit-transition: margin-top .2s; /* Safari */
            transition: margin-top .2s;
        }

        .ima-non-linear-display.ima-offscreen .mejs__ima {
            margin-top: 0px;
            -webkit-transition: margin-top .2s; /* Safari */
            transition: margin-top .2s;
        }

        .mejs__gdpr {
            color: #FFF;
            text-align: center;
            z-index: 999;
        }
        
        .mejs__gdpr-consent-block {
            margin: 15px;
            padding: 15px;
            background: #000;
            border-radius: 3px;
            opacity: 0.9;
        }
        
        .mejs__gdpr-consent-button {
            display: inline-block;
            margin-top: 10px;
            padding: 5px 15px;
            background: #F00;
            border-radius: 3px;
            cursor: pointer;
        }
        
        .mejs__gdpr-consent-button:hover {
            opacity: 0.8;
        }
		
		.contextmenu {
            position: absolute;
            top: 0;
            left: 0;
            margin: 0;
            padding: 0;
            background: #fff;
			border-radius: 2px;
			box-shadow: 1px 1px 2px #333;
            z-index: 9999999999; /* make sure it shows on fullscreen */
        }
        
        .contextmenu-item {
            margin: 0;
            padding: 8px 12px;
            font-family: 'Helvetica', Arial, serif;
            font-size: 12px;
            color: #222;		
            white-space: nowrap;
            cursor: pointer;
        }
    </style>    
    <?php
    if ( ! empty( $this->config->custom_css ) ) {
        printf( '<style type="text/css">%s</style>', $this->config->custom_css );
    }
    ?>
</head>
<body>
    <video id="player" <?php echo $attributes; ?>>
        <?php 
        foreach ( $sources as $source ) {
            if ( ! empty( $source['quality'] ) ) {
                printf( '<source type="%s" src="%s" data-quality="%s" />', $source['type'], $source['src'], $source['quality'] );
            } else {
                printf( '<source type="%s" src="%s" />', $source['type'], $source['src'] );
            }            
        }
       ?>   
	</video>

    <div id="contextmenu" class="contextmenu" style="display: none;">
        <div class="contextmenu-item">
            <?php
            if ( empty( $this->license->licensekey ) ) {
                echo 'Powered by "All Video Share"';                
            } else {
                $config = JFactory::getConfig();
                echo $config->get( 'sitename' );
            }
            ?>
        </div>
    </div>

	<script src="<?php echo JURI::root(); ?>components/com_allvideoshare/assets/mediaelement/mediaelement-and-player.min.js?v=<?php echo ALLVIDEOSHARE_VERSION; ?>" type="text/javascript"></script>
    <?php if ( 'vimeo' == $this->video->type ) : ?>
	    <script src="<?php echo JURI::root(); ?>components/com_allvideoshare/assets/mediaelement/renderers/vimeo.min.js?v=<?php echo ALLVIDEOSHARE_VERSION; ?>" type="text/javascript"></script>
    <?php endif; ?>
    <?php if ( $this->hasQualitySwitcher() ) : ?>
        <script src="<?php echo JURI::root(); ?>components/com_allvideoshare/assets/mediaelement/plugins/quality/quality.min.js?v=<?php echo ALLVIDEOSHARE_VERSION; ?>" type="text/javascript"></script>
    <?php endif; ?>
    <?php if ( $this->hasAds() ) : ?>
        <script src="https://imasdk.googleapis.com/js/sdkloader/ima3.js?v=<?php echo ALLVIDEOSHARE_VERSION; ?>" type="text/javascript"></script>
        <script src="<?php echo JURI::root(); ?>components/com_allvideoshare/assets/js/ima.js?v=<?php echo ALLVIDEOSHARE_VERSION; ?>" type="text/javascript"></script>
    <?php endif; ?>
    <script type="text/javascript">
		(function() {
			'use strict';
  
            /**
			 * A custom mediaelementjs plugin.
			 */
            Object.assign(MediaElementPlayer.prototype, {			
                buildavs: function buildavs( player, controls, layers, media ) {                
                    var t = this;
                
                    // GDPR Consent
					if ( 1 == t.options.showGdprConsent ) {							
                        t.gdprLayer = document.createElement( 'div' );
                        t.gdprLayer.className = t.options.classPrefix + 'overlay ' + t.options.classPrefix + 'layer ' + t.options.classPrefix + 'gdpr';
                        t.gdprLayer.innerHTML = ( '<div class="' + t.options.classPrefix + 'gdpr-consent-block">' ) + ( '<div class="' + t.options.classPrefix + 'gdpr-consent-message">' + t.options.gdprConsentMessage + '</div>' ) + ( '<div class="' + t.options.classPrefix + 'gdpr-consent-button">' + t.options.gdprConsentButtonLabel + '</div>' ) + '</div>';
                        
                        t.layers.appendChild( t.gdprLayer );
                        
                        t.gdprLayer.querySelector( '.' + t.options.classPrefix + 'gdpr-consent-button' ).addEventListener( 'click',  t.onGdprConsentAgreed.bind( t ) );	
                    }

                    // Embed
                    if ( 1 == t.options.embed.enabled ) {	
                        t.embedLayer = document.createElement( 'div' );
                        t.embedLayer.className = t.options.classPrefix + 'layer ' + t.options.classPrefix + 'embed';
                        t.embedLayer.innerHTML = '<div id="' + t.options.classPrefix + 'embed-button"></div><div id="' + t.options.classPrefix + 'embed-box" style="display: none;"><div id="' + t.options.classPrefix + 'embed-box-inner"><div id="' + t.options.classPrefix + 'embed-close-button"></div><div id="' + t.options.classPrefix + 'embed-content"><div id="' + t.options.classPrefix + 'embed-title">' + t.options.embed.labels.title + '</div><textarea id="' + t.options.classPrefix + 'embed-code">' + t.options.embed.code + '</textarea><div id="' + t.options.classPrefix + 'embed-copy-button">' + t.options.embed.labels.copy + '</div></div></div></div>';

                        t.layers.appendChild( t.embedLayer );

                        // Show or Hide
                        var embedLocked = false;

                        t.container.addEventListener( 'controlsshown',  function() {
                            if ( ! embedLocked ) {
                                mejs.Utils.fadeIn( t.embedLayer );
                            }                            
						});
						
						t.container.addEventListener( 'controlshidden',  function() {
                            if ( ! embedLocked ) {
                                mejs.Utils.fadeOut( t.embedLayer );
                            }
						});

                        // Open
						document.getElementById( t.options.classPrefix + 'embed-button' ).addEventListener( 'click',  function() {
                            embedLocked = true;

                            document.getElementById( t.options.classPrefix + 'embed-button' ).style.display = 'none';						
                            document.getElementById( t.options.classPrefix + 'embed-box' ).style.display = '';	
                        });

                        // Close
                        document.getElementById( t.options.classPrefix + 'embed-close-button' ).addEventListener( 'click',  function() {
                            embedLocked = false;

                            document.getElementById( t.options.classPrefix + 'embed-box' ).style.display = 'none';
                            document.getElementById( t.options.classPrefix + 'embed-button' ).style.display = '';                            	
                        });

                        // Copy
                        document.getElementById( t.options.classPrefix + 'embed-copy-button' ).addEventListener( 'click',  function() {
                            document.getElementById( t.options.classPrefix + 'embed-code' ).select();
                            document.execCommand( 'copy' );  

                            document.getElementById( t.options.classPrefix + 'embed-copy-button' ).textContent = t.options.embed.labels.copied;
                            setTimeout(function() {
                                document.getElementById( t.options.classPrefix + 'embed-copy-button' ).textContent = t.options.embed.labels.copy;
                            }, 2000 );	                       	
                        });
                    }

                    // Share
                    if ( 1 == t.options.share.enabled ) {
                        t.shareLayer = document.createElement( 'div' );
                        t.shareLayer.className = t.options.classPrefix + 'layer ' + t.options.classPrefix + 'share';
                        t.shareLayer.innerHTML = '<div id="' + t.options.classPrefix + 'share-button" style="top: ' + ( 1 == t.options.embed.enabled ? '50px' : '10px' ) + ';"></div><div id="' + t.options.classPrefix + 'share-box" style="display: none;"><div id="' + t.options.classPrefix + 'share-box-inner"><div id="' + t.options.classPrefix + 'share-close-button"></div><div id="' + t.options.classPrefix + 'share-content"><div id="' + t.options.classPrefix + 'share-title">' + t.options.share.labels.title + '</div><div id="' + t.options.classPrefix + 'share-icons"><a href="' + t.options.share.facebook.url + '" target="_blank" class="share-facebook"><span><img src="' + t.options.share.facebook.icon + '" /></span></a><a href="' + t.options.share.twitter.url + '" target="_blank" class="share-twitter"><span><img src="' + t.options.share.twitter.icon + '" /></span></a><a href="' + t.options.share.linkedin.url + '" target="_blank" class="share-linkedin"><span><img src="' + t.options.share.linkedin.icon + '" /></span></a><a href="' + t.options.share.pinterest.url + '" target="_blank" class="share-pinterest"><span><img src="' + t.options.share.pinterest.icon + '" /></span></a></div></div></div></div>';

                        t.layers.appendChild( t.shareLayer );

                        // Show or Hide
                        var shareLocked = false;

                        t.container.addEventListener( 'controlsshown',  function() {
                            if ( ! shareLocked ) {
                                mejs.Utils.fadeIn( t.shareLayer );
                            }                            
						});
						
						t.container.addEventListener( 'controlshidden',  function() {
                            if ( ! shareLocked ) {
                                mejs.Utils.fadeOut( t.shareLayer );
                            }
						});

                        // Open
						document.getElementById( t.options.classPrefix + 'share-button' ).addEventListener( 'click',  function() {
                            shareLocked = true;

                            document.getElementById( t.options.classPrefix + 'share-button' ).style.display = 'none';						
                            document.getElementById( t.options.classPrefix + 'share-box' ).style.display = '';	
                        });

                        // Close
                        document.getElementById( t.options.classPrefix + 'share-close-button' ).addEventListener( 'click',  function() {
                            shareLocked = false;
                            
                            document.getElementById( t.options.classPrefix + 'share-box' ).style.display = 'none';
                            document.getElementById( t.options.classPrefix + 'share-button' ).style.display = '';                            	
                        });
                    }

                    // Custom ContextMenu
                    var contextmenu = document.getElementById( 'contextmenu' );
                    var timeout_handler = '';
                    
                    document.addEventListener( 'contextmenu', function( e ) {                    
                        if ( 3 === e.keyCode || 3 === e.which ) {
                            e.preventDefault();
                            e.stopPropagation();
                            
                            var width = contextmenu.offsetWidth,
                                height = contextmenu.offsetHeight,
                                x = e.pageX,
                                y = e.pageY,
                                doc = document.documentElement,
                                scrollLeft = ( window.pageXOffset || doc.scrollLeft ) - ( doc.clientLeft || 0 ),
                                scrollTop = ( window.pageYOffset || doc.scrollTop ) - ( doc.clientTop || 0 ),
                                left = x + width > window.innerWidth + scrollLeft ? x - width : x,
                                top = y + height > window.innerHeight + scrollTop ? y - height : y;
                    
                            contextmenu.style.display = '';
                            contextmenu.style.left = left + 'px';
                            contextmenu.style.top = top + 'px';
                            
                            clearTimeout( timeout_handler );
                            timeout_handler = setTimeout(function() {
                                contextmenu.style.display = 'none';
                            }, 1500 );				
                        }                                                     
                    });
                    
                    if ( '' != t.options.logoClickURL ) {
                        contextmenu.addEventListener( 'click', function() {
                            top.window.location.href = t.options.logoClickURL;
                        });
                    }
                    
                    document.addEventListener( 'click', function() {
                        contextmenu.style.display = 'none';								 
                    });                    
                },

                onGdprConsentAgreed: function onGdprConsentAgreed() {					
                    var t = this;                   
					t.gdprLayer.querySelector( '.' + t.options.classPrefix + 'gdpr-consent-button' ).innerHTML = '...';        

                    // Set Cookie
                    var xmlhttp;

                    if ( window.XMLHttpRequest ) {
                        xmlhttp = new XMLHttpRequest();
                    } else {
                        xmlhttp = new ActiveXObject( 'Microsoft.XMLHTTP' );
                    };
                    
                    xmlhttp.onreadystatechange = function() {                
                        if ( 4 == xmlhttp.readyState && 200 == xmlhttp.status ) {                    
                            if ( 'success' == xmlhttp.responseText ) {
                                // Reload document
                                window.location.reload();
                            }                        
                        }                    
                    };	

                    xmlhttp.open( 'GET', '<?php echo JURI::root(); ?>index.php?option=com_allvideoshare&view=player&task=gdpr&format=raw', true );
                    xmlhttp.send();  
				}
            });
            
			/**
			 * Initialize the player.
			 */
            var autoplay = <?php echo $this->player->autostart; ?>;
            var loop = <?php echo $this->player->loop; ?>;
            var is_mobile = mejs.Features.isiPad || mejs.Features.isiPhone || mejs.Features.isAndroid;

            var settings = <?php echo json_encode( $settings ); ?>;

            settings.success = function( media ) {

                if ( ! is_mobile && autoplay ) {
                    media.play();
                }

                media.addEventListener( 'ended', function( e ) {
                    if ( loop ) {
                        media.play();
                    }
                });
                
            }

			var player = new MediaElementPlayer( 'player', settings );
		})();
    </script>
</body>
</html>