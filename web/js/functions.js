var loggedin = false;
var like_error_shown = false;
var dislike_error_shown = false;
var genre_error_shown = false;
var shareText = '';
var lastupdate = 0;
var lasttime = '';
var paused = true;
var s;
var muted=false;
var current_artist_slug = '';
var notification_test;
var mail = false;

function initialize(token, artist_slug){
	window.location.hash = '';

	Shadowbox.init({
		onOpen: function(){
			s.pause();
			$("#playPause").attr('src', '/web/images/play.png');
			window.lastupdate=0;
			window.paused=true;
		},
		onClose: function(){
			s.play();
			$("#playPause").attr('src', '/web/images/pause.png');
			window.lastupdate=0;
			window.paused=false;
		}
	});

	if(!artist_slug){
		artist_slug = '';
	}

	$("#content").height($(window).height());
	$("#login_create").height($(window).height());
	$("#share_window").height($(window).height());
	$("#genres_window").height($(window).height());

	Cufon.replace('.ptsans', { fontFamily: 'PT Sans', hover: true });

	$.getJSON('/ajax/isLoggedIn', function(data){
		if(data.loggedin == 0){
			$("#login").show();
		} else {
			loggedin = true;
			$("#logout").show();
		}
	});

	$.getJSON('/ajax/song/'+token+'/'+artist_slug, function(data){
		$("#id").attr('rel', data.id);

		/* Page Background */
		$.backstretch(data.profile);

		/* Player */
		$("#thumb").attr('src', data.thumb);
		$("link[rel='icon']").attr('href', data.thumb);
	
		$("#song").html(data.info.song);
		Cufon.replace('#song', { fontFamily: 'PT Sans', hover: true });
		$("#artist").html("by "+data.info.artist);
		Cufon.replace('#artist', { fontFamily: 'PT Sans', hover: true });
		$("#album").html("on "+data.info.album);
		Cufon.replace('#album', { fontFamily: 'PT Sans', hover: true });

		shareText = "Listening to '"+data.info.song+"' by "+data.info.twitter+" at";
		current_artist_slug = data.info.slug;

		/* Menu */
		$("#bandname").html("About "+data.info.artist);
		Cufon.replace('#bandname', { fontFamily: 'PT Sans', hover: true });

		/* About Pane */
		if(data.soundcloud == 1){
			$("#about_bio").html("<img src='"+data.profile+"' id='about_profile'>"+data.info.bio+"<br /><br />Song powered by <a href='http://soundcloud.com'>SoundCloud</a>.");
		} else {
			$("#about_bio").html("<img src='"+data.profile+"' id='about_profile'>"+data.info.bio);
		}

		$("#about_artist").html(data.info.artist);
		$("#info_name").html(data.info.artist);
		$("#info_location").html(data.info.location);
		$("#info_website").html("<a href='"+data.info.website+"' target='_blank'>"+data.info.website+"</a>");

		$("#about_albums").html("");

		$.each(data.albums, function(key, val){
			if(val.url){
				$("#about_albums").html($("#about_albums").html()+"<div class='albums'><a href='"+val.url+"' target='_blank'><img src='"+val.title+"' /></a><span class='box'>"+key+"</span></div>");
			} else {
				$("#about_albums").html($("#about_albums").html()+"<div class='albums'><img src='"+val.title+"' /><span class='box'>"+key+"</span></div>");
			}
		});

		$.getJSON('/ajax/shows/'+token+'/'+data.id, function(dat){
			$("#bandsintown").html('');
			
			if(dat.status == 1){
				$.each(dat.shows, function(key, val){
					if(val.title.match(/This artist has no upcoming shows/)){
						$("#bandsintown").html("<div>"+val.title+"</div>");
					} else {
						$("#bandsintown").html($("#bandsintown").html()+"<div style='font-weight: bold; font-size: 1.1em;'><a href='"+val.link+"' style='text-decoration: none;' target='_blank'>"+val.title+"</a></div>");
					}
				});
			} else {
				$("#bandsintown").html(dat.message);
			}

			Cufon.replace('h1, h2, h3', { fontFamily: 'PT Sans', hover: true });
		});
		
		$.getJSON('/ajax/videos/'+token+'/'+data.id, function(dat){
			$("#videos").html('');
			
			if(dat.status == 1){
				$.each(dat.videos, function(key, val){
					$("#videos").html($("#videos").html()+"<div class='videos'><a href='"+val.url+"' rel='shadowbox' title='"+val.title+"'><img src='"+val.thumb+"' /></a><span class='box'>"+val.title+"</span></div>");
				});

				Shadowbox.setup('div.videos a');
			} else {
				$("#videos").html(dat.message);
			}

			Cufon.replace('h1, h2, h3', { fontFamily: 'PT Sans', hover: true });
		});

		/* Page Title */
		if(data.soundcloud == 1){
			$("title").html(data.info.song+" by "+data.info.artist+" | Towntrack - Discover New Music | Powered by SoundCloud");
		} else {
			$("title").html(data.info.song+" by "+data.info.artist+" | Towntrack - Discover New Music");
		}

		loadSong(data.song, token);
		s.play();

		paused=false;
		lastupdate=0;


		/* Like Song */
        if(data.liked != 0){
            $("#thumbs_up").addClass('active');
            $("#thumbs_up").removeClass('transparent');
			$("#share").slideDown('slow');
			$("#genres").animate({'top':'178px'}, 'slow');
        }

		/*if (window.webkitNotifications.checkPermission() == 0) { 
    		notification_test = createNotificationInstance({icon:data.thumb, title:'Now Playing...', content:data.info.song+' by '+data.info.artist+' on '+data.info.album});
			notification_test.ondisplay = function() { setTimeout(function() { notification_test.cancel(); }, 10000); };
    		notification_test.show();
  		} else {
    		window.webkitNotifications.requestPermission();
  		}*/
	});

	$(document).keypress(function(e){
		if(!$("#login_create").is(':visible')){
			if(e.which == 32){
				playPause();
			}

			if(e.which == 61 || e.which == 43){
				like(token);
			}

			if(e.which == 45 || e.which == 95){
				dislike(token);
			}
		}
    });

	$(document).keyup(function(e) {
		if($("#error").is(':visible')){
			closeError();
		}

		if(e.which == 191){
			if(muted==false){
				s.mute();
				muted=true;
			} else {
				s.unmute();
				muted=false;
			}
		}

		if(e.which == 39 && !$("#login_create").is(':visible')) {
			skip(token);	
		}

		if(e.which == 27){
			if($("#login_create").is(':visible')){
	            $("#login_create").fadeOut('slow');
	        }

			if($("#share_window").is(':visible')){
				$("#share_window").fadeOut('slow');
			}
			
			if($("#genres_window").is(':visible')){
				$("#genres_window").fadeOut('slow');
			}

			if($("#about").is(':visible')){
        	    if(!$("#logger").is(":visible")){
        	        $("#logger").show();
        	    }

        	    $("#thumb").animate({'height':'100px', 'width':'100px'}, 'slow');
        	    $("#playPause").animate({'height':'100px', 'width':'100px'}, 'slow');

        	    if(!$("#controls").hasClass('down')){ 
        	        $("#controls").animate({'top': '110px', 'left':'5px'}, 'slow');
	            } else {
    	            $("#controls img").css({'margin':'0', 'margin-left':'11px'});
					$("#controls").css({'top': '110px', 'left':'5px', 'width':'90px', 'padding':'7px 5px 5px 5px'});
					$("#controls").removeClass('down');
            	}

            	$("#about").slideUp('slow');
            	$("#info").slideDown('slow');
				$("#share").animate({'left':'0px'}, 'slow');
				$("#genres").animate({'left':'0px'}, 'slow');
            	$("#bandname").removeClass('open');
        	}
		}
	});

	$("#playPause").click(function(){
		playPause();
	});

	$("#bandname").click(function(){
		if(!$(this).hasClass('open')){
			$("#thumb").animate({'height':'30px', 'width':'30px'}, 'slow');	
			$("#playPause").animate({'height':'30px', 'width':'30px'}, 'slow');
			if($(window).width() < 769){
				if($("#logger").is(":visible")){
    	                    $("#logger").hide();
	                    }

				$("#controls img").css({'margin':'5px', 'margin-left':'7px'});
				$("#controls").css({'top': '40px', 'left':'5px', 'width':'30px', 'padding':'0'});
				$("#controls").addClass('down');
				if($(window).width() < 563){
                        	if($("#title").is(":visible")){
                        	    $("#title").hide();
						$("#title_menu").hide();
                        	}

					$("#about").width($(window).width()-76);
					$("#about").css({'margin-left':'-'+$("#about").width()/2+'px'});

					$("#menu").width($("#about").width());
					$("#about_tooltip").width($("#about").width());
	                        $("#menu").css({'margin-left':'-'+$("#about").width()/2+'px'});
					$("#about_tooltip").css({'margin-left':'-'+$("#about").width()/2+'px'});
				}
                	} else if($(window).width() >= 769){
				if(!$("#logger").is(":visible")){
                        	$("#logger").show();
                    	}
                	    $("#controls img").css({'margin':'0', 'margin-left':'11px'});
                	    $("#controls").animate({'top': '5px', 'left':'40px', 'width':'90px', 'padding':'7px 5px 5px 5px'});
                	    $("#controls").removeClass('down');
                	    $("#about").width(485);
                	    $("#about").css({'margin-left':'-245px'});
                	}
			if(!$("#title_menu").is(':visible')){
				$("#about").height($(window).height()-56);
				$("#about").css({'bottom':'51px'});
			} else {
				$("#about").height($(window).height()-97);
				$("#about").css({'bottom':'92px'});
			}

			$("#about").slideDown('slow');
			$("#info").slideUp('slow');
			$("#share").animate({'left':'-200px'}, 'slow');
			$("#genres").animate({'left':'-200px'}, 'slow');
			$(this).addClass('open');
		} else {
			if(!$("#logger").is(":visible")){
                        $("#logger").show();
                    }

			$("#thumb").animate({'height':'100px', 'width':'100px'}, 'slow');
			$("#playPause").animate({'height':'100px', 'width':'100px'}, 'slow');
                    if(!$("#controls").hasClass('down')){
				$("#controls").animate({'top': '110px', 'left':'5px'}, 'slow');
			} else {
				$("#controls img").css({'margin':'0', 'margin-left':'11px'});
                        $("#controls").css({'top': '110px', 'left':'5px', 'width':'90px', 'padding':'7px 5px 5px 5px'});
                        $("#controls").removeClass('down');
			}
			$("#about").slideUp('slow');
			$("#info").slideDown('slow');
			$("#share").animate({'left':'0px'}, 'slow');
			$("#genres").animate({'left':'0px'}, 'slow');
			$(this).removeClass('open');
		}

		$("#about_tooltip").hide();
	});

	$("#title span").click(function(){
		if(!$("#title_menu").is(':visible')){
			if($("#bandname").hasClass('open')){
				$("#about").animate({'height':$("#about").height()-41+'px', 'bottom': '92px'});
			}

			$("#title_menu").slideDown();
		} else {
			if($("#bandname").hasClass('open')){
                        $("#about").animate({'height':$(window).height()-56+'px', 'bottom': '51px'});
                    }
			$("#title_menu").slideUp('slow');
		}

		$("#title_tooltip").hide();
	});

	$("#title span").mouseover(function(){
                if(!$("#title_menu").is(':visible') && !$("#bandname").hasClass('open')){
                    $("#title_tooltip").show();
                }
            });

	$("#title span").mouseout(function(){
		$("#title_tooltip").hide();
	});

	$("#bandname").mouseover(function(){
                if(!$("#bandname").hasClass('open') && !$("#title_menu").is(':visible')){
                    $("#about_tooltip").show();
                }
            });

            $("#bandname").mouseout(function(){
                $("#about_tooltip").hide();
            });

	$(window).resize(function(){
		$("#content").height($(window).height());
		$("#login_create").height($(window).height());

		if(!$("#title_menu").is(':visible')){
			$("#about").height($(window).height()-56);
			$("#about").css({'bottom':'51px'});
		} else {
			$("#about").height($(window).height()-97);
			$("#about").css({'bottom':'92px'});
		}

		if($(window).width() < 769 && $("#bandname").hasClass('open')){
			$("#controls img").css({'margin':'5px', 'margin-left':'7px'});
			$("#controls").css({'top': '40px', 'left':'5px', 'width':'30px', 'padding':'0'});
			$("#controls").addClass('down');

			if($("#logger").is(":visible")){
	                    $("#logger").hide();
	                }

			if($(window).width() < 563){
				if($("#title").is(":visible")){
					$("#title").hide();
					$("#title_menu").hide();
				}

				$("#about").width($(window).width()-76);
				$("#about").css({'margin-left':'-'+$("#about").width()/2+'px'});

				$("#menu").width($("#about").width());
				$("#about_tooltip").width($("#about").width());
                        $("#menu").css({'margin-left':'-'+$("#about").width()/2+'px'});
				$("#about_tooltip").css({'margin-left':'-'+$("#about").width()/2+'px'});
			} else {
				if(!$("#title").is(":visible")){
                            $("#title").show();
					$("#menu").width(360);
					$("#about_tooltip").width(360);
	                        $("#menu").css({'margin-left':'-120px'});
					$("#about_tooltip").css({'margin-left':'-120px'});
	                    }
			}
		} else if($(window).width() >= 769 && $("#bandname").hasClass('open')){
			if(!$("#logger").is(":visible")){
                        $("#logger").show();
                    }
			$("#controls img").css({'margin':'0', 'margin-left':'11px'});
                    $("#controls").css({'top': '5px', 'left':'40px', 'width':'90px', 'padding':'7px 5px 5px 5px'});
                    $("#controls").removeClass('down');
			$("#about").width(485);
			$("#about").css({'margin-left':'-245px'});
		} else if($(window).width() < 563 && !$("#bandname").hasClass('open')){
			if($("#title").is(":visible")){
				$("#title").hide();
				$("#title_menu").hide();
			}

			$("#menu").width($(window).width()-76);
			$("#about_tooltip").width($(window).width()-76);
			$("#menu").css({'margin-left':'-'+$("#menu").width()/2+'px'});
			$("#about_tooltip").css({'margin-left':'-'+$("#menu").width()/2+'px'});
             	} else if($(window).width() >= 563 && !$("bandname").hasClass('open')){
			if(!$("#title").is(":visible")){
				$("#title").show();
				$("#menu").width(360);
				$("#about_tooltip").width(360);
				$("#menu").css({'margin-left':'-120px'});
				$("#about_tooltip").css({'margin-left':'-120px'});
			}
		}
	});

	$("#backend").click(function(){
		if($("#error").is(':visible')){
			closeError();
		}

		if($("#about").is(':visible')){
			if(!$("#logger").is(":visible")){
				$("#logger").show();
			}

            $("#thumb").animate({'height':'100px', 'width':'100px'}, 'slow');
            $("#playPause").animate({'height':'100px', 'width':'100px'}, 'slow');
           	
			if(!$("#controls").hasClass('down')){
                $("#controls").animate({'top': '110px', 'left':'5px'}, 'slow');
            } else {
                $("#controls img").css({'margin':'0', 'margin-left':'11px'});
                        $("#controls").css({'top': '110px', 'left':'5px', 'width':'90px', 'padding':'7px 5px 5px 5px'});
                        $("#controls").removeClass('down');
            }

            $("#about").slideUp('slow');
            $("#info").slideDown('slow');
			$("#share").animate({'left':'0px'}, 'slow');
			$("#genres").animate({'left':'0px'}, 'slow');
            $("#bandname").removeClass('open');
		}
	});

	$("#email").keyup(function(){
		validateEmail($("#email").val());
	});

	$("#password").keyup(function(){
        validatePassword($("#password").val());
    });

	/*window.setInterval(function(){
		if(loggedin){
			if(mail==true){
				if(!$("#mail").is(":visible")){
					$("#mail").show();
					$("#menu").css({'border-top':'5px solid #3299CC', 'padding-top':'10px'});
					$("#title").css({'border-top':'5px solid #3299CC', 'padding-top':'10px'});
				}
			} else {
				if($("#mail").is(":visible")){
					$("#mail").hide();
					$("#menu").css({'border-top':'none', 'padding-top':'15px'});
			        $("#title").css({'border-top':'none', 'padding-top':'15px'});
				}
			}

			$.getJSON('/ajax/checkmail', function(data){
				if((data.success==true && mail==false) || mail==true){
					mail=true;
				} else {
					mail=false;
				}
            });
		}
	}, 1000);*/
}

