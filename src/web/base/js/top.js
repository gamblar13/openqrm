function init() {
get_events();
js_events = window.setInterval("get_events()", 1500);	
}


function get_events() {
xmlHttp = null;
xmlHttp = new Request();

if (xmlHttp) {
    xmlHttp.open("GET", "http://localhost/openqrm/base/server/event/event-xmlhttprequest.php?tmp="+Math.random(), true);
    xmlHttp.onreadystatechange = function () {
        if (xmlHttp.readyState == 4) {
		values = xmlHttp.responseText.split(',');
		document.getElementById('events_critical').innerHTML = values[0];
		document.getElementById('events_total').innerHTML = values[1];
        }
    };
    xmlHttp.send(null);
}




} 
