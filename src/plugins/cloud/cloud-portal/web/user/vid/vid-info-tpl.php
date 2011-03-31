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
<title>{cloud_object_type} {cloud_object}</title>

<link type="text/css" href="../../user/js/development-bundle/themes/smoothness/ui.all.css" rel="stylesheet" />
<link type="text/css" href="../../css/jquery.css" rel="stylesheet" />
<link type="text/css" href="../../css/mycloud.css" rel="stylesheet" />
<link type="text/css" href="vid.css" rel="stylesheet" />

<script type="text/javascript" src="../js/js/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="../js/js/jquery-ui-1.7.1.custom.min.js"></script>
<script type="text/javascript" src="../../js/drag.js"></script>

</head>

<body>

<style type="text/css">
#closewin {
	text-align: right;
}
</style>

<script language="JavaScript" type="text/javascript">

function runaction(ca_cmd, ca_id) {
	var action_response = $.ajax({
		 url: 'vid.php?action=' + ca_cmd + '&ca_id=' +ca_id,
		 type: "GET",
		 cache: false,
		 async: false,
		 success: function (html) {
			 if (html==1) {
				alert('Ajax-error in runaction');

			 }
		 }
	 }).responseText;
	 alert(action_response);
}


function doautorefresh() {
	if (document.autorefresh.autorefreshcheckbox.checked) {
		window.location.reload();
	}
	setTimeout("doautorefresh()", 5000);
}

setTimeout("doautorefresh()", 5000);

</script>

<div id="vidinfobox">
	{object_logo}
	<br>
	<br>
	{object_table}
	<br>

	<div id="closewin">
		<form name="autorefresh">
		<input name="autorefreshcheckbox" type="checkbox" value="1" checked> <small>Auto-Refresh /
		<a href="javascript:window.close();">Close Window</small></a>
		</form>

	</div>

</div>

<br/>




</body>
</html>