function update() {
	dur = s.durationEstimate;
	time = s.position;
	fraction = time/dur;
	percent = (fraction*100);
	wrapper = document.getElementById("duration_background");
	new_width = wrapper.offsetWidth*fraction;
	document.getElementById("duration_bar").style.width=new_width+"px";
}

function getNext(token) {
	$.getJSON('/ajax/song/'+token, function(data){
		$("#id").attr('rel', data.id);

		/* Page Background */
		$.backstretch(data.profile);

		/* Player */
		$("#thumb").attr('src', data.thumb);
		$("link[rel='icon']").attr('href', data.thumb);

		$("#song").html(data.info.song);
		Cufon.replace('#song', { fontFamily: 'PT Sans', hover: true });
                $("#artist").html("by "+data.info.artist);
		Cufon.replace('#artist', { fontFamily: 'PT Sans', hover: true });
                $("#album").html("on "+data.info.album);
		Cufon.replace('#album', { fontFamily: 'PT Sans', hover: true });

		shareText = "Listening to '"+data.info.song+"' by "+data.info.twitter+" at";
		current_artist_slug = data.info.slug;

		/* Menu */
                $("#bandname").html("About "+data.info.artist);
		Cufon.replace('#bandname', { fontFamily: 'PT Sans', hover: true });

		/* About Pane */
		if(data.soundcloud == 1){
            $("#about_bio").html("<img src='"+data.profile+"' id='about_profile'>"+data.info.bio+"<br /><br />Song powered by <a href='http://soundcloud.com'>SoundCloud</a>.");
        } else {
            $("#about_bio").html("<img src='"+data.profile+"' id='about_profile'>"+data.info.bio);
        }
		$("#about_artist").html(data.info.artist);
		$("#info_name").html(data.info.artist);
		$("#info_location").html(data.info.location);
		$("#info_website").html("<a href='"+data.info.website+"' target='_blank'>"+data.info.website+"</a>");

		$("#about_albums").html("");

		$.each(data.albums, function(key, val){
			if(val.url){
				$("#about_albums").html($("#about_albums").html()+"<div class='albums'><a href='"+val.url+"' target='_blank'><img src='"+val.title+"' /></a><span class='box'>"+key+"</span></div>");
			} else {
				$("#about_albums").html($("#about_albums").html()+"<div class='albums'><img src='"+val.title+"' /><span class='box'>"+key+"</span></div>");
			}
		});	

		$.getJSON('/ajax/shows/'+token+'/'+data.id, function(dat){
			$("#bandsintown").html('');
		
			if(dat.status == 1){
				$.each(dat.shows, function(key, val){
					if(val.title.match(/This artist has no upcoming shows/)){
						$("#bandsintown").html("<div>"+val.title+"</div>");
                    } else {
                        $("#bandsintown").html($("#bandsintown").html()+"<div style='font-weight: bold; font-size: 1.1em;'><a href='"+val.link+"' style='text-decoration: none;' target='_blank'>"+val.title+"</a></div>");
			        }
				});
			} else {
                $("#bandsintown").html(dat.message);
            }

			Cufon.replace('h1, h2, h3', { fontFamily: 'PT Sans', hover: true });
       	});
       	
       	$.getJSON('/ajax/videos/'+token+'/'+data.id, function(dat){
			$("#videos").html('');
			
			if(dat.status == 1){
				$.each(dat.videos, function(key, val){
                    $("#videos").html($("#videos").html()+"<div class='videos'><a href='"+val.url+"' rel='shadowbox' title='"+val.title+"'><img src='"+val.thumb+"' /></a><span class='box'>"+val.title+"</span></div>");
                });

				Shadowbox.setup('div.videos a');
			} else {
				$("#videos").html(dat.message);
			}

			Cufon.replace('h1, h2, h3', { fontFamily: 'PT Sans', hover: true });
		});

		/* Page Title */
		if(data.soundcloud == 1){
            $("title").html(data.info.song+" by "+data.info.artist+" | Towntrack - Discover New Music | Powered by SoundCloud");
        } else {
            $("title").html(data.info.song+" by "+data.info.artist+" | Towntrack - Discover New Music");
        }

		s.destruct();
		loadSong(data.song, token);
		s.play();

		lastupdate=0;
		paused=false;

		/* Like Song */
                if(data.liked != 0){
                    $("#thumbs_up").addClass('active');
                    $("#thumbs_up").removeClass('transparent');
					$("#share").slideDown('slow');
					$("#genres").animate({'top':'178px'}, 'slow');
                } else {
			$("#thumbs_up").addClass('transparent');
	                $("#thumbs_up").removeClass('active');
					$("#share").slideUp('slow');
					$("#genres").animate({'top':'145px'}, 'slow');
		}

		$("#playPause").attr('src', '/web/images/pause.png');

		/*if (window.webkitNotifications.checkPermission() == 0) {
            notification_test = createNotificationInstance({icon:data.thumb, title:'Now Playing...', content:data.info.song+' by '+data.info.artist+' on '+data.info.album});
			notification_test.ondisplay = function() { setTimeout(function() { notification_test.cancel(); }, 10000); };            
			notification_test.show();
        } else {
            window.webkitNotifications.requestPermission();
        }*/
    });
}

