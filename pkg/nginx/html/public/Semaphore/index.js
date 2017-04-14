var sRedLight;
var sYellowLight;
var sGreenLight;
var sNightMode;

var sRedTransAfter = 5000;
var sGreenAfter = 2000;
var sGreenTransAfter = 4000;
var sGreenTransBlinks = 3;
var sGreenTransInterval = 500;

var sNightModeInterval = 500;

function sLight(color, state) {
	switch (color) {
		case 'red':
			if (state)
				sRedLight.style.backgroundColor="#f00";
			else
				sRedLight.style.backgroundColor="#000";
			break;
		case 'yellow':
			if (state)
				sYellowLight.style.backgroundColor="#ff0";
			else
				sYellowLight.style.backgroundColor="#000";
			break;
		case 'green':
			if (state)
				sGreenLight.style.backgroundColor="#0f0";
			else
				sGreenLight.style.backgroundColor="#000";
			break;
	}
}

function sReset() {
	sLight('red', false);
	sLight('yellow', false);
	sLight('green', false);
}

function sTimeoutW(func, timeout) {
	setTimeout(func, timeout);
	return timeout;
}

function sLoop() {
	var lT = 0;
	var bC = 0;
	
	sReset();
	
	if (!sNightMode) {
		sLight('red', true);
		lT=sTimeoutW(function() {
			sLight('yellow', true);
		}, lT+sRedTransAfter);
		
		lT=sTimeoutW(function() {
			sLight('red', false);
			sLight('yellow', false);
			sLight('green', true);
		}, lT+sGreenAfter);
		
		lT=sTimeoutW(function() {
			sLight('green', false);
		}, lT+sGreenTransAfter);
		
		while (true) {
			if (bC<sGreenTransBlinks) {
				lT=sTimeoutW(function() {
					sLight('green', true);
				}, lT+sGreenTransInterval);
				
				lT=sTimeoutW(function() {
					sLight('green', false);
				}, lT+sGreenTransInterval);
			} else {
				lT=sTimeoutW(function() {
					sLoop();
				}, lT+sGreenTransInterval);
				break;
			}
			bC++
		}
	} else {
		sLight('yellow', true);
		
		lT=sTimeoutW(function() {
			sLight('yellow', false);
		}, lT+sNightModeInterval);
		
		lT=sTimeoutW(function() {
			sLoop();
		}, lT+sNightModeInterval);
	}
}

function main() {
	var nmToggleButton = document.getElementById("nmodetoggle");
	var nmIndicator = document.getElementById("nightmodeindicator");
	
	sRedLight = document.getElementById("red");
	sYellowLight = document.getElementById("yellow");
	sGreenLight = document.getElementById("green");
	sNightMode = false;
	
	nmToggleButton.addEventListener("click",function(){
		sNightMode = !sNightMode;
		console.log('NightMode: '+sNightMode); 
		if (sNightMode) {
			nmIndicator.innerHTML = "Night mode on";
		} else {
			nmIndicator.innerHTML = "Night mode off";
		}
	})
	
	//init main loop;
	sLoop();
}
