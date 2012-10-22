<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Towntrack - Discover New Music</title>

	<meta name="description" content="Towntrack is a media company driven to make it easier for people to discover new music and artists to promote their music.  towntrack audio player and services are here to fill the ever-increasing gap in the music industry by providing an unbiased, dynamic resource to both local/unsigned artists and their fans.  towntrack provides media services to allow fans to directly discover, listen to, and engage with local artists in their desired town or local area while giving local artists actionable analytics to target their promotions, tours, and distribution strategies." />

	<meta property="fb:app_id" content="<?=$this->config->item('facebook_app_id')?>" />
	<meta http-equiv="cache-control" content="no-cache" />

	<link rel="stylesheet" type="text/css" href="<?=$this->config->item('base_url')?>web/css/style.css" />

	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js?v=1"></script>
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js"></script>
	<script type="text/javascript" src="<?=$this->config->item('base_url')?>web/js/jquery.backstretch.min.js?v=1"></script>
	<script type="text/javascript" src="<?=$this->config->item('base_url')?>web/js/cufon-yui.js?v=1"></script>
	<script type="text/javascript" src="<?=$this->config->item('base_url')?>web/fonts/PT_Sans_400-PT_Sans_700.font.js?v=1"></script>
	<script type="text/javascript" src="<?=$this->config->item('base_url')?>web/soundmanager/script/soundmanager2-nodebug-jsmin.js"></script>
	<script type="text/javascript" src="<?=$this->config->item('base_url')?>web/js/functions.js?v=9"></script>
	
	<link rel="stylesheet" type="text/css" href="<?=$this->config->item('base_url')?>web/js/shadowbox/shadowbox.css">
	<script type="text/javascript" src="<?=$this->config->item('base_url')?>web/js/shadowbox/shadowbox.js"></script>

	<script type="text/javascript">
		$(document).ready(function(){
			soundManager.url = '<?=$this->config->item('base_url')?>web/soundmanager/swf/';
			soundManager.flashVersion = 9; // optional: shiny features (default = 8)
			soundManager.useFlashBlock = false; // optionally, enable when you're ready to dive in

			/*
			 * read up on HTML5 audio support, if you're feeling adventurous.
			 * iPad/iPhone and devices without flash installed will always attempt to use it.
			*/

			soundManager.onready(function() {
			  // Ready to use; soundManager.createSound() etc. can now be called.
				<?if(isset($artist_slug)){?>
					initialize('<?=$this->session->userdata('token');?>', '<?=$artist_slug?>');
				<?} else {?>
					initialize('<?=$this->session->userdata('token');?>');
				<?}?>
				$("#playPause").attr("src", "<?=$this->config->item('base_url')?>web/images/pause.png");
			});
		});
	</script>

	<script type="text/javascript">
  		WebFontConfig = {
    		google: { families: [ 'PT+Serif:400,700,400italic,700italic:latin' ] }
  		};
  		(function() {
    		var wf = document.createElement('script');
    		wf.src = ('https:' == document.location.protocol ? 'https' : 'http') +
    		  '://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js';
    		wf.type = 'text/javascript';
    		wf.async = 'true';
    		var s = document.getElementsByTagName('script')[0];
    		s.parentNode.insertBefore(wf, s);
 		 })(); 
	</script>

	<link rel="icon" type="image/jpeg" href="" />
