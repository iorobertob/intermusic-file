// Stop all the audio from tabs upon clicking on the tabs, plus make selected tab's text bold. 
function selectedTabMedia(btn) {

	for(i=0; i<document.getElementsByTagName('video').length; i++) document.getElementsByTagName('video')[i].pause();
 	for(i=0; i<document.getElementsByTagName('audio').length; i++) document.getElementsByTagName('audio')[i].pause();

 	var tabs =  document.getElementsByClassName('tabButtons');

 	for(i=0; i<tabs.length; i++) {

 		if (btn.parentNode.parentNode.parentNode.id == tabs[i].parentNode.parentNode.parentNode.id){
 			tabs[i].style.fontWeight = '300';
 		}
 	}
 	btn.style.fontWeight =  '700';
}


// Bolden characters of selected tab, lighten those of all other tabs within the same block, by parent nodes' id
function selectedTab(btn) {

 	var tabs =  document.getElementsByClassName('tabButtons');

 	for(i=0; i<tabs.length; i++) {

 		if (btn.parentNode.parentNode.parentNode.id == tabs[i].parentNode.parentNode.parentNode.id){
 			tabs[i].style.fontWeight = '300';
 		}
 		
 	}
 	btn.style.fontWeight =  '700';
}

function resizeIframe(obj) {
	// var pages = obj.contentWindow.document.querySelectorAll('.page').length;
    obj.style.height = obj.contentWindow.document.documentElement.scrollHeight + 'px';
  }


// (document).ready(function() {
// document.addEventListener("DOMContentLoaded", function(event) { 
//     var videos = document.getElementsByTagName('video');
//     var audios = document.getElementsByTagName('audio')
//     for(i=0; i<videos.length; i++) {
//     	videos[i].bind('contextmenu',function() { return false; });
//     }

//     for(i=0; i<daudios.length; i++){
//     	audios[i].bind('contextmenu',function() { return false; });
//     } 
// });	