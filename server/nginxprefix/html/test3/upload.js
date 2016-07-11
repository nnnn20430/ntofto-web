var iPreviousBytesLoaded = 0;
var iAverageSpeed = 0;
var list = [];
var filecount = 0;
document.getElementById('uploadfilebutton').onclick = uploadinit;

function uploadinit(){
 var filelist = document.getElementById('upload_file').files;
 for (var i = 0; i < filelist.length && i < 5; i++) {
  filecount++;
  list.push(filelist[i]);
 }
 upload();
}

function bytesToSize(bytes) {
    var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    if (bytes == 0) return 'n/a';
    var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
    return (bytes / Math.pow(1024, i)).toFixed(1) + ' ' + sizes[i];
};


function uploadProgress(e) { // upload process in progress
    if (e.lengthComputable) {
        iBytesUploaded = e.loaded;
        iBytesTotal = e.total;
        var iPercentComplete = Math.round(e.loaded * 100 / e.total);
        var iBytesTransfered = bytesToSize(iBytesUploaded);

        document.getElementById('progress').innerHTML = 'Percentage complete: ' + iPercentComplete.toString() + '%';
        document.getElementById('b_transfered').innerHTML = 'Transfered: ' + iBytesTransfered;
        if (iPercentComplete == 100) {
            var oUploadResponse = document.getElementById('progress');
            // do nothing
        }
    } else {
        document.getElementById('progress').innerHTML = 'unable to compute';
    }
}

function doInnerUpdates() { // we will use this function to display upload speed
    var iCB = iBytesUploaded;
    var iDiff = iCB - iPreviousBytesLoaded;

    // if nothing new loaded - exit
    if (iDiff == 0)
        return;

    iPreviousBytesLoaded = iCB;
    //iDiff = iDiff * 2;
    setTimeout(function(){ iAverageSpeed=iDiff; }, 3000);
    iAverageSpeed = 0.005 * iDiff + (1-0.005) * iAverageSpeed;
    var iBytesRem = iBytesTotal - iPreviousBytesLoaded;
    var secondsRemaining = iBytesRem / Math.round(iAverageSpeed);

    // update speed info
    var iSpeed = iDiff + 'B/s';
    if (iDiff > 1024 * 1024) {
        iSpeed = (Math.round(iDiff * 100/(1024*1024))/100).toString() + 'MB/s';
    } else if (iDiff > 1024) {
        iSpeed =  (Math.round(iDiff * 100/1024)/100).toString() + 'KB/s';
    }

    document.getElementById('speed').innerHTML = 'Speed: ' + iSpeed;
    document.getElementById('remaining').innerHTML = 'Time remaining: ' + secondsToTime(secondsRemaining);   
}

function secondsToTime(secs) { // we will use this function to convert seconds in normal time format
    var hr = Math.floor(secs / 3600);
    var min = Math.floor((secs - (hr * 3600))/60);
    var sec = Math.floor(secs - (hr * 3600) -  (min * 60));

    if (hr < 10) {hr = "0" + hr; }
    if (min < 10) {min = "0" + min;}
    if (sec < 10) {sec = "0" + sec;}
    if (hr) {hr = "00";}
    return hr + ':' + min + ':' + sec;
};

function uploadFinish(){    
 clearInterval(oTimer);
 document.getElementById('speed').innerHTML = '';
 document.getElementById('remaining').innerHTML = '';
 document.getElementById('b_transfered').innerHTML = '';
 document.getElementById('progress').innerHTML = '<h4>Done</h4>';
 if (filecount > 0){
  upload();
 }
}

function upload(){
 filecount = filecount - 1;
 file = list.shift();
 
 UploadRequestForm({'file': file}, function (response){AuthRequestForm(localStorage.getItem('username'), localStorage.getItem('loginSessionKey'), {'Request': 'RequestValidateUpload', 'UploadKey': response})});
}

function UploadRequestForm(datatosend, callback, headercallback){/* datatosend is a object-array to set it use {'key1': 'value1', 'key2': 'value2'} */
 var serverresponse = '';
 
 var formData = new FormData();

 for (var prop in datatosend) {
  if (datatosend.hasOwnProperty(prop)) { 
   // or if (Object.prototype.hasOwnProperty.call(obj,prop)) for safety...
   formData.append(prop, datatosend[prop]);
  }
 }
 

 var request = new XMLHttpRequest();
 request.open("POST", "./upload.php");
 request.upload.addEventListener('progress', uploadProgress, false);
 request.addEventListener('load', function(){returnReponse(request);uploadFinish()}, false);
 request.addEventListener('error', function(){returnReponse('error');}, false);
 request.addEventListener('abort', function(){returnReponse('abort');}, false);
 request.send(formData);
 oTimer = setInterval(doInnerUpdates, 300);

 function returnReponse(response){
  serverresponse = response.responseText;
  if(headercallback != null){
   headercallback(headerStringToObject(response.getAllResponseHeaders()));
   /*returns object to get value of header you want just do obj['header name']*/
  }
  if(callback != null){
   callback(serverresponse);
  }
 }
}
