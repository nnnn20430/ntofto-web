/*browser detection*/
var isOpera = !!window.opera || navigator.userAgent.indexOf(' OPR/') >= 0;
    // Opera 8.0+ (UA detection to detect Blink/v8-powered Opera)
var isFirefox = typeof InstallTrigger !== 'undefined';   // Firefox 1.0+
var isSafari = Object.prototype.toString.call(window.HTMLElement).indexOf('Constructor') > 0;
    // At least Safari 3+: "[object HTMLElementConstructor]"
var isChrome = !!window.chrome && !isOpera;              // Chrome 1+
var isIE = /*@cc_on!@*/false || !!document.documentMode; // At least IE6

if (isChrome){
 window.webkitRequestFileSystem(/*TEMPORARY*/PERSISTENT, (1024*1024)*10, function(fs){
   fs.root.getDirectory('/Downloads', {}, function(dirEntry) {

     dirEntry.removeRecursively(function() {
       console.log('Directory removed.');
     }, function(){});

   }, function(){});
  
 }, function(){});
}



function ab2str(buf) {
 var str = "";
 var ab = new Uint8Array(buf);
 var abLen = ab.length;
 var CHUNK_SIZE = Math.pow(2, 8);
 var offset, len, subab;
 for (offset = 0; offset < abLen; offset += CHUNK_SIZE) {
  len = Math.min(CHUNK_SIZE, abLen-offset);
  subab = ab.subarray(offset, offset+len);
  str += String.fromCharCode.apply(null, subab);
 }
 return str;
}

var str2ab = function(str) {
 /* var buf = new ArrayBuffer(str.length * 2); */
 var buf = new ArrayBuffer(str.length * 1);
 var bufView = new Uint8Array(buf);
 for (var i = 0; i < str.length; i++) {
  bufView[i] = str.charCodeAt(i);
 }
 return buf;
}

Element.prototype.remove = function() {
    this.parentElement.removeChild(this);
}
NodeList.prototype.remove = HTMLCollection.prototype.remove = function() {
    for(var i = 0, len = this.length; i < len; i++) {
        if(this[i] && this[i].parentElement) {
            this[i].parentElement.removeChild(this[i]);
        }
    }
}

function initiateSave(data){
 var ab = str2ab(data);
 window.URL = window.URL || window.webkitURL;
 var blob = new Blob([ab], {type: 'application/octet-stream'});
 var a = document.createElement('a');
    a.href = window.URL.createObjectURL(blob);
    a.download = requestedFileName;
    a.textContent = '';
    a.style = "display: none";
    document.body.appendChild(a);
    a.click();
    a.remove();
}

function initiateSavechrome(data){
 var a = document.createElement('a');
    a.href = data;
    a.download = requestedFileName;
    a.textContent = '';
    a.style = "display: none";
    document.body.appendChild(a);
    a.click();
    a.remove();
}

function deleteuserfile(name){
   name = name.replace("!ApostropheChar!", "'");
   var requestdata = '&Request=deletefile' + '&filename=' + name;
   AuthRequest(localStorage.getItem('username'), localStorage.getItem('loginSessionKey'), requestdata, function(){});
}


function errorHandler(error) {
    console.log(error)
}

