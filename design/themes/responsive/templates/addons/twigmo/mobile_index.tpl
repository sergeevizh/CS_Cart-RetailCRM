<!DOCTYPE html>
<html xmlns:ng="http://angularjs.org" lang="{$smarty.const.CART_LANGUAGE|lower}">
<head>
{if $twg_settings.companyName}
    <title>{if $twg_settings.home_page_title}{$twg_settings.home_page_title} - {/if}{$twg_settings.companyName}</title>
{else}
    <title>{$twg_settings.home_page_title}</title>
{/if}
<meta name="description" content="">
<meta name="HandheldFriendly" content="True">
<meta name="MobileOptimized" content="320">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="cleartype" content="on">
<meta content="Twigmo" name="description" />
<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0">
<meta name="robots" content="noindex">
{if $twg_state.appstore_app_id}
    <meta name="apple-itunes-app" content="app-id={$twg_state.appstore_app_id}">
{/if}

<base href="{$twg_settings.url.base}/"/>

<link rel="apple-touch-icon" href="{$urls.favicon}" />
<link rel="shortcut icon" href="{$urls.favicon}" />

{if $twg_state.theme_editor_mode}
    <meta http-equiv="cache-control" content="max-age=0" />
    <meta http-equiv="cache-control" content="no-cache" />
    <meta http-equiv="expires" content="0" />
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <meta http-equiv="pragma" content="no-cache" />
    <link rel="stylesheet" type="text/css" href="{$urls.preview_css}app.css?{$repo_revision}" data-theme="Y" />
    <link rel="stylesheet" type="text/css" href="{$urls.preview_css}custom.css?{$repo_revision}" data-theme="Y" />
{/if}

{if $twg_state.cordova_platform}
    <script type="text/javascript" src="{$urls.repo}cordova/{$twg_state.cordova_platform}/cordova.js?{$repo_revision}" async></script>
{/if}

{literal}
<style>#boot_loader{position: absolute; top: 50%; left: 50%; margin: -50px 0 0 -50px;}#splash-screen{top:0px;left:0px;right:0px;bottom:0px;position:fixed;background-color:white;opacity:1; z-index:999}</style>
{/literal}

{if $addons.google_analytics.status == "A" && !$do_not_use_google_template}
    {include file=$google_template}
{/if}
</head>

