<h1><img src="/openqrm/base/img/user.png"> {label} {name}</h1>
<div id="form">
<form action="{thisfile}" method="POST">
{form}
{user}
{lang}
{role}
{pass1}
{pass2}
{forename}
{lastname}
{gender}
{department}
{office}
{state}
{description}
{capabilities}
{submit}{cancel}
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
function wait() {
	document.getElementById('form').style.display = 'none';
	document.getElementById('wait').style.display = 'block';
	document.getElementById('cancel').style.display = 'none';
}
function cancel() {
	document.getElementById('form').style.display = 'none';
	document.getElementById('wait').style.display = 'none';
	document.getElementById('cancel').style.display = 'block';
}
</script>