window.fscreatefile = function(fs, name, callback) {
    fs.root.getFile('/Downloads/' + name, {create: true}, function(fileEntry) {

        // fileEntry.isFile === true
        // fileEntry.name == 'log.txt'
        // fileEntry.fullPath == '/log.txt'
        if (fileEntry.isFile) {
            window.fsFileCreated = true;
        }
        if (callback != null) {
            callback();
        }

    }, errorHandler);
}
window.fsappendtofile = function(fs, name, data, callback) {
    fs.root.getFile('/Downloads/' + name, {create: false}, function(fileEntry) {

        // Create a FileWriter object for our FileEntry (log.txt).
        fileEntry.createWriter(function(fileWriter) {

            fileWriter.seek(fileWriter.length); // Start write position at EOF.
            var ab = str2ab(data);

            // Create a new Blob and write it to file.
            var blob = new Blob([ab], {type: 'application/octet-stream'});

            fileWriter.onwriteend = function(e) {
                console.log('Write completed.');

                if (callback != null) {
                    callback();
                }

            };

            fileWriter.onerror = function(e) {
                console.log('Write failed: ' + e.toString());
                window.FaliureEncounteredWhileDownloading = true;
		downloadStoped();
            };

            fileWriter.write(blob);
        }, errorHandler);

    }, errorHandler);
}
window.fsgetfileurl = function(fs, name, callback) {
    fs.root.getFile('/Downloads/' + name, {}, function(fileEntry) {

        var fileurl = fileEntry.toURL();

        if (callback != null) {
            callback(fileurl);
        }

    }, errorHandler);
}
window.fsremovefile = function(fs, name, callback) {
    fs.root.getFile('/Downloads/' + name, {create: false}, function(fileEntry) {

        fileEntry.remove(function() {
            console.log('File removed.');
            if (callback != null) {
                callback();
            }
        }, errorHandler);

    }, errorHandler);
}
window.fstryremovefile = function(fs, name, callback) {
    fs.root.getFile('/Downloads/' + name, {create: false}, function(fileEntry) {

        fileEntry.remove(function() {
            console.log('File removed.');
            if (callback != null) {
                callback(fs);
            }
        }, errorHandler);

    }, function(error) {
        if (callback != null) {
            callback(fs);
        }
        ;
    });
}
window.fstryremovedownloaddir = function(fs, callback) {
    fs.root.getDirectory('/Downloads', {}, function(dirEntry) {

        dirEntry.removeRecursively(function() {
            console.log('Directory removed.');
            if (callback != null) {
                callback(fs);
            }
        }, function() {
            callback(fs);
        });

    }, function() {
        callback(fs);
    });

}

function downloadStoped(){
  clearInterval(window.fileStatusInterval);
}



function AuthDownloadRequestFormold(username, key, datatosend, callback, headercallback){
 getPubkeyandMakeNew();
 var serverresponse = '';
 
 var formData = new FormData();
 formData.append('AuthenticatedRequest', 'true');
 formData.append('username', username);
 formData.append("loginSessionKey", compute('encrypt', key, window.server_public_key)); 
 for (var prop in datatosend) {
  if (datatosend.hasOwnProperty(prop)) { 
   // or if (Object.prototype.hasOwnProperty.call(obj,prop)) for safety...
   formData.append(prop, datatosend[prop]);
  }
 }
 

 var request = new XMLHttpRequest();
 request.open("POST", "./compute.php", true);
 request.overrideMimeType('text\/plain; charset=x-user-defined');
// request.setRequestHeader('connection', '');
// request.upload.addEventListener('progress', uploadProgress, false);
 request.addEventListener('load', function(){returnReponse(request.responseText);}, false);
 request.addEventListener('error', function(){returnReponse('error');}, false);
 request.addEventListener('abort', function(){returnReponse('abort');}, false);
 request.send(formData);
 
 function returnReponse(response){
  serverresponse = response;
  if(headercallback != null){
   headercallback(request.getResponseHeader('Content-Disposition'));
  }
  if(callback != null){
   callback(serverresponse);
  }
 }
}

