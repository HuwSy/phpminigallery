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
    <title>A gallery of <pmg:count/> pictures</title>
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

      .tabindex > div {
        display: inline-block;
        width: 12.5%;
        height: calc(12.5vw * <ratio>);
        overflow: hidden;
        font-size: 28pt;
        position: relative;
        padding: 0;
        margin: 0;
      }
      @media only screen and (max-width: 1200px) {
       .tabindex > div {
        width: 20%;
        height: calc(20vw * <ratio>);
       }
      }
      @media only screen and (max-width: 1000px) {
       .tabindex > div {
        width: 25%;
        height: calc(25vw * <ratio>);
        font-size: 32pt;
       }
      }

    .thumbimg {
      min-width: 103%;
      min-height: 103%;
      margin: 50%;
      transform: translate(-50%,-50%);
    }

    .picture {
      background-color: #C0C0C0;
      height: 100%;
    }

    .nav {
      z-index: 2;
      position: fixed;
      top: 0;
      text-align: right;
      width: 100%;
      background: black;
      opacity: 0.75;
      right: 0;
      padding-right: 10px;
    }

      .nav a {
        color: white;
      }

    .picimg {
      background-color: #000000;
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
      min-height: 25px;
    }
    @media only screen and (max-width: 1000px) {
      .caption {
        min-height: 50px;
      }
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
    <div class="Nav">
      <pmg:root>[ Parent ]&nbsp;</pmg:root>
      <pmg:first>[ Start... ]</pmg:first>
    </div>
    <pmg:index/>
  </pmg:if>

  <pmg:if page="picture">
    <div class="nav">
      <pmg:root>[ Parent ]&nbsp;</pmg:root>
      <pmg:toc>[ Folder ]&nbsp;</pmg:toc>
      <pmg:first>[ First ]&nbsp;</pmg:first>
      <pmg:prev>[ Previous ]&nbsp;</pmg:prev>
      <pmg:next>[ Next ]&nbsp;</pmg:next>
      <pmg:last>[ Last ]</pmg:last>
    </div>
    <div class="picture">
      <pmg:image/>
      <pmg:caption/>
    </div>
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
