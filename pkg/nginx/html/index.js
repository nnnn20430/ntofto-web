
window.onload = function () {
	// get "center-content" inside "center" div
	// get half width/height of the element to get it's center
	// offset position by that much to make center of element be center of "center" div
	var divCContent = document.getElementById('center-content');
	if (document.body.style.transform === undefined) {
		divCContent.style.left = "-"+(divCContent.clientWidth/2)+"px";
		divCContent.style.top = "-"+(divCContent.clientHeight/2)+"px";
	}
}