function playPause(){
	if(s.paused){
		s.play();
		$("#playPause").attr('src', '/web/images/pause.png');
		paused=false;
		lastupdate=0;
	} else {
		s.pause();
		$("#playPause").attr('src', '/web/images/play.png');
		paused=true;
		lastupdate=0;
	}
}

function loadSong(url, token){
	s = soundManager.createSound({
			id:'audio',
			url:url,
			multiShot:false,
			whileplaying:function() {
				update();
			},
			onfinish:function(){
		 		playNext(token);
			}
	});

	if(muted==true){
		s.mute();
	}
}

function like(token){
	if(!loggedin){
		if(!like_error_shown){
			like_error_shown = true;

			$("#error").slideDown('fast');
			$("#error span").html('Unfortunately, you must log in if you want to like a song. <a href="#" onClick="closeError();">Close</a>.');
			$("#logger").animate({top:'26px'}, 'fast');
			$("#thumb").animate({top:'26px'}, 'fast');
			$("#info").animate({top:'26px'}, 'fast');
			$("#share").animate({top:'166px'}, 'fast');
			$("#genres").animate({'top':'166px'}, 'fast');
			$("#playPause").animate({top:'26px'}, 'fast');
			$("#controls").animate({top:'131px'}, 'fast');
		}
	}

	if(!$("#thumbs_up").hasClass('active')){
		$("#thumbs_up").addClass('active');
		$("#thumbs_up").removeClass('transparent');
		
		if(loggedin){
			$.post('/ajax/like/'+token+'/'+$("#id").attr('rel'));
			$("#share").slideDown('slow');
			$("#genres").animate({'top':'178px'}, 'slow');
		}
	} else {
		$("#thumbs_up").addClass('transparent');
		$("#thumbs_up").removeClass('active');

		if(loggedin){
			$.post('/ajax/unlike/'+token+'/'+$("#id").attr('rel'));
			$("#share").slideUp('slow');
			$("#genres").animate({'top':'145px'}, 'slow');
		}
	}
}

