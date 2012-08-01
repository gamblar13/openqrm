<noscript>
<div class="noscript">Error: JavaScript must be activated for this page</div>
</noscript>

{content}

<script type="text/javascript">
var resizeFrame = {
	set : function() {
		h = '';
		f = document.getElementById('MainFrame');
		if(f.contentDocument && f.contentDocument.body.offsetHeight != "undefined") {
			p = parseFloat(navigator.userAgent.substring(navigator.userAgent.indexOf("Firefox")).split("/")[1])>=0.1? 16 : 0
			h = f.contentDocument.body.offsetHeight+p;
		}
		if(f.Document && f.Document.body.offsetHeight != "undefined") {
			h = f.Document.body.offsetHeight;
		}
		f.height = h +20;
	},
	handlers : function() {
		f = document.getElementById('MainFrame');
		if (window.addEventListener) {
			f.addEventListener("load", resizeFrame.set, false);
		}
		else if (window.attachEvent) {
			f.attachEvent("onload", resizeFrame.set);
		}
		else {
			f.onload = resizeFrame.set;
		}
	}
};
if(document.getElementById('MainFrame')) {
	resizeFrame.handlers();
}
</script>
