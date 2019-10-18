'use strict';

/**
 * Integrates the Google IMA SDK to enable advertising on your video content.
 */

Object.assign(mejs.MepDefaults, {			  
	imaAdTagUrl: 'https://pubads.g.doubleclick.net/gampad/ads?sz=640x480&iu=/124319096/external/single_ad_samples&ciu_szs=300x250&impl=s&gdfp_req=1&env=vp&output=vast&unviewed_position_start=1&cust_params=deployment%3Ddevsite%26sample_ct%3Dskippablelinear&correlator=',
	imaAdTagVariables: {
		siteUrl: '',
		videoID: 0,
		videoTitle: '',
		videoExcerpt: '',
		ipAddress: ''
	},
	imaVpaidMode: 'enabled',	
	imaLiveStreamAdInterval: 300
});


Object.assign(MediaElementPlayer.prototype, {
			  
	imaMainContainer: '',
	imaContentSrc: '',
	imaAdsManager: '',
	imaAdsManagerReady: '',
	imaAdsLoader: '',
	imaAdDisplayContainer: '',	
	imaAdDisplayContainerInitialized: '',
	imaAdType: '',
	imaAdWidth: '',
	imaAdHeight: '',
	imaIntervalHandler:'',
	imaContentCompleteCalled: '',

    buildima: function buildima( player, controls, layers, media ) {
		
        var t = this;

		t.imaMainContainer = document.getElementById( 'mep_0' );
		mejs.Utils.addClass( t.imaMainContainer, 'ima-initialize' );
		
		t.imaLayer = document.createElement( 'div' );
		t.imaLayer.className = t.options.classPrefix + 'overlay ' + t.options.classPrefix + 'layer ' + t.options.classPrefix + 'ima';
		
		t.layers.insertBefore( t.imaLayer, t.layers.querySelector( '.' + t.options.classPrefix + 'overlay-play' ) );
		
		t.imaContentPlayEventProxy = t.imaContentPlayEvent.bind( t );
		t.imaContentEndedEventProxy = t.imaContentEndedEvent.bind( t );
				
		t.imaInitSetup();

		t.autoplayRequested = t.node.attributes.autoplay && 'false' !== t.node.attributes.autoplay;		
		if ( t.autoplayRequested ) {
			
			if ( window.imaAutoplayChecksResolved ) {
				t.imaRequestAds(0);
			} else {
				var intervalHandler = setInterval(
					function() {
						if ( window.imaAutoplayChecksResolved ) {
							clearInterval( intervalHandler );
							t.imaRequestAds(0);
						}
					},
					100 ); // every 100ms
			}
			
		} else {
			t.imaRequestAds(0);
		}
		
		t.layers.querySelector( '.' + t.options.classPrefix + 'ima' ).addEventListener( 'click', t.imaContentPlayEventProxy );
		t.media.addEventListener( 'play', t.imaContentPlayEventProxy );
		
		var fullScreenEvents = [ 'fullscreenchange', 'mozfullscreenchange', 'webkitfullscreenchange' ];		
  		for ( var index in fullScreenEvents ) {
    		document.addEventListener( fullScreenEvents[ index ], t.imaAdResizeEvent.bind( t ), false );
  		}
		window.addEventListener( 'resize', t.imaAdResizeEvent.bind( t ) );
		
    },	
	
	imaInitSetup: function imaInitSetup() {
		
		var t = this;
		
		// Set the VPAID mode.
	  	t.imaSetVpaidMode();
		
	  	// Create the ad display container.
	  	t.imaCreateAdDisplayContainer();
	  
	  	// Create ads loader.
		t.imaAdsLoader = new google.ima.AdsLoader( t.imaAdDisplayContainer );
		t.imaAdsLoader.getSettings().setDisableCustomPlaybackForIOS10Plus( true );
		
		// Listen and respond to ads loaded and error events.
		t.imaAdsLoader.addEventListener( google.ima.AdsManagerLoadedEvent.Type.ADS_MANAGER_LOADED, t.imaAdsManagerLoadedEvent.bind( t ), false );
		t.imaAdsLoader.addEventListener( google.ima.AdErrorEvent.Type.AD_ERROR, t.imaAdErrorEvent.bind( t ), false );
		
		// An event listener to tell the SDK that our content video
		// is completed so the SDK can play any post-roll ads.
		t.media.addEventListener( 'ended', t.imaContentEndedEventProxy );	
		  
	},
	
	imaSetVpaidMode: function imaSetVpaidMode() {
		
		var t = this;
		
		switch ( t.options.imaVpaidMode ) {
			case 'enabled':
				google.ima.settings.setVpaidMode( google.ima.ImaSdkSettings.VpaidMode.ENABLED );
				break;
			case 'insecure':
				google.ima.settings.setVpaidMode( google.ima.ImaSdkSettings.VpaidMode.INSECURE );
				break;
			case 'disabled':
				google.ima.settings.setVpaidMode( google.ima.ImaSdkSettings.VpaidMode.DISABLED );
				break;
		}
		
		
	},
	
	imaCreateAdDisplayContainer: function imaCreateAdDisplayContainer() {
		
		var t = this;
	  	t.imaAdDisplayContainer = new google.ima.AdDisplayContainer( t.imaLayer, t.media );
  
	},
	
	imaContentEndedEvent: function imaContentEndedEvent() {
		
		var t = this;
		
		t.imaContentCompleteCalled = true;
		
  		if ( t.imaAdsLoader ) {
    		t.imaAdsLoader.contentComplete();
  		}
  
	},
	
	imaContentPlayEvent: function imaContentPlayEvent() {
		
		var t = this;		
		var playAds = false;

		mejs.Utils.removeClass( t.imaMainContainer, 'ima-initialize' );
		
		if ( ! t.imaAdDisplayContainerInitialized ) {
			
			t.layers.querySelector( '.' + t.options.classPrefix + 'ima' ).removeEventListener( 'click', t.imaContentPlayEventProxy );
			t.media.removeEventListener( 'play', t.imaContentPlayEventProxy );
			
			t.imaContentSrc = t.media.originalNode.src;
			playAds = true;
			
		} else {
			
			var contentSrc = t.media.originalNode.src;
			
			if ( contentSrc != t.imaContentSrc ) {
				t.imaContentSrc = contentSrc;
				t.imaRequestAds(0);
				playAds = true;
			}
			
		}
		
		
		if ( playAds ) {
			
			if ( t.imaAdsManagerReady ) {
				t.imaPlayAds();
			} else {
				var intervalHandler = setInterval(
					function() {
						if ( t.imaAdsManagerReady ) {
							clearInterval( intervalHandler );
							t.imaPlayAds();
						}
					},
					100 ); // every 100ms
			}
			
		}
		
	},
	
	imaRequestAds: function imaRequestAds( liveStreamPrefetchSeconds ) {
		
		var t = this;		

		// Destroy the current AdsManager, in case the tag you requested previously
  		// contains post-rolls (we don't want to play those now).
		t.imaAdsManagerReady = false;
		
  		if ( t.imaAdsManager ) {
    		t.imaAdsManager.destroy();
  		}
		
		// Reset the IMA SDK.
  		if ( t.imaAdsLoader ) {    
    		t.imaAdsLoader.contentComplete();			
  		}
		
		t.imaContentCompleteCalled = false;
		
		// Request video ads.
		var adsRequest = new google.ima.AdsRequest();		  
		adsRequest.adTagUrl = t.imaGetAdTagUrl();
		
		// Specify the linear and nonlinear slot sizes. This helps the SDK to
		// select the correct creative if multiple are returned.
		adsRequest.linearAdSlotWidth = t.container.clientWidth;
		adsRequest.linearAdSlotHeight = t.container.clientHeight;
		
		adsRequest.nonLinearAdSlotWidth = t.container.clientWidth;
		adsRequest.nonLinearAdSlotHeight = t.container.clientHeight;
		
		if ( window.imaAutoplayAllowed ) {
			adsRequest.setAdWillAutoPlay( window.imaAutoplayAllowed );
		}
		
		if ( window.imaAutoplayRequiresMuted ) {
  			adsRequest.setAdWillPlayMuted( window.imaAutoplayRequiresMuted );
		}
		
		if ( t.options.forceLive ) {
			adsRequest.liveStreamPrefetchSeconds = liveStreamPrefetchSeconds;
		}
		
		t.imaAdsLoader.requestAds( adsRequest );
		
	},
	
	imaGetAdTagUrl: function imaGetAdTagUrl() {
		
		var t = this;	
		
		var imaAdTagUrl = t.options.imaAdTagUrl;
		
		imaAdTagUrl = imaAdTagUrl.replace( '[domain]', encodeURIComponent( t.options.imaAdTagVariables.siteUrl ) );
		imaAdTagUrl = imaAdTagUrl.replace( '[player_width]', t.container.clientWidth );
		imaAdTagUrl = imaAdTagUrl.replace( '[player_height]', t.container.clientHeight );
		imaAdTagUrl = imaAdTagUrl.replace( '[random_number]', Date.now() );
		imaAdTagUrl = imaAdTagUrl.replace( '[timestamp]', Date.now() );
		imaAdTagUrl = imaAdTagUrl.replace( '[page_url]', encodeURIComponent( window.top.location ) );
		imaAdTagUrl = imaAdTagUrl.replace( '[referrer]', encodeURIComponent( document.referrer ) );
		imaAdTagUrl = imaAdTagUrl.replace( '[ip_address]', t.options.imaAdTagVariables.ipAddress );
		imaAdTagUrl = imaAdTagUrl.replace( '[video_id]', t.options.imaAdTagVariables.videoID );
		imaAdTagUrl = imaAdTagUrl.replace( '[video_title]', encodeURIComponent( t.options.imaAdTagVariables.videoTitle ) );
		imaAdTagUrl = imaAdTagUrl.replace( '[video_excerpt]', encodeURIComponent( t.options.imaAdTagVariables.videoExcerpt ) );
		imaAdTagUrl = imaAdTagUrl.replace( '[video_file]', encodeURIComponent( t.media.originalNode.src ) );

		var duration = '';
		if ( t.media.duration ) {
			duration = t.media.duration;
		}
		imaAdTagUrl = imaAdTagUrl.replace( '[video_duration]', duration );
		
		var autoplay = 0;
		if ( t.autoplayRequested ) {
			if ( window.imaAutoplayAllowed ) {
				autoplay = 1;
			}
			
			if ( window.imaAutoplayRequiresMuted && t.media.volume > 0 ) {
				autoplay = 0;
			}
		}
		imaAdTagUrl = imaAdTagUrl.replace( '[autoplay]', autoplay );
			
		return imaAdTagUrl;
		
	},
		
	imaAdsManagerLoadedEvent: function imaAdsManagerLoadedEvent( adsManagerLoadedEvent ) {
		
		var t = this;
		
		// Get the ads manager.
  		var adsRenderingSettings = new google.ima.AdsRenderingSettings();
		adsRenderingSettings.uiElements = [ google.ima.UiElements.AD_ATTRIBUTION, google.ima.UiElements.COUNTDOWN ];
  		adsRenderingSettings.restoreCustomPlaybackStateOnAdBreakComplete = true;
  		
  		t.imaAdsManager = adsManagerLoadedEvent.getAdsManager( t, adsRenderingSettings );
		
		// Add listeners to the required events.
  		t.imaAdsManager.addEventListener( google.ima.AdErrorEvent.Type.AD_ERROR, t.imaAdErrorEvent.bind( t ) );
  		t.imaAdsManager.addEventListener( google.ima.AdEvent.Type.CONTENT_PAUSE_REQUESTED, t.imaContentPauseRequestedEvent.bind( t ) );
  		t.imaAdsManager.addEventListener( google.ima.AdEvent.Type.CONTENT_RESUME_REQUESTED, t.imaContentResumeRequestedEvent.bind( t ) );
  		t.imaAdsManager.addEventListener( google.ima.AdEvent.Type.ALL_ADS_COMPLETED, t.imaAdEvent.bind( t ) );
		
		// Listen to any additional events, if necessary.
  		t.imaAdsManager.addEventListener( google.ima.AdEvent.Type.LOADED, t.imaAdEvent.bind( t ) );
  		t.imaAdsManager.addEventListener( google.ima.AdEvent.Type.STARTED, t.imaAdEvent.bind( t ) );
  		t.imaAdsManager.addEventListener( google.ima.AdEvent.Type.COMPLETE, t.imaAdEvent.bind( t ) );
		
		t.imaAdsManagerReady = true;
  
	},
	
	imaAdEvent: function imaAdEvent( adEvent ) {
		
		var t = this;		
		
		// Retrieve the ad from the event. Some events (e.g. ALL_ADS_COMPLETED)
  		// don't have ad object associated.
  		var ad = adEvent.getAd();

		switch ( adEvent.type ) {
			case google.ima.AdEvent.Type.LOADED:
			  	// This is the first event sent for an ad - it is possible to
			  	// determine whether the ad is a video ad or an overlay.				
			  	if ( ad.isLinear() ) {					
					if ( 'linear' != t.imaAdType ) {
						t.imaAdType = 'linear';
						t.imaSetupUIForAds();
					}					
				} else {					
					t.imaAdType = 'non-linear';					
					t.media.play();
					t.imaSetupUIForAds();					
			  	}				
			  break;
			case google.ima.AdEvent.Type.COMPLETE:
			  	// This event indicates the ad has finished - the video player
			  	// can perform appropriate UI actions, such as removing the timer for
			  	// remaining time detection.
				t.imaAdType = '';
				t.imaSetupUIForContent();
			  	break;
			case google.ima.AdEvent.Type.ALL_ADS_COMPLETED:
				if ( t.options.forceLive ) {
					// Pre-fetch our next ad break.
      				t.imaRequestAds( parseInt( t.options.imaLiveStreamAdInterval ) - 10 );
					
					// Play those ads at the next ad break.
      				setTimeout( t.imaPlayAds, parseInt( t.options.imaLiveStreamAdInterval ) * 1000 );
				}
				break;
		  }
		
	},
	
	imaAdErrorEvent: function imaAdErrorEvent( adErrorEvent ) {
		
		var t = this;		
		
		if ( t.imaAdsManager ) {
     		t.imaAdsManager.destroy();
			
			if ( 'linear' == t.imaAdType ) {
				t.imaContentResumeRequestedEvent();
			}
		}		
		
	},
	
	imaContentPauseRequestedEvent: function imaContentPauseRequestedEvent() {
		
		var t = this;
		
		t.media.removeEventListener( 'ended', t.imaContentEndedEventProxy );		
		t.media.pause();
		
		t.imaAdType = 'linear';
		t.imaSetupUIForAds();
		
	},
	
	imaContentResumeRequestedEvent: function imaContentResumeRequestedEvent() {
		
		var t = this;
		
		if ( ! t.imaContentCompleteCalled ) {
			t.media.addEventListener( 'ended', t.imaContentEndedEventProxy );			
			t.media.play();
		}
		
		t.imaSetupUIForContent();
		
	},
	
	imaSetupUIForAds: function imaSetupUIForAds() {
		
		var t = this;
		
		clearInterval( t.imaIntervalHandler );		
		
		mejs.Utils.removeClass( t.imaMainContainer, 'ima-offscreen' );
				
		if ( 'linear' == t.imaAdType ) {
			mejs.Utils.removeClass( t.imaMainContainer, 'ima-non-linear-display' );
			mejs.Utils.addClass( t.imaMainContainer, 'ima-linear-display' );
		} else if ( 'non-linear' == t.imaAdType ) {
			mejs.Utils.removeClass( t.imaMainContainer, 'ima-linear-display' );
			mejs.Utils.addClass( t.imaMainContainer, 'ima-non-linear-display' );
			
			t.imaIntervalHandler = setInterval(
				function() {
					if ( mejs.Utils.hasClass( t.controls, 'mejs__offscreen' ) ) {
						mejs.Utils.addClass( t.imaMainContainer, 'ima-offscreen' );
					} else {
						mejs.Utils.removeClass( t.imaMainContainer, 'ima-offscreen' );
					}
				},
				300 ); // every 300ms
		}
				
	},
	
	imaSetupUIForContent: function imaSetupUIForContent() {
		
		var t = this;
		
		clearInterval( t.imaIntervalHandler );			
		
		mejs.Utils.removeClass( t.imaMainContainer, 'ima-linear-display' );
		mejs.Utils.removeClass( t.imaMainContainer, 'ima-non-linear-display' );	
		mejs.Utils.removeClass( t.imaMainContainer, 'ima-offscreen' );
	
	},
	
	imaPlayAds: function imaPlayAds() {
		
		var t = this;		
		
		// Initialize the container. Must be done via a user action on mobile devices.
		if ( ! t.imaAdDisplayContainerInitialized ) {
			// t.media.load();
			
			t.imaAdDisplayContainer.initialize();
			t.imaAdDisplayContainerInitialized = true;			
		}
	
	  	try {
			// Initialize the ads manager. Ad rules playlist will start at this time.
			t.imaAdsManager.init( t.container.clientWidth, t.container.clientHeight, t.imaViewMode() );
		
			// Call play to start showing the ad. Single video and overlay ads will
			// start at this time; the call will be ignored for ad rules.
			t.imaAdsManager.start();
	  	} catch ( adError ) {
			// An error may be thrown if there was a problem with the VAST response.
			t.media.play();
	  	}
		
	},
	
	imaAdResizeEvent: function imaAdResizeEvent() {
		
		var t = this;
		
		if ( t.imaAdsManager ) {
			
			var width  = t.container.clientWidth,
				height = t.container.clientHeight;
				
			if ( t.imaAdWidth != width || t.imaAdHeight != height ) {
				t.imaAdWidth  = width;
				t.imaAdHeight = height;
				t.imaAdsManager.resize( t.imaAdWidth, t.imaAdHeight, t.imaViewMode() );
			}
			
		}
		
	},
	
	imaViewMode: function imaViewMode() {
		
		if ( mejs.Features.hasTrueNativeFullScreen && mejs.Features.isFullScreen() ) {
			return google.ima.ViewMode.FULLSCREEN;
		} else {
			return google.ima.ViewMode.NORMAL;
		}
		
	}
	
});

