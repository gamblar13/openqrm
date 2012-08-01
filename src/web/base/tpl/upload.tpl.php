<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>openQRM Server</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<meta http-equiv="content-style-type" content="text/css">
<meta http-equiv="content-script-type" content="text/javascript">
<meta http-equiv="content-language" content="en">
<meta name="date" content="2000-01-10T22:19:28+0100">
<meta name="author" content="openQRM Enterprise GmbH">

<link rel="stylesheet" href="{baseurl}/css/htmlobject.css" type="text/css" media="all">
<link rel="stylesheet" href="{baseurl}/css/default.css" type="text/css" media="all">
<script src="{baseurl}/js/jquery/js/jquery-1.3.2.min.js" type="text/javascript"></script>
<script src="{baseurl}/js/jqClock.min.js" type="text/javascript"></script>
<script src="{baseurl}/js/helpers.js" type="text/javascript"></script>

<style type="text/css">
#page {
 	width: 980px;
}
#page #middle {
 	width: 978px;
}
#page #middle #content .middle {
	width: 757px;
}
.htmlobject_box .left {
    float: none;
    margin: 0;
    padding: 0 0 25px 0;
	line-height: 16px;
    width: auto;
}
.htmlobject_box .right {
    float: none;
}
.errormsg {
	margin: 10px 0 0 0;
	color: red;
}
.main h1{
color: white;
font-size: 16px;
font-weight: bold;
margin: 20px 0 0 0;
}
</style>

</head>
<body>

<div id="page">
	<div id="top">&#160;</div>
	<div id="middle">
		<div id="head">

			<input id="servertime" type="hidden" value="{timestamp}">
			<div class="logo">
				<img src="{baseurl}/img/logo.png">
				<div class="openqrmVersion"></div>
			</div>


			<div class="top">
				<div id="Watch"></div>
				<div class="floatbreaker">&#160;</div>
			</div>

			<div class="main"><h1>{label}</h1></div>


		</div>
		<div id="content">
			<div class="menu">&#160;</div>
			<div class="middle">

			<form action="{thisfile}" enctype="multipart/form-data" method="POST">
				<div style="text-align: center; width:380px; margin: 100px auto 200px auto; ">
				{upload}
				<br><br>
				{submit}
				</div>
			</form>

			</div>
			<div class="floatbreaker">&#160;</div>
		</div>
		<div id="footer">
			<div id="openqrm_enterprise_footer" style="float:right;text-align:left;"><a href="http://www.openqrm-enterprise.com/" style="text-decoration:none;" target="_BLANK">openQRM&nbsp;Enterprise&nbsp;-&nbsp;&copy;&nbsp;2012&nbsp;openQRM Enterprise GmbH</a></div>
			<div class="floatbreaker">&#160;</div>
		</div>
	</div>
	<div id="bottom">&#160;</div>
</div>

<script type="text/javascript">
$(document).ready(function(){
	servertime = parseFloat( $("#servertime").val() ) * 1000;
	$("#Watch").clock({"timestamp":servertime,"format":"24","calendar":"false"});
});
</script>
</body>
</html>
