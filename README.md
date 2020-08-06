### Important: 
 - The name of the folder of this plugin should be named 'file'.

 - "Filter Tabs" filter needs to be installed in Moodle so that, if more than one file is added, they will be displayed arranged in "tabs". (https://moodle.org/plugins/filter_tabs)




File Block for Moodle
=====================

This is a File block for Moodle. It allows you to embed a single file from Moodle storage as a block in your page.

The block currently supports embedding audio, video, image and PDF files. For files of other types, a link to the file will be shown.

PDF preview is implemented via Mozilla's PDF.js viewer, a full build of which is supplied in the pdfjs subdirectory. Please note that PDF.js is licensed under the terms of Apache license, a copy of which is supplied in the pdfjs/LICENSE file.

This is part of a mini library of intermusic plugins to manage content specific to this platform solution, but can be used independently, and it was meant to be used with Poster plugin, where it works as intended. 


## How to install
The install process is the same as for other Moodle plugins, by simply including the project folder into the 'blocks' directory in the moodle files system. 
## How to use
To use, for example inside a Poster activity, add a block of the "File" type and in its settings's file manager (filepicker) add the fiels you want to display. 

 - The tabs will display a name for the file in a format that is specially designed for the Intermusic project, but could easily be modified, and it works as it is anyway: Filenames should be separated in 3 sections, split by an underscore ' _ '. The first part corresponds to the collection ID, second to the file name with more metadata and the third contains a special type description as per Intermusic's requirements. 

 - The tabs display will take the 3rd filename section (type) and try to match its first 5 letters with one of the following strings:
 			"SCORE",
            "TRANS",
            "WORD2",
            "VIDEO",
            "AUDIO",
            "RECIT"
    If it finds a match it will use such string as the label for the tab, otherwise it will use the raw filname. 


- To customise the matching strings or how the labeling works, the block_file.php script can be edited at lines 66 to 101. 




## TODOs:
- Add general settings to easily configure the strings to match in the filenames to display in the tabs, from the plugin native settings page in the Moodle page. 