function AuthDownloadRequestForm(username, key, filename, callback, headercallback){
  var dataRetrived = "";
  var returnedFileName = "";
  var filesize = 0;
  var calcFilename=filename.split("/").pop();
  window.fileStatusInterval = setInterval(function(){document.getElementById('userfilesstatusdisplay:'+calcFilename).innerHTML = 'Downloading';}, 10);
  AuthRequestForm(username, key, {'Request': 'RequestDownloadFileSize', 'file': filename}, function(data){filesize=data;continueAuthDownloadRequestForm()});
  function continueAuthDownloadRequestForm(){
   var chunksize=10*(1024*1024)
   var requestParts=Math.ceil(filesize/chunksize);
   var partsGot = 0;
   window.fsFileCreated = false;
   function getFileParts(){
     if (partsGot >= requestParts){donedownloading();}
     else
     {
     AuthRequestForm(username, key, {'Request': 'RequestDownloadFilePart', 'file': filename, 'part': partsGot+1}, function(data){partsGot++;dataRetrived=dataRetrived+data;getFileParts()}, function(header){returnedFileName=header['Content-Disposition'].substr(21);});
     }
   }
   function getFilePartsChromeinit(){
     function onInitFs(fs){window.fstryremovedownloaddir(fs, function(fs){fs.root.getDirectory('Downloads', {create: true}, function(dirEntry) {getFilePartsChrome(fs);}, errorHandler);});};
     navigator.webkitPersistentStorage.requestQuota(filesize+(1024*1024)*10, function(grantedBytes) {
       console.log(grantedBytes);
       window.webkitRequestFileSystem(/*TEMPORARY*/PERSISTENT, grantedBytes, onInitFs, errorHandler);
     }, function(e) {
       console.log('Error', e);
     });
   }
   function getFilePartsChrome(filesystem){
     if (partsGot >= requestParts && window.FaliureEncounteredWhileDownloading == false){donedownloadingchrome(filesystem);}
     else if (window.FaliureEncounteredWhileDownloading == true){console.log('Something went wrong.');window.fsremovefile(filesystem, returnedFileName);}
     else
     {
     AuthRequestForm(username, key, {'Request': 'RequestDownloadFilePart', 'file': filename, 'part': partsGot+1}, function(data){if(window.fsFileCreated){partsGot++;fsappendtofile(filesystem, returnedFileName, data, function(){getFilePartsChrome(filesystem);}/*getFilePartsChrome(filesystem)*/);}else{window.resumedownloadafterfsfilecreated = function(){partsGot++;fsappendtofile(filesystem, returnedFileName, data, function(){getFilePartsChrome(filesystem);}/*getFilePartsChrome(filesystem)*/);}}}, function(header){returnedFileName=header['Content-Disposition'].substr(21);if(!window.fsFileCreated){window.fstryremovefile(filesystem, returnedFileName, function(filesystem){window.fscreatefile(filesystem, returnedFileName, function(){window.resumedownloadafterfsfilecreated()});});}});/*THE ONE LINE MESS!*/
     }
   }
   if(isChrome){getFilePartsChromeinit();}else{getFileParts();}
  }
  function donedownloading(){
    headercallback(returnedFileName);
    callback(dataRetrived);
    dataRetrived = "";
  }
  function donedownloadingchrome(filesystem){
    headercallback(returnedFileName);
    window.fsgetfileurl(filesystem, returnedFileName, function(fileurl){callback(fileurl);setTimeout(function(){window.fsremovefile(filesystem, returnedFileName);}, 1000)});
  }
}


function initiateDownload(key){
 var iframe = document.createElement('iframe');
    iframe.src = "./download.php?get="+key;
    iframe.height = 0;
    iframe.width = 0;
    iframe.style = "display: none";
    document.body.appendChild(iframe);
}


var requestedFileName = '';
function AuthGetFile(file){
  file = file.replace("!ApostropheChar!", "'");
  window.FaliureEncounteredWhileDownloading = false;
  AuthRequestForm(localStorage.getItem('username'), localStorage.getItem('loginSessionKey'), {Request: "RequestDownloadFileKey", file: ""+file}, initiateDownload);
  //AuthDownloadRequestForm(localStorage.getItem('username'), localStorage.getItem('loginSessionKey'), file, function(data){downloadStoped();if(isChrome){initiateSavechrome(data);}else{initiateSave(data);}}, function(header){requestedFileName = header;});
}








function CreateIndexedDB(name, storename){
const dbName = name;

var request = indexedDB.open(dbName, 4);

request.onerror = function(event) {
  // Handle errors.
};
request.onupgradeneeded = function(event) {
  db = event.target.result;
  var objectStore = db.createObjectStore(storename, { keyPath: 'key' });
  objectStore.transaction.oncomplete = function(event) {
    db.close();
  }
};
}

function WriteToIndexDB(name, storename, key, data){
var request = indexedDB.open(name, 4);

request.onerror = function(event) {
  // Handle errors.
};

request.onsuccess = function(event) {
  var db = event.target.result;
    var customerObjectStore = db.transaction(storename, "readwrite").objectStore(storename);
    console.log({ key: key, value: data });
    customerObjectStore.add({ key: key, value: data });
    db.close();
  }
};


function ReadIndexdDB(name, storename, key, callback){
var storeValue='';
var request = indexedDB.open(name, 4);

request.onsuccess = function(event) {
  var db = event.target.result;
  databaseopend(db);
  db.close();
};

function databaseopend(db){
 var transaction = db.transaction([storename]);
 var objectStore = transaction.objectStore(storename);
 var request = objectStore.get(key);
 request.onerror = function(event) {
   // Handle errors!
 };
 request.onsuccess = function(event) {
   // Do something with the request.result!
   var requestedValue="value";
   storeValue=request.result[requestedValue];
   callback(storeValue);
 };
}
}
