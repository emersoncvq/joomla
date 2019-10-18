/**
 * @package Helix3 Framework
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
 
 
       // ************    Parallax   ************** //
!function(a){var b=a(window),c=b.height();b.resize(function(){c=b.height()}),a.fn.parallax=function(d,e,f){function k(){var f=b.scrollTop();g.each(function(){var b=a(this),j=b.offset().top,k=h(b);f>j+k||j>f+c||g.css("backgroundPosition",d+" "+Math.round((i-f)*e)+"px")})}var h,i,g=a(this);g.each(function(){i=g.offset().top}),h=f?function(a){return a.outerHeight(!0)}:function(a){return a.height()},(arguments.length<1||null===d)&&(d="50%"),(arguments.length<2||null===e)&&(e=.1),(arguments.length<3||null===f)&&(f=!0),b.bind("scroll",k).resize(k),k()}}(jQuery);
jQuery(document).ready(function($) {
$("#data-parallax").addClass("fixed-background").parallax("100%", 0.4);
});


/*! UIkit 2.27.2 | http://www.getuikit.com | (c) 2014 YOOtheme | MIT License */
!function(e){var t;window.UIkit&&(t=e(UIkit)),"function"==typeof define&&define.amd&&define("uikit-parallax",["uikit"],function(){return t||e(UIkit)})}(function(e){"use strict";function t(t,a,r){var i,n,s,o,c,l,p,f=new Image;return n=t.element.css({backgroundSize:"cover",backgroundRepeat:"no-repeat"}),i=n.css("background-image").replace(/^url\(/g,"").replace(/\)$/g,"").replace(/("|')/g,""),o=function(){var e=n.innerWidth(),i=n.innerHeight(),o="bg"==a?r.diff:r.diff/100*i;return i+=o,e+=Math.ceil(o*c),e-o<s.w&&i<s.h?t.element.css({backgroundSize:"auto"}):(i>e/c?(l=Math.ceil(i*c),p=i,i>window.innerHeight&&(l=1.2*l,p=1.2*p)):(l=e,p=Math.ceil(e/c)),n.css({backgroundSize:l+"px "+p+"px"}).data("bgsize",{w:l,h:p}),void 0)},f.onerror=function(){},f.onload=function(){s={w:f.width,h:f.height},c=f.width/f.height,e.$win.on("load resize orientationchange",e.Utils.debounce(function(){o()},50)),o()},f.src=i,!0}function a(e,t,a){return e=i(e),t=i(t),a=a||0,r(e,t,a)}function r(e,t,a){var r="rgba("+parseInt(e[0]+a*(t[0]-e[0]),10)+","+parseInt(e[1]+a*(t[1]-e[1]),10)+","+parseInt(e[2]+a*(t[2]-e[2]),10)+","+(e&&t?parseFloat(e[3]+a*(t[3]-e[3])):1);return r+=")"}function i(e){var t,a;return a=(t=/#([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})/.exec(e))?[parseInt(t[1],16),parseInt(t[2],16),parseInt(t[3],16),1]:(t=/#([0-9a-fA-F])([0-9a-fA-F])([0-9a-fA-F])/.exec(e))?[17*parseInt(t[1],16),17*parseInt(t[2],16),17*parseInt(t[3],16),1]:(t=/rgb\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*\)/.exec(e))?[parseInt(t[1]),parseInt(t[2]),parseInt(t[3]),1]:(t=/rgba\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9\.]*)\s*\)/.exec(e))?[parseInt(t[1],10),parseInt(t[2],10),parseInt(t[3],10),parseFloat(t[4])]:p[e]||[255,255,255,0]}var n=[],s=!1,o=0,c=window.innerHeight,l=function(){o=e.$win.scrollTop(),window.requestAnimationFrame(function(){for(var e=0;e<n.length;e++)n[e].process()})};e.component("parallax",{defaults:{velocity:.5,target:!1,viewport:!1,media:!1},boot:function(){s=function(){var e,t=document.createElement("div"),a={WebkitTransform:"-webkit-transform",MSTransform:"-ms-transform",MozTransform:"-moz-transform",Transform:"transform"};document.body.insertBefore(t,null);for(var r in a)void 0!==t.style[r]&&(t.style[r]="translate3d(1px,1px,1px)",e=window.getComputedStyle(t).getPropertyValue(a[r]));return document.body.removeChild(t),void 0!==e&&e.length>0&&"none"!==e}(),e.$doc.on("scrolling.uk.document",l),e.$win.on("load resize orientationchange",e.Utils.debounce(function(){c=window.innerHeight,l()},50)),e.ready(function(t){e.$("[data-uk-parallax]",t).each(function(){var t=e.$(this);t.data("parallax")||e.parallax(t,e.Utils.options(t.attr("data-uk-parallax")))})})},init:function(){this.base=this.options.target?e.$(this.options.target):this.element,this.props={},this.velocity=this.options.velocity||1;var t=["target","velocity","viewport","plugins","media"];Object.keys(this.options).forEach(function(e){if(-1===t.indexOf(e)){var a,r,i,n,s=String(this.options[e]).split(",");e.match(/color/i)?(a=s[1]?s[0]:this._getStartValue(e),r=s[1]?s[1]:s[0],a||(a="rgba(255,255,255,0)")):(a=parseFloat(s[1]?s[0]:this._getStartValue(e)),r=parseFloat(s[1]?s[1]:s[0]),n=r>a?r-a:a-r,i=r>a?1:-1),this.props[e]={start:a,end:r,dir:i,diff:n}}}.bind(this)),n.push(this)},process:function(){if(this.options.media)switch(typeof this.options.media){case"number":if(window.innerWidth<this.options.media)return!1;break;case"string":if(window.matchMedia&&!window.matchMedia(this.options.media).matches)return!1}var e=this.percentageInViewport();this.options.viewport!==!1&&(e=0===this.options.viewport?1:e/this.options.viewport),this.update(e)},percentageInViewport:function(){var e,t,a,r=this.base.offset().top,i=this.base.outerHeight();return r>o+c?a=0:o>r+i?a=1:c>r+i?a=(c>o?o:o-c)/(r+i):(e=o+c-r,t=Math.round(e/((c+i)/100)),a=t/100),a},update:function(e){var r,i,n={transform:"",filter:""},o=e*(1-(this.velocity-this.velocity*e));0>o&&(o=0),o>1&&(o=1),(void 0===this._percent||this._percent!=o)&&(Object.keys(this.props).forEach(function(c){switch(r=this.props[c],0===e?i=r.start:1===e?i=r.end:void 0!==r.diff&&(i=r.start+r.diff*o*r.dir),"bg"!=c&&"bgp"!=c||this._bgcover||(this._bgcover=t(this,c,r)),c){case"x":n.transform+=s?" translate3d("+i+"px, 0, 0)":" translateX("+i+"px)";break;case"xp":n.transform+=s?" translate3d("+i+"%, 0, 0)":" translateX("+i+"%)";break;case"y":n.transform+=s?" translate3d(0, "+i+"px, 0)":" translateY("+i+"px)";break;case"yp":n.transform+=s?" translate3d(0, "+i+"%, 0)":" translateY("+i+"%)";break;case"rotate":n.transform+=" rotate("+i+"deg)";break;case"scale":n.transform+=" scale("+i+")";break;case"bg":n["background-position"]="50% "+i+"px";break;case"bgp":n["background-position"]="50% "+i+"%";break;case"color":case"background-color":case"border-color":n[c]=a(r.start,r.end,o);break;case"blur":n.filter+=" blur("+i+"px)";break;case"hue":n.filter+=" hue-rotate("+i+"deg)";break;case"grayscale":n.filter+=" grayscale("+i+"%)";break;case"invert":n.filter+=" invert("+i+"%)";break;case"fopacity":n.filter+=" opacity("+i+"%)";break;case"saturate":n.filter+=" saturate("+i+"%)";break;case"sepia":n.filter+=" sepia("+i+"%)";break;default:n[c]=i}}.bind(this)),n.filter&&(n["-webkit-filter"]=n.filter),this.element.css(n),this._percent=o)},_getStartValue:function(e){var t=0;switch(e){case"scale":t=1;break;default:t=this.element.css(e)}return t||0}});var p={black:[0,0,0,1],blue:[0,0,255,1],brown:[165,42,42,1],cyan:[0,255,255,1],fuchsia:[255,0,255,1],gold:[255,215,0,1],green:[0,128,0,1],indigo:[75,0,130,1],khaki:[240,230,140,1],lime:[0,255,0,1],magenta:[255,0,255,1],maroon:[128,0,0,1],navy:[0,0,128,1],olive:[128,128,0,1],orange:[255,165,0,1],pink:[255,192,203,1],purple:[128,0,128,1],violet:[128,0,128,1],red:[255,0,0,1],silver:[192,192,192,1],white:[255,255,255,1],yellow:[255,255,0,1],transparent:[255,255,255,0]};return e.parallax});

jQuery(function ($) {

    // ************    START Helix 1.4 JS    ************** //
    // **************************************************** //

    //Default
    if (typeof sp_offanimation === 'undefined' || sp_offanimation === '') {
        sp_offanimation = 'default';
    }

    if (sp_offanimation == 'default') {
        $('#offcanvas-toggler').on('click', function (event) {
            event.preventDefault();
            $('.off-canvas-menu-init').addClass('offcanvas');
        });

        $('<div class="offcanvas-overlay"></div>').insertBefore('.offcanvas-menu');
        $('.close-offcanvas, .offcanvas-overlay').on('click', function (event) {
            event.preventDefault();
            $('.off-canvas-menu-init').removeClass('offcanvas');
        });
    }

    // Slide Top Menu
    if (sp_offanimation == 'slidetop') {
        $('#offcanvas-toggler').on('click', function (event) {
            event.preventDefault();
            $('.off-canvas-menu-init').addClass('slide-top-menu');
        });

        $('<div class="offcanvas-overlay"></div>').insertBefore('.offcanvas-menu');
        $('.close-offcanvas, .offcanvas-overlay').on('click', function (event) {
            event.preventDefault();
            $('.off-canvas-menu-init').removeClass('slide-top-menu');
        });
    }

    //Full Screen
    if (sp_offanimation == 'fullscreen') {
        $('#offcanvas-toggler').on('click', function (event) {
            event.preventDefault();
            $('.off-canvas-menu-init').addClass('full-screen-off-canvas');
        });
        $(document).ready(function () {
            $('.off-canvas-menu-init').addClass('full-screen');
        });
        $('.close-offcanvas, .offcanvas-overlay').on('click', function (event) {
            event.preventDefault();
            $('.off-canvas-menu-init').removeClass('full-screen-off-canvas');
        });
    }

    //Full screen from top
    if (sp_offanimation == 'fullScreen-top') {
        $('#offcanvas-toggler').on('click', function (event) {
            event.preventDefault();
            $('.off-canvas-menu-init').addClass('full-screen-off-canvas-ftop');
        });
        $(document).ready(function () {
            $('.off-canvas-menu-init').addClass('full-screen-ftop');
        });
        $('.close-offcanvas, .offcanvas-overlay').on('click', function (event) {
            event.preventDefault();
            $('.off-canvas-menu-init').removeClass('full-screen-off-canvas-ftop');
        });
    }

    //Dark with plus
    if (sp_offanimation == 'drarkplus') {
        $('#offcanvas-toggler').on('click', function (event) {
            event.preventDefault();
            $('.off-canvas-menu-init').addClass('new-look-off-canvas');
        });
        $('<div class="offcanvas-overlay"></div>').insertBefore('.offcanvas-menu');
        $(document).ready(function () {
            $('.off-canvas-menu-init').addClass('new-look');
        });
        $('.close-offcanvas,.offcanvas-overlay').on('click', function (event) {
            event.preventDefault();
            $('.off-canvas-menu-init').removeClass('new-look-off-canvas');
        });
    }

    // if sticky header
    if ($("body.sticky-header").length > 0) {
        var fixedSection = $('#sp-header');
        // sticky nav
        var headerHeight = fixedSection.outerHeight();
        var stickyNavTop = fixedSection.offset().top;
        fixedSection.addClass('animated');
        fixedSection.before('<div class="nav-placeholder"></div>');
        $('.nav-placeholder').height('inherit');
        //add class
        fixedSection.addClass('menu-fixed-out');
        var stickyNav = function () {
            var scrollTop = $(window).scrollTop();
            if (scrollTop > stickyNavTop) {
                fixedSection.removeClass('menu-fixed-out').addClass('menu-fixed');
                $('.nav-placeholder').height(headerHeight);
            } else {
                if (fixedSection.hasClass('menu-fixed')) {
                    fixedSection.removeClass('menu-fixed').addClass('menu-fixed-out');
                    $('.nav-placeholder').height('inherit');
                }
            }
        };
        stickyNav();
        $(window).scroll(function () {
            stickyNav();
        });
    }
    // go to top
    if (typeof sp_gotop === 'undefined') {
        sp_gotop = '';
    }

    if (sp_gotop) {
        // go to top
        $(window).scroll(function () {
            if ($(this).scrollTop() > 100) {
                $('.scrollup').fadeIn();
            } else {
                $('.scrollup').fadeOut(400);
            }
        });

        $('.scrollup').click(function () {
            $("html, body").animate({
                scrollTop: 0
            }, 600);
            return false;
        });
    } // has go to top

    // Preloader
    if (typeof sp_preloader === 'undefined') {
        sp_preloader = '';
    }

    if (sp_preloader) {
        $(window).on('load', function () {
            if ($('.sp-loader-with-logo').length > 0) {
                move();
            }
            setTimeout(function () {
                $('.sp-pre-loader').fadeOut();
            }, 1000);
        });
    } // has preloader
    //preloader Function
    function move() {
        var elem = document.getElementById("line-load");
        var width = 1;
        var id = setInterval(frame, 10);
        function frame() {
            if (width >= 100) {
                clearInterval(id);
            } else {
                width++;
                elem.style.width = width + '%';
            }
        }
    }
    // ************    END:: Helix 1.4 JS    ************** //
    // **************************************************** //

    // **************   START Mega SCRIPT   *************** //
    // **************************************************** //

    //mega menu
    $('.sp-megamenu-wrapper').parent().parent().css('position', 'static').parent().css('position', 'relative');
    $('.sp-menu-full').each(function () {
        $(this).parent().addClass('menu-justify');
    });

    // boxlayout
    if ($("body.layout-boxed").length > 0) {
        var windowWidth = $('#sp-header').parent().outerWidth();
        $("#sp-header").css({"max-width": windowWidth, "left": "auto"});
    }

    // **************   END:: Mega SCRIPT   *************** //
    // **************************************************** //

    // **************  START Others SCRIPT  *************** //
    // **************************************************** //

    //Tooltip
    $('[data-toggle="tooltip"]').tooltip();

    // Article Ajax voting
    $(document).on('click', '.sp-rating .star', function (event) {
        event.preventDefault();

        var data = {
            'action': 'voting',
            'user_rating': $(this).data('number'),
            'id': $(this).closest('.post_rating').attr('id')
        };

        var request = {
            'option': 'com_ajax',
            'plugin': 'helix3',
            'data': data,
            'format': 'json'
        };

        $.ajax({
            type: 'POST',
            data: request,
            beforeSend: function () {
                $('.post_rating .ajax-loader').show();
            },
            success: function (response) {
                var data = $.parseJSON(response.data);

                $('.post_rating .ajax-loader').hide();

                if (data.status == 'invalid') {
                    $('.post_rating .voting-result').text('You have already rated this entry!').fadeIn('fast');
                } else if (data.status == 'false') {
                    $('.post_rating .voting-result').text('Somethings wrong here, try again!').fadeIn('fast');
                } else if (data.status == 'true') {
                    var rate = data.action;
                    $('.voting-symbol').find('.star').each(function (i) {
                        if (i < rate) {
                            $(".star").eq(-(i + 1)).addClass('active');
                        }
                    });

                    $('.post_rating .voting-result').text('Thank You!').fadeIn('fast');
                }

            },
            error: function () {
                $('.post_rating .ajax-loader').hide();
                $('.post_rating .voting-result').text('Failed to rate, try again!').fadeIn('fast');
            }
        });
    });

    // **************  END:: Others SCRIPT  *************** //
    // **************************************************** //

});
