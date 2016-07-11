
function correctMain() {
	var divMain = document.getElementById('main');
	var divMainHeight = divMain.clientHeight;
	var divMainWidth = divMain.clientWidth;
	divMain.style.left = "-"+divMainWidth/2+"px";
	divMain.style.top = "-"+divMainHeight/2+"px";
}