/**
 * Check if HTML5 video autoplay is supported.
 */
(function () {
		   
    var autoplayAllowed, autoplayRequiresMuted;
	
	var videoElement = document.createElement( 'video' );
	videoElement.id = 'avs-video-hidden';
    videoElement.src = "data:video/mp4;base64,AAAAFGZ0eXBNU05WAAACAE1TTlYAAAOUbW9vdgAAAGxtdmhkAAAAAM9ghv7PYIb+AAACWAAACu8AAQAAAQAAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAAAAAAABAAAAAAAAAAAAAAAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAgAAAnh0cmFrAAAAXHRraGQAAAAHz2CG/s9ghv4AAAABAAAAAAAACu8AAAAAAAAAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAAAAAAABAAAAAAAAAAAAAAAAAABAAAAAAFAAAAA4AAAAAAHgbWRpYQAAACBtZGhkAAAAAM9ghv7PYIb+AAALuAAANq8AAAAAAAAAIWhkbHIAAAAAbWhscnZpZGVBVlMgAAAAAAABAB4AAAABl21pbmYAAAAUdm1oZAAAAAAAAAAAAAAAAAAAACRkaW5mAAAAHGRyZWYAAAAAAAAAAQAAAAx1cmwgAAAAAQAAAVdzdGJsAAAAp3N0c2QAAAAAAAAAAQAAAJdhdmMxAAAAAAAAAAEAAAAAAAAAAAAAAAAAAAAAAFAAOABIAAAASAAAAAAAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAGP//AAAAEmNvbHJuY2xjAAEAAQABAAAAL2F2Y0MBTUAz/+EAGGdNQDOadCk/LgIgAAADACAAAAMA0eMGVAEABGjuPIAAAAAYc3R0cwAAAAAAAAABAAAADgAAA+gAAAAUc3RzcwAAAAAAAAABAAAAAQAAABxzdHNjAAAAAAAAAAEAAAABAAAADgAAAAEAAABMc3RzegAAAAAAAAAAAAAADgAAAE8AAAAOAAAADQAAAA0AAAANAAAADQAAAA0AAAANAAAADQAAAA0AAAANAAAADQAAAA4AAAAOAAAAFHN0Y28AAAAAAAAAAQAAA7AAAAA0dXVpZFVTTVQh0k/Ou4hpXPrJx0AAAAAcTVREVAABABIAAAAKVcQAAAAAAAEAAAAAAAAAqHV1aWRVU01UIdJPzruIaVz6ycdAAAAAkE1URFQABAAMAAAAC1XEAAACHAAeAAAABBXHAAEAQQBWAFMAIABNAGUAZABpAGEAAAAqAAAAASoOAAEAZABlAHQAZQBjAHQAXwBhAHUAdABvAHAAbABhAHkAAAAyAAAAA1XEAAEAMgAwADAANQBtAGUALwAwADcALwAwADYAMAA2ACAAMwA6ADUAOgAwAAABA21kYXQAAAAYZ01AM5p0KT8uAiAAAAMAIAAAAwDR4wZUAAAABGjuPIAAAAAnZYiAIAAR//eBLT+oL1eA2Nlb/edvwWZflzEVLlhlXtJvSAEGRA3ZAAAACkGaAQCyJ/8AFBAAAAAJQZoCATP/AOmBAAAACUGaAwGz/wDpgAAAAAlBmgQCM/8A6YEAAAAJQZoFArP/AOmBAAAACUGaBgMz/wDpgQAAAAlBmgcDs/8A6YEAAAAJQZoIBDP/AOmAAAAACUGaCQSz/wDpgAAAAAlBmgoFM/8A6YEAAAAJQZoLBbP/AOmAAAAACkGaDAYyJ/8AFBAAAAAKQZoNBrIv/4cMeQ==";    
    videoElement.autoplay = true;
    videoElement.style.position = 'fixed';
    videoElement.style.left = '5000px';

   	document.getElementsByTagName( 'body' )[0].appendChild( videoElement );
	var videoContent = document.getElementById( 'avs-video-hidden' );
	
	function checkAutoplaySupport() {
		// Check if autoplay is supported.
  		var playPromise = videoContent.play();
  		if ( playPromise !== undefined ) {
    		playPromise.then( onAutoplayWithSoundSuccess ).catch( onAutoplayWithSoundFail );
  		} else {
			autoplayAllowed = false;
  			autoplayRequiresMuted = false;
  			autoplayChecksResolved();
		}
	}

	function onAutoplayWithSoundSuccess() {
  		// If we make it here, unmuted autoplay works.
  		videoContent.pause();
  		autoplayAllowed = true;
  		autoplayRequiresMuted = false;
  		autoplayChecksResolved();
	}

	function onAutoplayWithSoundFail() {
  		// Unmuted autoplay failed. Now try muted autoplay.
  		checkMutedAutoplaySupport();
	}

	function checkMutedAutoplaySupport() {
  		videoContent.volume = 0;
  		videoContent.muted = true;
  		var playPromise = videoContent.play();
  		if ( playPromise !== undefined ) {
    		playPromise.then( onMutedAutoplaySuccess ).catch( onMutedAutoplayFail );
  		}
	}

	function onMutedAutoplaySuccess() {
  		// If we make it here, muted autoplay works but unmuted autoplay does not.
  		videoContent.pause();
  		autoplayAllowed = true;
  		autoplayRequiresMuted = true;
  		autoplayChecksResolved();
	}

	function onMutedAutoplayFail() {
  		// Both muted and unmuted autoplay failed. Fall back to click to play.
  		videoContent.volume = 1;
  		videoContent.muted = false;
  		autoplayAllowed = false;
  		autoplayRequiresMuted = false;
  		autoplayChecksResolved();
	}
	
	function autoplayChecksResolved() {
		document.getElementsByTagName( 'body' )[0].removeChild( videoElement );
		
		// Announce to the World!
		window.imaAutoplayChecksResolved = true;
	 	window.imaAutoplayAllowed = autoplayAllowed;
		window.imaAutoplayRequiresMuted = autoplayRequiresMuted;
	};	
	
	// ...
	checkAutoplaySupport();
	
})();