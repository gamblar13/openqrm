<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<!--
/*
  This file is part of openQRM.

	openQRM is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License version 2
	as published by the Free Software Foundation.

	openQRM is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with openQRM.  If not, see <http://www.gnu.org/licenses/>.

	Copyright 2010, Matthias Rechenburg <matt@openqrm.com>
*/
-->

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Upload custom icon</title>

<link type="text/css" href="js/development-bundle/themes/smoothness/ui.all.css" rel="stylesheet" />
<link type="text/css" href="../css/jquery.css" rel="stylesheet" />
<link type="text/css" rel="stylesheet" href="../css/calendar.css" />
<link rel="stylesheet" type="text/css" href="../css/mycloud.css" />

<script type="text/javascript" src="js/js/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="js/js/jquery-ui-1.7.1.custom.min.js"></script>
<script src="../js/jquery.MultiFile.js" type="text/javascript" language="javascript"></script>

</head>

<body>

<style type="text/css">
#iconuploadbox {
	position: absolute;
	left: 10px;
	top: 10px;
	width: 350px;
	height: 130px;
	padding: 10px;
	border: solid 1px #808080;
}


</style>


<script language="JavaScript" type="text/javascript">
	window.onunload=function() {
		window.opener.doRefresh()
	}

	window.onload=function() {
		{window_close_trigger}
	}

</script>


<div id="iconuploadbox">
	<a href="javascript:window.close();"><small>Close Window</small></a>
	<h2>Upload a custom icon for Cloud {cloud_object} </h2>
	<small>(48px x 48px, png, gif, jpg)</small>
	<form action="" method="post" enctype="multipart/form-data">
	<input type="file" name="pic[]" class="multi" />
	<input type="submit" name="upload" value="Upload" />
	</form>
</div>


<br/>


</body>

</html>
