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

    address {
      margin-top: 10px;
      border-top: 1px solid #000000;
      font-size: 80%;
      text-align: center;
    }

    .tabindex {
      width: 100%;
    }

    .tabindex div {
      display: inline-block;
      max-width: 240px;
      max-height: 240px;
      overflow: hidden;
    }

    .thumbimg {
      background-color: #000000;
      padding: 3px;
      margin: 50%;
      transform: translate(-50%,-50%);
    }

    .picture {
      background-color: #C0C0C0;
      text-align: center;
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
      padding: 0;
      margin: auto;
      margin-bottom: 0;
      width: auto;
      height: auto;
      max-width: 100%;
      max-height: 100%;
      display: block;
    }

    .caption {
      position: absolute;
      bottom: 0;
      text-align: center;
      width: 100%;
      background: black;
      opacity: 0.75;
      color: white;
      right: 0;
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
      <div class="caption"><pmg:caption/></div>
    </div>
  </pmg:if>
<script>
document.onkeydown = function(ev) {
	console.log("on key down event: ", ev.keyCode)
	switch(ev.keyCode) {
	case 37:
		// left key pressed
		let prev=document.getElementById('prev')
		prev.click();
		break;
	case 39:
		// right key pressed
		let next=document.getElementById('next')
		next.click();
		break;
	}
}
</script>
</body>
</html>