function dislike(token){
	if(!loggedin){
		if(!dislike_error_shown){
			dislike_error_shown = true;

			$("#error span").html('Unfortunately, you must log in if you want to dislike a song. <a href="#" onClick="$(\'#error\').slideUp(); return false;">Close</a>.');
	        $("#error").slideDown('fast');
			$("#logger").animate({top:'26px'}, 'fast');
            $("#thumb").animate({top:'26px'}, 'fast');
            $("#info").animate({top:'26px'}, 'fast');
			$("#share").animate({top:'166px'}, 'fast');
			$("#genres").animate({'top':'166px'}, 'fast');
            $("#playPause").animate({top:'26px'}, 'fast');
            $("#controls").animate({top:'131px'}, 'fast');
		}

		getNext(token);
        return;
	}

	getNext(token);

	if(!$("#thumbs_up").hasClass('active')){
        $.post('/ajax/dislike/'+token+'/'+$("#id").attr('rel'));
    } else {
		$.post('/ajax/undislike/'+token+'/'+$("#id").attr('rel'));
	}
}

function skip(token){
	dur = s.durationEstimate;
    time = s.position;
    fraction = time/dur;
    percent = (fraction*100);

	$.post('/ajax/skip/'+token+'/'+$("#id").attr('rel')+'/'+encodeURIComponent(percent));

	getNext(token);
}