</head>
<body>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=213199742070365";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
<div id="id"></div>
<div id="login_create" class='ptsans'>
	<div id="login_create_container">
		<form id='login_create_form' autocomplete="off" onSubmit="loginCreateSubmit('<?=$this->session->userdata('token');?>', $('#email').val(), $('#password').val()); return false;" method="POST">
		<h1><span style='display: none;' class='login logincreate'>Log Into Your Account</span><span class='create logincreate' style='display: none;'>Create An Account</span><span id="login_create_error"> - <span style='color: red;'>Failed</span></span></h1>
		<p>Fill out the form below to <span class='login logincreate'>log into</span><span class='create logincreate'>create</span> your account.</p>
	    <div class='block'>
			<h3>Your Email <span id='email_error' style='color: green; font-size: 13px;'></span></h3>
			<input type="text" name='email' id='email' />
		</div>
		<div class='block'>
		    <h3>Your Password <span id='pass_error' style='color: green; font-size: 13px;'></span></h3>
			<input type="password" name='password' id='password' />
		</div>
		<p style='margin-bottom: 0px; margin-top: 10px;' class='create logincreate'>By clicking <span style='color: white;'>Create Account</span> below, you are agreeing to the TownTrack <a style='color: white;' href='#'>Terms of Service</a> and <a style='color: white;' href='#'>Privacy Policy</a>.</p>
		<div style='float: left; margin-top: 10px; margin-right: 13px;'>
			<input type="submit" value="Log In" class='login logincreate' /><input type="submit" value="Create Account" class='create logincreate' /> <input type="submit" onClick='$("#login_create").fadeOut("slow");' value="Cancel" class='cancel' />
		</div>
		</form>
	</div>
</div>

<div id="share_window" class='ptsans'>
	<div id='share_window_container'>
		<h1>Spread The Word!</h1>
		<p>Like what you hear? Click any of the icons below to share this song with the world!</p>
		<div style='margin-top: 10px;'>
			<div style='width: 33%; display: inline-block;' align='center'>
				<a href='#' onClick="window.open('http://www.facebook.com/sharer.php?u='+encodeURIComponent('<?=$this->config->item('base_url')?>'+current_artist_slug)+'&t='+encodeURIComponent($('#title').html()),'sharer','toolbar=0,status=0,width=548,height=325');"><img src='<?=$this->config->item('base_url')?>web/images/Facebook.png' />
				<h2>Facebook</h2></a>
				<h3></h3>
			</div>
			<div style='width: 32%; display: inline-block;' align='center'>
				<a href='#' onClick="window.open('http://twitter.com/share?text='+encodeURIComponent(shareText)+'&via=TownTrack&url='+encodeURIComponent('<?=$this->config->item('base_url')?>'+current_artist_slug),'sharer','toolbar=0,status=0,width=548,height=325');"><img src='<?=$this->config->item('base_url')?>web/images/Twitter.png' />
				<h2>Twitter</h2></a>
				<h3></h3>
			</div>
			<div style='width: 33%; display: inline-block;' align='center'>
                <a href="#" onClick='$("#share_window").fadeOut("slow");'><img src='<?=$this->config->item('base_url')?>web/images/Close.png' />
                <h2>Close</h2></a>
                <h3></h3>
            </div>
		</div>
	</div>
</div>

<div id="genres_window" class='ptsans'>
        <div id='share_window_container'>
                <h1>Genres</h1>
                <p>Towntrack is designed to discover new music, but if a certain genre just drives you nuts, you can disable it here.</p>
                <div style='margin-top: 10px;'>
                        <center>
                        <ul>
                        <? foreach($genres as $genre){ ?>
                        <li id="genre_<?=$genre->slug?>" class='genre <?=(in_array($genre->id, $user_genres))?'genre_liked':'genre_disliked'?>' onClick='toggleGenre("<?=$this->session->userdata('token');?>", "<?=$genre->slug?>", $(this));'><span><?=$genre->title?></span></li>
                        <? } ?>
                        </ul>
                        </center>
                        <h3 class='cancel' onClick='$("#genres_window").fadeOut("slow");'>Close</h3>
                </div>
        </div>
</div>

<div id="error">
	<span>Error Message</span>
</div>

