<h1><img src="/openqrm/base/img/user.png"> {label}</h1>

<div id="form">
<br>{new}<br><br>
<form action="{thisfile}" method="POST">
{form}
{table}
</form>
</div>

<table id="wait" style="display:none; border:0px none;">
<tr>
	<td style="vertical-align:middle;border:0px none;">{wait}</td>
	<td style="vertical-align:middle;border:0px none;"><img src="/openqrm/base/img/loading.gif"></td>
</tr>
</table>

<table id="cancel" style="display:none; border:0px none;">
<tr>
	<td style="vertical-align:middle;border:0px none;">{canceled}</td>
	<td style="vertical-align:middle;border:0px none;"><img src="/openqrm/base/img/loading.gif"></td>
</tr>
</table>

<script type="text/javascript">
button = document.getElementsByName('ut[action]');
if(button[0]) {
	for(i=0; i<button.length; i++) {
		button[i].onclick = function() { wait(); }
	}
}
tmp = document.getElementById('tab_{prefix_tab}0');
if(tmp) {
	a = tmp.getElementsByTagName('a')[0];
	a.onclick = function() { wait(); };
}
tmp = document.getElementById('tab_{prefix_tab}1');
if(tmp) {
	a = tmp.getElementsByTagName('a')[0];
	a.onclick = function() { wait(); };
}
$('.pageturn_head a').click(function() { wait(); });
$('.pageturn_bottom a').click(function() { wait(); });
$('.actiontable input').click(function() { wait(); });
function wait() {
	document.getElementById('form').style.display = 'none';
	document.getElementById('wait').style.display = 'block';
}
</script>