function playNext(token){
	$.post('/ajax/plays/'+token+'/'+$("#id").attr('rel'));

	getNext(token);
}

function loginCreate(action){
	$("#login_create").fadeIn('slow');
	$("#login_create .logincreate").hide();
	$("#login_create input[type='text']").val('');
	$("#login_create input[type='password']").val('');
	$("#login_create_error").hide();
	$("#email_error").html('');
	$("#pass_error").html('');
	$("."+action).show();
}

function closeError(){
	$('#error').slideUp('fast'); 
	$("#logger").animate({top:'5px'}, 'fast');
	$("#thumb").animate({top:'5px'}, 'fast');
	$("#info").animate({top:'5px'}, 'fast');
	$("#share").animate({top:'145px'}, 'fast');
	$("#genres").animate({'top':'145px'}, 'fast');
	$("#playPause").animate({top:'5px'}, 'fast');
	$("#controls").animate({top:'110px'}, 'fast');
	$("#error span").css({'background-color': '#500'});

	return false;
}

function validateEmail(address){
	if($(".create").is(':visible')){
		var taken = 1;

		if (address == '') {
	        $("#email_error").html('');
	    } else if(!address.match(/^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/)){
			$("#email_error").css({'color':'red'});
			$("#email_error").html('Is Invalid');
		} else {
			$.getJSON('/ajax/checkEmail/'+encodeURIComponent(address), function(data){
	            taken = data.taken;
				if(taken == 1) {
            		$("#email_error").css({'color':'red'});
            		$("#email_error").html('Is Taken');
					Cufon.replace('#email_error', { fontFamily: 'PT Sans', hover: true }); 
        		} else {
					$("#email_error").css({'color':'green'});
					$("#email_error").html('Is Perfect!');
					Cufon.replace('#email_error', { fontFamily: 'PT Sans', hover: true }); 
				}
			});
		}

		Cufon.replace('#email_error', { fontFamily: 'PT Sans', hover: true });
	}
}

