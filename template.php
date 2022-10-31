<?php
  /**
   * The PHP Mini Gallery V1.3
   * (C) 2003 Richard "Shred" Koerber -- all rights reserved
   * http://www.shredzone.net/go/minigallery
   *
   * This is an example template. Feel free to modify it as you like.
   *
   * This software is free software; you can redistribute it and/or modify
   * it under the terms of the GNU General Public License as published by
   * the Free Software Foundation; either version 2 of the License, or
   * (at your option) any later version.
   *
   * This program is distributed in the hope that it will be useful,
   * but WITHOUT ANY WARRANTY; without even the implied warranty of
   * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   * GNU General Public License for more details.
   *
   * You should have received a copy of the GNU General Public License
   * along with this program; if not, write to the Free Software
   * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
   *
   * $Id: template.php,v 1.2 2003/12/07 14:14:20 shred Exp $
   */
?>
<html>
<head>
  <pmg:if page="index">
    <title>A gallery of <pmg:count/> folders and pictures in Gallery<pmg:path/></title>
  </pmg:if>
  <pmg:if page="picture">
    <title>Picture <pmg:current/>/<pmg:count/></title>
  </pmg:if>
  <style type="text/css"><!--
    body {
      background-color: #FFFFFF;
      font-family: arial,helvetica,sans-serif;
      font-size: 16px
    }

    a {
      text-decoration: none;
    }

    .tabindex {
      width: 100%;
    }

    .tiles {
      display: inline-block;
      width: 12.5%;
      height: calc(12.5vw * <pmg:ratio/>);
      overflow: hidden;
      font-size: 28pt;
      position: relative;
      padding: 0;
      margin: 0;
    }
    @media only screen and (max-width: 1200px) {
     .tiles {
        width: 20%;
        height: calc(20vw * <pmg:ratio/>);
      }
    }
    @media only screen and (max-width: 1000px) {
      .tiles {
        width: 25%;
        height: calc(25vw * <pmg:ratio/>);
        font-size: 32pt;
      }
    }

    .thumbimg {
      min-width: 105%;
      min-height: 105%;
      margin: 50%;
      transform: translate(-50%,-50%);
    }

    .picture {
      height: 97%;
      align-content: center;
      display: flex;
    }

    .nav {
      z-index: 2;
      position: fixed;
      top: 0;
      text-align: center;
      width: 100%;
      background: black;
      opacity: 0.75;
      right: 0;
    }

    .nav a {
      color: white;
    }

    .picimg {
      margin: auto;
      display: block;
      max-width: 100%;
      max-height: 100%;
      width: auto;
      height: auto;
    }

    .caption {
      z-index: 2;
      position: absolute;
      bottom: 0;
      text-align: center;
      width: 100%;
      background: black;
      opacity: 0.75;
      color: white;
      right: 0;
      font-weight: bold;
    }

    .tabindex .caption {
      height: 50%;
    }
    .picture .caption {
      font-size: 18px;
    }
    .caption .exif {
      display: none;
    }
    .caption:hover .exif {
      display: block;
    }
  //--></style>
</head>
<body>
  <pmg:if page="index">
    <pmg:index/>
  </pmg:if>

  <pmg:if page="picture">
    <div class="nav">
      <div style="float: right">
        <pmg:next>[ > ]&nbsp;</pmg:next>
        <pmg:last>[ >> ]</pmg:last>
      </div>
      <div style="float: left">
        <pmg:first>[ << ]</pmg:first>
        <pmg:prev>&nbsp;[ < ]</pmg:prev>
      </div>
      <pmg:toc>[ <pmg:path/> ]&nbsp;</pmg:toc>
    </div>
    <div class="picture">
      <pmg:image/>
      <pmg:caption/>
    </div>
    <style>body{height: 100%;overflow:hidden}</style>
  </pmg:if>
<script>
document.onkeydown = function(ev) {
   switch(ev.keyCode) {
      case 37:
         document.getElementsByClassName('picture')[0].style.opacity = .5;
         document.getElementById('prev').click();
         break;
      case 39:
         document.getElementsByClassName('picture')[0].style.opacity = .5;
         document.getElementById('next').click();
         break;
      case 38:
         document.getElementsByClassName('picture')[0].style.opacity = .5;
         document.getElementById('parent').click();
         break;
   }
}
var startX = null, startY = null;
window.addEventListener("touchstart",function(event){
   if(event.touches.length === 1){
      startX = event.touches.item(0).clientX;
      startY = event.touches.item(0).clientY;
   }else{
      startX = null;
      startY = null;
   }
});
window.addEventListener("touchend",function(event){
   var offset = 100;
   if(startX || startY){
      var endX = event.changedTouches.item(0).clientX;
      var endY = event.changedTouches.item(0).clientY;

      if(endY < startY - offset){
         document.getElementsByClassName('picture')[0].style.opacity = .5;
         document.getElementById('parent').click();
      } else if(endX > startX + offset){
         document.getElementsByClassName('picture')[0].style.opacity = .5;
         document.getElementById('prev').click();
      } else if(endX < startX - offset ){
         document.getElementsByClassName('picture')[0].style.opacity = .5;
         document.getElementById('next').click();
      }
   }
});
setTimeout(function () {
   var i = document.getElementById(document.location.hash.replace('#',''));
   (i == null ? null : i.scrollIntoView());
}, 100);
</script>
</body>
</html>
