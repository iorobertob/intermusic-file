function selectedTab(btn) {
	alert("tests");
	for(i=0; i<document.getElementsByTagName('video').length; i++) document.getElementsByTagName('video')[i].pause();
 	for(i=0; i<document.getElementsByTagName('audio').length; i++) document.getElementsByTagName('audio')[i].pause();

 	var tabs =  document.getElementsByClassName('tabButtons');

 	for(i=0; i<tabs.length; i++) style.fontWeight = '300';

 	btn.style.fontWeight =  '700';
}