function validatePassword(password){
	if($(".create").is(':visible')){
		if (password == '') {
            $("#pass_error").html('');
        } else if(password.length>0 && password.length<6){
            $("#pass_error").css({'color':'red'});
            $("#pass_error").html('Is Too Short');
        } else if(!password.match(/[0-9]/)){
			$("#pass_error").css({'color':'red'});
            $("#pass_error").html('Needs At Least One Number');
        } else if(!password.match(/[a-zA-Z]/)){
            $("#pass_error").css({'color':'red'});
            $("#pass_error").html('Needs At Least One Letter');
        } else {
            $("#pass_error").css({'color':'green'});
            $("#pass_error").html('Is Perfect!');
        }

		Cufon.replace('#pass_error', { fontFamily: 'PT Sans', hover: true });
	}
}

function loginCreateSubmit(token, email, password){
	if($(".create").is(':visible')){
		$.post('/ajax/createAccount/'+token, {'email': email, 'password': password }, function(data){
			var obj = jQuery.parseJSON(data);
			if(obj.success == 0){
				$("#login_create_error").fadeIn('fast');
			} else if(obj.success == 1){
				$("#login_create").fadeOut('slow');
				$("#error").slideDown('fast');
				$("#logger").animate({top:'26px'}, 'fast');
	            $("#thumb").animate({top:'26px'}, 'fast');
	            $("#info").animate({top:'26px'}, 'fast');
				$("#share").animate({top:'166px'}, 'fast');
				$("#genres").animate({'top':'166px'}, 'slow');
	            $("#playPause").animate({top:'26px'}, 'fast');
	            $("#controls").animate({top:'131px'}, 'fast');
	            $("#error span").html('Welcome to towntrack! Have a listen and enjoy the tunes! <a href="#" onClick="closeError();">Close</a>.');
				$("#error").css({'background-color': '#050'});
				loggedin = true;
				$("#logout").show();
				$("#login").hide();
				getNext(token);
			}
		});
	} else {
		$.post('/ajax/login/'+token, {'email': email, 'password': password }, function(data){
            var obj = jQuery.parseJSON(data);
            if(obj.success == 0){
                $("#login_create_error").fadeIn('fast');
            } else if(obj.success == 1){
                $("#login_create").fadeOut('slow');
                loggedin = true;
				$("#logout").show();
				$("#login").hide();
            }
        });
	}

	return false;
}