<div id="content">
	<div id="backend"></div>
		<div id="logger">
			<div id="inout" class="box ptsans">
				<span id="login"><a href="#" alt='Log In' class='link' style="text-decoration: none;" onClick="loginCreate('login');">Log In</a> or <a href="#" alt='Create Account' class='link' style="text-decoration: none;" onClick="loginCreate('create');">Create Account</a></span>
				<span id='logout'><a alt='Logout' href="#" class='link' style="text-decoration: none;" onClick="logout();">Logout</a></span>
			</div>
		</div>

		<img id="thumb" />
		<img id="playPause" class="transparent" />
		<div id="controls" class='box'>
			<img src="<?=$this->config->item('base_url')?>web/images/thumbs_up.png" onClick="like('<?=$this->session->userdata('token');?>');" class='transparent' id="thumbs_up" alt='Thumbs Up' />
			<img src="<?=$this->config->item('base_url')?>web/images/thumbs_down.png" onClick="dislike('<?=$this->session->userdata('token');?>');" class='transparent' id="thumbs_down" alt='Thumbs Down' />
			<img src="<?=$this->config->item('base_url')?>web/images/next.png" onClick="skip('<?=$this->session->userdata('token');?>');" class='transparent' id="next" alt='Next Song' />
		</div>

		<div id='share' class='ptsans'>
			<a href='#' onClick='$("#share_window").fadeIn("slow");'>share</a>
		</div>

		<div id='genres' class='ptsans'>
  			<a href='#' onClick='showGenres();'>genres</a>
		</div>

		<div id="info" class='box'>
			<p id="song"><?=$song?$song[0]->title:''?></p>
			<p id="artist"><?=$artist?'by '.$artist[0]->name:''?></p>
			<p id="album"><?=$album?'on '.$album[0]->title:''?></p>
			<div id="duration_background">
		        <div id="duration_bar"></div>
		    </div>
		</div>


	<div id="title_menu" class='ptsans'>
		<center>
            <ul>
                <li><a href="#" target="_blank" alt='About'>about</a></li>
				<li><a href="#" target="_blank" alt='Blog'>blog</a></li>
				<li><a href="#" target="_blank" alt='Artists'>artists</a></li>
				<li><a href="#" target="_blank" alt='Contact'>contact</a></li>
				<li><a href="#" target="_blank" alt='Submit Music'>submit music</a></li>
            </ul>
        </center>
	</div>

	<img src='<?=$this->config->item('base_url')?>web/images/ttlogo.png' style='position: absolute; bottom: 10px; right: 10px;' />

	<div id="title" class='box'>
		<center>
			<span class="link ptsans"> towntrack </span>
			<span class="beta" style='position: absolute; bottom: 9px; right: 17px; color: darkred; font-size: 10px;' class='ptsans'>beta</span>
		</center>
	</div>

	<div id="title_tooltip">
		<center>
            <span class='ptsans'>Click for menu</span>
        </center>
	</div>

	<div id="menu" class='box ptsans'>
		<center>
			<ul>
				<li class="link" id="bandname"><?=$artist?$artist[0]->name:''?></li>
			</ul>
		</center>
	</div>

	<div id="about_tooltip">
        <center>
            <span class='ptsans'>Click for info</span>
        </center>
    </div>

	<div id="about" class='box'>
		<h1 id="about_artist"><?=$artist?$artist[0]->name:''?></h1>
		<p id="about_bio"><?=$artist?$artist[0]->description:''?></p>
		<h2 id="about_info">Artist Info</h2>
        <div id="about_info_container">
            <p><span style="font-weight: bold;">Name:</span> <span id="info_name"><?=$artist?$artist[0]->name:''?></span></p>
            <p><span style="font-weight: bold;">Location:</span> <span id="info_location"><?=$artist?ucwords(preg_replace("/-/", " ", $artist[0]->city)).", ".ucwords(preg_replace("/-/", " ", $artist[0]->state)):''?></span></p>
            <p><span style="font-weight: bold;">Website:</span> <span id="info_website"><?=$artist?'<a href="'.$artist[0]->website.'">'.$artist[0]->website.'</a>':''?></span></p>
        </div>
		<h2 id="about_discography">Discography</h2>
		<div id="about_disco_container">
			<p>Click on the album thumbnail to view it in the iTunes Store.</p>
		</div>
		<div id="about_albums"></div>
		<h2 id="about_videos_header">Videos</h2>
		<div id="about_videos_container">
			<p>Click on the album thumbnail to watch a video.</p>
		</div>
		<div id="videos"></div>
		<h2 id="about_shows">Upcoming Shows</h2>
        <div id="about_shows_container">
            <div id="bandsintown" style="margin-left: 5px;">
            </div>
        </div>
	</div>
</div> 
</body>
</html>
