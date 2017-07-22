window.addEventListener('load', function load(){

	var el = document.getElementById('jmrNotice');

	if ( undefined !== el ) {
		var s = document.body.firstChild;
		s.parentNode.insertBefore(el, s);
		el.style.display = "block";
	}

}, false);