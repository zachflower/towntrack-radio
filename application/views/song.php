<!DOCTYPE html>
<html>
	<head>
		<meta property="og:title" content="towntrack - Separating the Music from the Industry" />
	    <meta property="og:description" content="Towntrack is a media company driven to make it easier for people to discover new music and artists to promote their music.  Towntrack audio player and services are here to fill the ever-increasing gap in the music industry by providing an unbiased, dynamic resource to both local/unsigned artists and their fans.  Towntrack provides media services to allow fans to directly discover, listen to, and engage with local artists in their desired town or local area while giving local artists actionable analytics to target their promotions, tours, and distribution strategies." />
	    <meta property="og:image" content="<?=$album_art?>" />
	    <meta name="medium" content="audio" />
	    <meta property="og:audio" content="<?=$song_url?>" />
	    <meta property="og:audio:type" content="Content-Type: audio/mpeg" />
	    <meta property="og:audio:title" content="<?=$song_title?>" /> 
	    <meta property="og:audio:artist" content="<?=$artist_name?>" /> 
	    <meta property="og:audio:album" content="<?=$album_name?>" />
		<meta http-equiv="refresh" content="0;url=<?=$this->config->item('base_url')?>" />
	</head>
</html>