function showGenres(){
	if(!loggedin){
		if(!genre_error_shown){
			genre_error_shown = true;

			$("#error").slideDown('fast');
			$("#error span").html('Unfortunately, you must log in if you want to filter by genre. <a href="#" onClick="closeError();">Close</a>.');
			$("#logger").animate({top:'26px'}, 'fast');
			$("#thumb").animate({top:'26px'}, 'fast');
			$("#info").animate({top:'26px'}, 'fast');
			$("#share").animate({top:'166px'}, 'fast');
			$("#genres").animate({'top':'166px'}, 'fast');
			$("#playPause").animate({top:'26px'}, 'fast');
			$("#controls").animate({top:'131px'}, 'fast');
		}
	} else {
		$("#genres_window").fadeIn("slow");
	}
}

function toggleGenre(token, genre){
	if(loggedin){
		$.post('/ajax/toggleGenre/'+token+'/'+encodeURIComponent(genre));
	
		if($("#genre_"+genre).hasClass('genre_liked')){
			$("#genre_"+genre).removeClass('genre_liked');
			$("#genre_"+genre).addClass('genre_disliked');
			$("#genre_"+genre).css('color', '#222');
		} else {
			$("#genre_"+genre).removeClass('genre_disliked');
			$("#genre_"+genre).addClass('genre_liked');
			$("#genre_"+genre).css('color', 'white');
		}
	
		Cufon.replace('#genre_'+genre, { fontFamily: 'PT Sans', hover: true });
	}
	
	return false;
}

function logout(){
	$.post('/ajax/logout/', function(data){
		loggedin = false;
		$("#login").show();
		$("#logout").hide();
	});

	return false;
}

function createNotificationInstance(options) {
	return window.webkitNotifications.createNotification( options.icon, options.title, options.content );
}
