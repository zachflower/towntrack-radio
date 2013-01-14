TOWNTRACK RADIO
---------------

Towntrack Radio was originally developed by Towntrack Interactive as a way to provide a clean and interactive way for unsigned and indie musicians to be seen and heard by potential fans. This project is no longer supported or under development.

LICENSE
-------

               DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE
                       Version 2, December 2004

    Everyone is permitted to copy and distribute verbatim or modified
    copies of this license document, and changing it is allowed as long
    as the name is changed.

               DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE
      TERMS AND CONDITIONS FOR COPYING, DISTRIBUTION AND MODIFICATION

     0. You just DO WHAT THE FUCK YOU WANT TO.

CONFIGURATION
-------------
### Basic Configuration Options

The basic configuration options required to get started with Towntrack Radio are found in the following file:

    /application/config/towntrack.php

### Filesystem

In order to easily find image and audio files, Towntrack Radio uses the following filesystem structure:

#### Music
    <web_root>/content/artists/<artist_slug>/albums/<album_slug>/<song_file>

#### Album Art
	<web_root>/content/artists/<artist_slug>/albums/<album_slug>/thumb.jpg

#### Artist Background Images
	<web_root>/content/artists/<artist_slug>/profile.jpg

#### Artist Profile Thumbnails
	<web_root>/content/artists/<artist_slug>/thumb.jpg
	
### Database Tables

The MySQL Database Schemas have been provided here:

    /application/sql/towntrack.sql
