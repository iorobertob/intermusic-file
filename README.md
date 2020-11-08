### Important: 
 - The name of the folder of this plugin should be named 'file'.

 - "Filter Tabs" filter needs to be installed in Moodle so that if more than one file is added, they will be displayed arranged in "tabs". (https://moodle.org/plugins/filter_tabs)




File Block for Moodle
=====================

This is a File block for Moodle. It allows to embed one or many file block in your page. If more than one file are added they will be arranged in tabs, and the order of the tabs and their title can be set by defaults depending on the file naming format described below.

The block currently supports embedding audio, video, image and PDF files. For files of other types, a link to the file will be shown.

This is part of a mini library of intermusic plugins to manage content specific to this platform solution, but can be used independently, and it was meant to be used with Media Poster plugin, for which it was built. 


## Installation 
Standard moodle plugin process, though the admin pages or by copying this repository in a folder named **file** inside **blocks/**

## How to use
To use, for example inside a Media Poster activity, add a **File** block  and in its settings's file manager (filepicker) add the fiels you want to display. 

If more than one file are selected, they will be sorted in tabas corresponding to the configurable strings. For this, the file must contain such string in the last part of an *underscore* divided filename consisting of 3 sections. i.e.  **three_filename_sections.extension**

If the third part of such filename matches the strings in the configuration the block, the name of the file will be substituted with such string. Otherwise, the full filename is displaye as the title of the tab. 

 - The tabs will display a default name value for the file in a format that is specially designed for the Intermusic project, but could easily be modified.

## Filename convention
 Filenames should be separated in 3 sections, split by an underscore ' _ '. 

 - The tabs display will take the 3rd filename section (type) and try to match it with one of the following strings (configurable):
 			"SCORE",
            "TRANS",
            "WORD",
            "VIDEO",
            "AUDIO",
            "RECIT",
            "LANG"
    If it finds a match it will use such string as the label for the tab, otherwise it will use the raw filname. 


- To customise the matching strings or how the labeling works, the block_file.php script can be edited.

Intermusic Project
----------
This module was created as part of the [Intermusic Project](https://intermusic.lmta.lt). Its functionality reflects the needs of the project, but it is also intended to work away from that context and use the metadata features more generally. 