<body class="device-{$twg_state.device} browser-{$twg_state.browser}">
    <div ng-include="'/core/customer/index.html'" ng-controller="AppCtrl"></div>


    <div id="splash-screen">
        <canvas id="boot_loader" width="100" height="100"></canvas>
    </div>

    <script type="text/javascript">
        //<![CDATA[
        (function() {ldelim}
            var repoRevision = '{$repo_revision}';
            var rootUrl = '{$urls.repo}';
            var requestUrl = '{$twg_settings.url.host}{$twg_settings.url.index}{$twg_settings.url.dispatch}';
            var cacheRequest = false;
            {literal}
            var filesToLoad=[{path:rootUrl+"vendor.js"+"?"+repoRevision,id:"vendor.js"},{path:rootUrl+"{/literal}{if $twg_state.is_app_mode}twigmo-app.js{else}twigmo.js{/if}{literal}"+"?"+repoRevision,id:"twigmo.js",waitFor:"vendor.js"},{path:requestUrl+"&action=get_settings.js",dontCache:true},{path:rootUrl+"custom_js.twgjs"+"?"+repoRevision+"&as.js",id:"custom.js",waitFor:"twigmo.js"}]
            {/literal}

            {if $twg_state.theme_editor_mode}
            {literal}
            filesToLoad.push({path:rootUrl+"theme_editor.js"+"?"+repoRevision,waitFor:"twigmo.js"})
            {/literal}
            {else}
            {literal}
            filesToLoad.push({path:rootUrl+"app.css"+"?"+repoRevision,id:"app.css"});filesToLoad.push({path:rootUrl+"custom.css"+"?"+repoRevision,waitFor:"app.css"});
            {/literal}
            {/if}
            {if $twg_state.is_app_mode}
                {if $twg_state.theme_editor_mode}
                    {literal}filesToLoad.push({path:rootUrl+"customer_app.css"+"?"+repoRevision});{/literal}
                {else}
                    {literal}filesToLoad.push({path:rootUrl+"customer_app.css"+"?"+repoRevision,waitFor:"app.css"});{/literal}
                {/if}
            {/if}
            {if $twg_settings.cacheRequest}
            cacheRequest = {ldelim}{rdelim};
            {foreach from=$twg_settings.cacheRequest item=value key=key}
            cacheRequest['{$key}'] = '{$value}';
            {/foreach}
            {/if}
            {literal}

            if(cacheRequest){var url=requestUrl;for(var param in cacheRequest)url+="&"+param+"="+cacheRequest[param];filesToLoad.push({path:url+"&get_cache.js",dontCache:!0,id:"cache.js"})}var initProgressBar=function(e){var t=document.getElementById("boot_loader"),a=t.getContext("2d"),i=1,r=0,n=t.width,o=t.height;window.devicePixelRatio>1&&(t.width=n*window.devicePixelRatio,t.height=o*window.devicePixelRatio,t.style.width=n+"px",t.style.height=o+"px"),a.strokeStyle="rgb(152,152,152)",a.fillStyle="white",a.lineWidth=40;var d=function(t){var a=t/e*2;return t==e?a=1.4999:.5>a?a+=1.5:a-=.5,a*Math.PI},c=function(){var e=.05;return{start:d(i-1)+e,end:d(i)-e}},u=function(){var t=(new Date).getTime();if(r&&50>t-r&&.5>i/e)return void setTimeout(u,60);r=t;var n=c();a.beginPath(),a.arc(50,50,20,n.start,n.end),a.stroke(),a.closePath(),a.beginPath(),a.arc(50,50,30,0,2*Math.PI),a.fill(),a.closePath(),i++};return a.scale(window.devicePixelRatio,window.devicePixelRatio),u(),u},updateProgress=initProgressBar(filesToLoad.length+2),runApp=function(){return"undefined"==typeof angular?void window.setTimeout(runApp,100):void angular.element(document).ready(function(){updateProgress(),setTimeout(function(){var e=document.getElementById("splash-screen");e.parentNode.removeChild(e)},3),setTimeout(function(){angular.bootstrap(document,["app"])},1)})},onScriptLoaded=function(e,t,a,i,r){a.indexOf("/app.css?")>0&&!r&&(i.data=i.data.replace(/url\("([^/][^/].*?)"/g,'url("'+rootUrl+'$1"')),a.indexOf("get_cache.js")>0&&(window.twgCachedData={request:cacheRequest},i.data="window.twgCachedData.data = "+i.data+";"),updateProgress(),e==t&&runApp()};
            var BootUp=function(t,e){function a(){for(var e=0;e<t.length;e++){if(F)return;var a=t[e];L.push(a),m(a.path)}}function c(t){t&&(t.error&&(C=t.error),t.loaded&&(M=t.loaded),t.threads&&(E=t.threads),t.debug&&(I=t.debug),t.fresh&&(A=t.fresh))}function n(){c(e),T=t.length;try{j&&localStorage.getItem("cache")&&(w=JSON.parse(localStorage.getItem("cache")))}catch(n){localStorage.removeItem("cache")}a()}function o(t){for(var e=0;e<L.length;e++)if(L[e].path==t)return L[e];return null}function r(t){var e=o(t);return e?o(t).data:null}function l(t){if(w){for(var e=0;e<w.objects.length;e++)if(w.objects[e].path===t)return w.objects[e];return null}}function s(){for(var t=[],e=!1,a=0;a<L.length;a++)L[a].isExecuted&&t.push(L[a].id);for(var a=0;a<L.length;a++)L[a].data&&!L[a].isExecuted&&(L[a].waitFor&&-1==t.indexOf(L[a].waitFor)||(i(L[a]),L[a].isExecuted=!0,t.push(L[a].id),e=!0));e&&s()}function u(){s(),O===T&&h()}
                function i(t){var e=-1===t.path.indexOf(".js")?!1:!0,a=document.createElement(e?"script":"style");a.type="text/"+(e?"javascript":"css"),e?a.text=t.data:a.styleSheet?a.styleSheet.cssText=t.data:a.appendChild(document.createTextNode(t.data));var c=document.head||document.getElementsByTagName("head")[0];c.appendChild(a)}function h(){if(j){for(var t={objects:L},e=0;e<t.objects.length;e++)delete t.objects[e].callback,t.objects[e].dontCache&&t.objects.splice(e--,1);try{localStorage.cache=JSON.stringify(t)}catch(a){y("Couldn't cache objects this time")}}}function d(t){t.cached=!0;var e=o(t.path);e.data=t.data,callback=e.callback,y("from cache",t.path),O++,M&&M.call(this,O,T,t.path,e,!0),u()}function f(t,e){var a=o(t);a.data=e.responseText,O++,x--,a.callback&&a.callback.call(this,t),M&&M.call(this,O,T,t,a),u(),b()}function p(t,e){y("FAILED TO LOAD A FILE",e),C&&C.call(this),F=!0}
                function g(){return window.XMLHttpRequest?new XMLHttpRequest:new ActiveXObject("Microsoft.XMLHTTP")}function v(t){k.push(t)}function b(){if(k.length>0){var t=k.pop();m(t)}}function m(t){if(x>=E)return void v(t);if(!F){var e=l(t);if(e)return void d(e);x++;var a=g();a.onreadystatechange=function(){F||(4!=a.readyState||200!=a.status&&204!=a.status?4==a.readyState&&a.status>400&&a.status<600&&p(a,t):f(t,a))},a.open("GET",t,!0),a.send(null)}}function y(){I&&console&&console.log.apply(console,arguments)}var j=!1;try{localStorage&&(j=!0)}catch(S){}var x=0,E=8,T=0,O=0,w=null,L=[],k=[],F=!1,I=!1,A=!1,C=null,M=null;return n(),{getFile:r}};
            new BootUp(filesToLoad, {loaded: onScriptLoaded});
            {/literal}
        })();
        //]]>
    </script>

    {if $twg_settings.geolocation == 'Y'}
        <script type="text/javascript">
            //<![CDATA[
            {literal}
            var twgMapsCallback = function() {if (typeof(twg) != 'undefined' && twg.geo && twg.func && angular.element(document).injector()) {twg.func.publish('geo:apiLoaded');}};
            {/literal}
            //]]>
        </script>
        <script async type="text/javascript" src="//maps.google.com/maps/api/js?sensor=false&?v=3.7&language={$smarty.const.CART_LANGUAGE}&callback=twgMapsCallback"></script>
    {/if}
</body>
</html>
