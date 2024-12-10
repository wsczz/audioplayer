<?php

	/*

		Single File PHP Audio Player 1.1.0

		See END USER LICENSE AGREEMENT for commercial use

		Released: 22-Nov-2021
		http://sye.dk/sfpap/
		By Kenny Svalgaard

	*/

	// --------------------- CONFIGURATION ----------------------

	define('CHARSET', 'UTF-8');
	define('SERVER_TIMEOUT', 15);
	define('MIN_SEARCH_STR', 1);
	define('MAX_SEARCH_RESULT', 100);

	define('TXT_ROOT', 'Home');
	define('TXT_BROWSER', 'Browser');
	define('TXT_PLAYLIST', 'Playlist');
	define('TXT_SEARCH', 'Search');
	define('TXT_TRACKS', 'Titles in playlist');
	define('TXT_CLEAR', '- Click to clear');
	define('TXT_EMPTY', 'Playlist is Empty');
	define('TXT_TITLE', 'Title');
	define('TXT_DIR', 'Directory');
	define('TXT_ACC_DIR', 'Server is unable to access directory');
	define('TXT_NO_RES', 'Server not responding');
	define('TXT_SEARCH_RES', 'search result');
	define('TXT_SEARCHING', 'Searching...');
	define('TXT_SEARCH_DESC', 'Type and choose Title or Directory');
	define('TXT_TOO_MANY', 'Too many matches. Result have been limited to ');
	define('TXT_MIN_SEARCH', 'Minimum search characters: ');
	define('TXT_EXIT', 'Quit player?');

	define('CLR_BACK', '#336eff');
	define('CLR_TEXT', '#0000FF');
	define('CLR_TEXT_SMALL', '#555555');
	define('CLR_TAB', '#FAFAD2');
	define('CLR_TAB_ACT', '#ffffff');
	define('CLR_BUTTON', '#FAFAD2');
	define('CLR_LED_DISABLED', '#808040');
	define('CLR_LED_ON', '#FF4500');
	define('CLR_LED_OFF', '#FF45ee');
	define('CLR_TIME_BOX', '#eeefff');
	define('CLR_TRACK_BOX', '#ffeeff');
	define('CLR_PATH', '#bbbbbb');
	define('CLR_DIR', '#bbbbbb');
	define('CLR_TRACK', '#aaaaaa');
	define('CLR_TRACK_HL', '#dddddd');
	define('CLR_SEARCH', '#dddddd');
	define('CLR_COLLECTION', '#cccccc');

	$audio_extensions=['mp3','wav','ogg'];

	// ------------------ END OF CONFIGURATION ------------------


	function ea($var)
	{
		if(is_array($var))
		{
			$res='';
			foreach($var as $v)
			{
				if(is_array($v))
				{
					$res.=($res?',':'').ea($v);
				}
				else
				{
					$res.=($res?',':'').'"'.$v.'"';
				}
			}
		}
		else
		{
			$res='"'.$var.'"';
		}
		return '['.$res.']';
	}


	function searchForTitle($str,$dir='')
	{
		global $audio_extensions;
		$title=[];
		$items=@scandir('./'.$dir);
		if ($items!==false)
		{
			foreach($items as $item)
			{
				if(is_dir('./'.$dir.$item) and ($item!='.') and ($item!='..'))
				{
					$title=array_merge($title,searchForTitle($str, $dir.$item.'/'));
				}
				elseif((array_search(mb_strtolower(mb_substr($item,mb_strrpos($item,'.')+1)),$audio_extensions)!==false) and (mb_stripos($item,$str)!==false))
				{
					$title[]=$dir.$item;
				}
				if(count($title)>MAX_SEARCH_RESULT)
				{
					$title=array_slice($title,0,MAX_SEARCH_RESULT+1);
					break;
				}
			}
		}
		return $title;
	}


	function searchForDir($str,$dir='')
	{
		$dirs=[];
		$items=@scandir('./'.$dir);
		if ($items!==false)
		{
			foreach($items as $item)
			{
				if(is_dir('./'.$dir.$item) and ($item!='.') and ($item!='..'))
				{
					$dirs=array_merge($dirs,searchForDir($str, $dir.$item.'/'));
					if(mb_stripos($item,$str)!==false)
					{
						$dirs[]=$dir.$item;
					}
				}
				if(count($dirs)>MAX_SEARCH_RESULT)
				{
					$dirs=array_slice($dirs,0,MAX_SEARCH_RESULT+1);
					break;
				}
			}
		}
		return $dirs;
	}


	function echoReqHtml($data,$func)
	{
		echo'<!DOCTYPE html><html><head><meta charset="'.CHARSET.'"><script>var dataContainer='.ea($data).';</script></head><body onload="parent.'.$func.'(dataContainer)"></body></html>';
	}

	// adding &#65038; after to avoid chars being shown as icons
	define('TXT_PLAY', '<alignPlay>&#9658;</alignPlay>');
	define('TXT_PAUSE', '<alignJumpPause>&#10074;&#10074;</alignJumpPause>');
	define('TXT_STOP', '&#9632;');
	define('TXT_NEXT', '<alignJumpTrack>&#9658;&#9658;</alignJumpTrack>');
	define('TXT_PREVIOUS', '<alignJumpTrack>&#9668;&#9668;</alignJumpTrack>');
	define('TXT_MARK', '&#9733;');
	define('TXT_LOAD', '&bull;');
	define('TXT_ARROW', ' &#10137; ');
	define('TXT_SHUFFLE', '<alignShuffle>&#128256;&#xfe0e;</alignShuffle>');
	mb_internal_encoding(CHARSET);
	header('Content-Type: text/html; charset="'.CHARSET.'"');


	if(isset($_POST['dffunc']))
	{
		$func=$_POST['dffunc'];
		$data=$_POST['dfdata'];
		if($func=='dir')
		{
			$error=false;
			$dir=$data;
			$title=[];
			$dirs=[];
			if((mb_strpos('./'.$dir,'/../')===false) and (mb_substr('./'.$dir,-1)==='/') and (mb_strpos($dir,'\\')===false))
			{
				$items=@scandir('./'.$dir);
				if ($items!==false)
				{
					foreach($items as $item)
					{
						if(is_dir('./'.$dir.$item) and ($item!='.') and ($item!='..'))
						{
							$dirs[]=$item;
						}
						elseif(array_search(mb_strtolower(mb_substr($item,mb_strrpos($item,'.')+1)),$audio_extensions)!==false)
						{
							$title[]=$item;
						}
					}
				}
				else
				{
					$error=true;
				}
			}
			else
			{
				$error=true;
			}
			if($error)
			{
				$status=TXT_ACC_DIR;
				$dir='';
				$dirs='';
				$title='';
			}
			else
			{
				$status='ok';
			}
			sort($dirs, SORT_FLAG_CASE | SORT_NATURAL);
			sort($title, SORT_FLAG_CASE | SORT_NATURAL);
			echoReqHtml([$status,$dir,$dirs,$title],'getBrowserData');
			exit();
		}
		if($func=='searchTitle')
		{
			$message='';
			$title=[];
			$searchStr=trim($data);
			if(mb_strlen($searchStr)<MIN_SEARCH_STR)
			{
				$message=TXT_MIN_SEARCH.MIN_SEARCH_STR;
			}
			else
			{
				if($searchStr!=='')
				{
					$title=searchForTitle($searchStr);
					if(count($title)>MAX_SEARCH_RESULT)
					{
						$message=TXT_TOO_MANY.MAX_SEARCH_RESULT;
						$title=array_slice($title,0,MAX_SEARCH_RESULT);
					}
				}
			}
			sort($title, SORT_FLAG_CASE | SORT_NATURAL);
			echoReqHtml([$message,$title],'getSearchTitle');
			exit();
		}
		if($func=='searchDir')
		{
			$message='';
			$dir=[];
			$searchStr=trim($data);
			if(mb_strlen($searchStr)<MIN_SEARCH_STR)
			{
				$message=TXT_MIN_SEARCH.MIN_SEARCH_STR;
			}
			else
			{
				if($searchStr!=='')
				{
					$dir=searchForDir($searchStr);
					if(count($dir)>MAX_SEARCH_RESULT)
					{
						$message=TXT_TOO_MANY.MAX_SEARCH_RESULT;
						$dir=array_slice($dir,0,MAX_SEARCH_RESULT);
					}
				}
			}
			sort($dir, SORT_FLAG_CASE | SORT_NATURAL);
			echoReqHtml([$message,$dir],'getSearchDir');
			exit();
		}
		exit();
	}

             		
?><!DOCTYPE html>
<html>
<head>
	<meta charset="<?php echo CHARSET;?>">
	<title>wesT</title>
	<meta name="viewport" content="width=device-width,initial-scale=1,minimal-ui">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<style>

		/* info: fix to have iOS render outside view-area */
		body *
		{
			-webkit-transform:translate3d(0,0,0);
		}

		body
		{
			font-size:5vw;
			color:<?php echo CLR_TEXT; ?>;
			font-family: Arial,Helvetica Neue,Helvetica,sans-serif;
			-webkit-touch-callout: none;
			-webkit-user-select: none;
			-khtml-user-select: none;
			-moz-user-select: none;
			-ms-user-select: none;
			user-select: none;
			-webkit-overflow-scrolling:touch;
			background:<?php echo CLR_BACK; ?>;
		}

		smallPath
		{
			position:relative;
			top: -0.8em;
			font-size:60%;
			color:<?php echo CLR_TEXT_SMALL; ?>;
		}

		.third
		{
			width:25vw;
			text-align:center;
		}

		.inp
		{
			width:38vw;
			border:none;
			font-size:90%;
			background:<?php echo CLR_SEARCH; ?>;
			margin:0.2em 0.3em 0 0;
			border-radius:0.1em;
		}

		.hideout
		{
			position:absolute;
			left:0em;
			top:0em;
			visibility:hidden;
		}

		.fixedMenu
		{
			position:fixed;
			top:0em;
			left:0em;
			width:100%;
			height:8.3em;
			text-align:left;
			padding:0em;
			background:<?php echo CLR_BACK; ?>;
			z-index:100;
		}

		.timeBox
		{
			text-align:center;
			display:inline-block;
			width:30%;
			background:<?php echo CLR_TIME_BOX; ?>;
			border-radius:0.1em;
			margin:0.4em 1% 0.45em 2%;
		}

		.bar
		{
			width:100%;
			height:1.65em;
			padding:0em 0em 0em 0.25em;
			margin:0em;
		}

		.barOn,.barOff,.barGrey
		{
			border-radius:0.1em;
			cursor:pointer;
			display:inline-block;
			background:<?php echo CLR_LED_OFF; ?>;
			border:none;
			width:1.2em;
			height:1.2em;
			margin:0% 0% 0% 0.9%;
		}

		.barOn
		{
			background:<?php echo CLR_LED_ON; ?>;
		}

		.barGrey
		{
			background:<?php echo CLR_LED_DISABLED; ?>;
		}

		.trackName
		{
			cursor:pointer;
			width:96%;
			height:2em;
			text-align:center;
			white-space:nowrap;
			overflow:auto;
			overflow-y:hidden;
			background:<?php echo CLR_TRACK_BOX; ?>;
			border-radius:0.1em;
			display:inline-block;
			margin:0em 0em 0.5em 0.4em;
			vertical-align:top;
		}

		.tabBack
		{
			background:<?php echo CLR_BACK; ?>;
			text-align:center;
			position:fixed;
			top:8.3em;
			left:0em;
			width:100%;
			height:1.9em;
			z-index:50;
		}

		.tabBrowser,.tabPlaylist,.tabSearch
		{
			text-align:center;
			cursor:pointer;
			position:absolute;
			bottom:0em;
			left:2%;
			width:30.66%;
			height:1.5em;
			border-radius:0.3em 0.3em 0em 0em;
		}

		.tabPlaylist
		{
			left:34.66%;
		}

		.tabSearch
		{
			left:67.33%;
		}

		.tabFrame,.tabFrameBack
		{
			overflow:hidden;
			overflow-y:auto;
			position:absolute;
			top:10.45em;
			left:0em;
			bottom:0em;
			width:100%;
			background:<?php echo CLR_TAB_ACT; ?>;
			z-index:10;
		}

		.tabFrameBack
		{
			border-radius:0.1em;
			overflow:hidden;
			top:10.2em;
			z-index:0;
			position:fixed;
		}

		.listContainer,.pathContainer
		{
			overflow:hidden;
			white-space:nowrap;
			width:98%;
			height:2em;
			margin:0em 0em 0.2em 0.2em;
		}

		.pathContainer
		{
			overflow:auto;
			overflow-y:hidden;
		}

		.browserPath,.browserDir,.browserTitle,.browserTitleHL,.browserAction
		{
			cursor:pointer;
			vertical-align:top;
			border-radius:0.1em;
			overflow:hidden;
			white-space:nowrap;
			display:inline-block;
			background:<?php echo CLR_TRACK; ?>;
			height:100%;
			margin:0% 1% 0% 0%;
		}

		.browserPath
		{
			background:<?php echo CLR_PATH; ?>;
		}

		.browserDir
		{
			background:<?php echo CLR_DIR; ?>;
			width:100%;
			overflow:auto;
			overflow-y:hidden;
		}

		.browserTitle,.browserTitleHL
		{
			width:89%;
			overflow:auto;
			overflow-y:hidden;
		}

		.browserTitleHL
		{
			background:<?php echo CLR_TRACK_HL; ?>;
		}

		.browserAction
		{
			width:10%;
		}

		.mark
		{
			position:relative;
			left:0.5em;
			top:0.2em;
		}

		.markPlay
		{
			visibility:hidden;
			position:absolute;
			font-size:50%;
			left:0.4em;
			top:0.3em;
		}

		.markLoad
		{
			visibility:hidden;
			position:absolute;
			font-size:100%;
			right:0.3em;
			top:-0.15em;
			animation: blinker 1s linear infinite;
		}
		
		@keyframes blinker
		{  
			50% {opacity: 0.0;}
		}

		.button,.shuffleOn,.shuffleOff
		{
			display:inline-block;
			overflow:hidden;
			font-size:150%;
			height:1.35em;
			width:19.5%;
			cursor:pointer;
			background:<?php echo CLR_BUTTON; ?>;
			border:none;
			border-radius:0.1em;
			text-align:center;
			padding:0;
			margin:0 0 0.3em 2%;
		}
		
		.shuffleOn,.shuffleOff
		{
			width:10%;
		}

		.shuffleOn
		{
			background:<?php echo CLR_LED_ON; ?>;
		}
		
		alignPlay
		{
			line-height: 1.3;
		}

		alignJumpPause
		{
			font-size:90%;
		}

		alignShuffle
		{
			font-size:70%;
			letter-spacing:-0.4em;
			margin:0 0.3em 0 0;
		}

		alignJumpTrack
		{
			font-size:70%;
			letter-spacing:-0.3em;
			margin:0 0.3em 0 0;
		}

		.shuffleOn,.shuffleOff
		{
			display:inline-block;
		}

		.collection
		{
			display:inline-block;
			overflow:hidden;
			width:96%;
			background:<?php echo CLR_COLLECTION; ?>;
			border:none;
			border-radius:0.1em;
			text-align:center;
			padding:0.7em 0 0 0;
			margin:0 0 0.3em 2%;
		}

		.landscape
		{
			visibility:hidden;
		}

		@media all and (orientation:landscape)
		{
			body
			{
				font-size:2.5vw;
			}

			.landscape
			{
				visibility:visible;
			}

			.fixedMenu
			{
				width:50%;
			}

			.tabFrame,.tabFrameBack
			{
				top:1.9em;
				left:50%;
				bottom:0em;
				width:50%;
			}

			.tabFrame
			{
				top:2.15em;
			}

			.tabBack
			{
				top:0em;
				left:50%;
				width:50%;
			}

			.third
			{
				width:12.5vw;
			}

			.inp
			{
				width:19vw;
			}
		}

	</style>
	<script>
		var player;
		var playingFrom='';
		var browserPlaylistTitles=[];
		var browserPlaylistDir='';
		var playlistTracks=[];
		var browserCurDir;
		var browserCurDirs=[];
		var browserDirs=[];
		var browserTitles=[];
		var searchDirs=[];
		var searchDirTracks=[];
		var searchplaylistTracks=[];
		var playing=0;
		var playingTrack='';
		var lastProgress=-1;
		var tabShowing=0;
		var loading=false;
		var dataframeTime=0;
		var searchString='';
		var searchAction='';
		var shuffledList=[];
		var shuffle=false;


		function getBrowserData(data)
		{
			loading=false;
			markLoading(false);
			if(String(data[0])=='ok')
			{
				browserCurDir=(String(data[1]));
				var tmpArr=browserCurDir.split('/');
				browserCurDirs=[];
				for(var i=0;i<tmpArr.length;i++)
				{
					if(tmpArr[i]!='')
					{
						browserCurDirs[browserCurDirs.length]=tmpArr[i];
					}
				}
				browserDirs=data[2];
				browserTitles=data[3];
				updateBrowser();
			}
			else
			{
				alert(data[0]);
			}
		}


		function getSearchTitle(data)
		{
			loading=false;
			markLoading(false);
			searchDirs=[];
			searchDirTracks=data[1];
			updateSearch('title');
			if(data[0]!='')
			{
				alert(data[0]);
			}
		}


		function getSearchDir(data)
		{
			loading=false;
			markLoading(false);
			searchDirTracks=[];
			searchDirs=data[1];
			updateSearch('dir');
			if(data[0]!='')
			{
				alert(data[0]);
			}
		}


		function init()
		{
			window.onbeforeunload = function() { return '<?php echo TXT_EXIT; ?>'; };
			checkDataframe();
			showTab(1);
			markPlayingTab('');
			player=gebi('player');
			loadPlaylist();
			updateProgressBar();
			browseDir();
			updateAllLists();
			player.onended = function()
			{
				changeTrack(1);
			}
			player.onpause = function()
			{
				gebi('buttonPlay').innerHTML='<?php echo TXT_PLAY; ?>';
			}
			player.onplaying = function()
			{
				gebi('buttonPlay').innerHTML='<?php echo TXT_PAUSE; ?>';
			}
			player.ontimeupdate = function()
			{
				updateProgressBar();
			}
			player.onloadedmetadata = function()
			{
				updateProgressBar();
			}
		}


		function markLoading(tab)
		{
			if(tab==false)
			{
				gebi('markLoadBrowser').style.visibility='hidden';
				gebi('markLoadSearch').style.visibility='hidden';
			}
			else if(tab=='browser')
			{
				gebi('markLoadBrowser').style.visibility='visible';
				gebi('markLoadSearch').style.visibility='hidden';
			}
			else if(tab=='search')
			{
				gebi('markLoadBrowser').style.visibility='hidden';
				gebi('markLoadSearch').style.visibility='visible';
			}
		}


		function secondsToTime(secs)
		{
			var negative=false;
			if(secs!=secs)
			{
				return '';
			}
			if(secs<0)
			{
				secs*=-1;
				negative=true;
			}
				
			var hh = Math.floor(secs/3600);
			var mm = Math.floor(secs/60)%60;
			var ss = Math.floor(secs)%60;
			return (negative?'-':'')+(hh<10?'0':'')+hh+':'+(mm<10?'0':'')+mm+':'+(ss<10?'0':'')+ss;
		}


		function updateProgressBar()
		{
			var leds=14;
			var cur=player.currentTime;
			var max=player.duration;
			if((cur!=cur)||(max!=max)||(cur>max))
			{
				var bar='';
				lastProgress=progress;
				for(var i=0;i<leds;i++)
				{
					bar+='<div class="barGrey"></div>';
				}
				gebi('bar').innerHTML=bar;
				gebi('trackCurrentTime').innerHTML=secondsToTime(0);
				gebi('trackRemaining').innerHTML=secondsToTime(0);
				gebi('trackDuration').innerHTML=secondsToTime(0);
			}
			else
			{
				gebi('trackCurrentTime').innerHTML=secondsToTime(Math.floor(cur));
				gebi('trackRemaining').innerHTML=secondsToTime(Math.floor(max)-Math.floor(cur));
				gebi('trackDuration').innerHTML=secondsToTime(player.duration);
				var progress = Math.floor(cur/max*leds);
				if(progress==leds)
				{
					progress=leds-1;
				}
				if(progress!=lastProgress)
				{
					var bar='';
					lastProgress=progress;
					for(var i=0;i<leds;i++)
					{
						bar+='<div class="'+(progress==i?"barOn":"barOff")+'" onClick="player.currentTime='+Math.ceil(max/leds*(i))+'"></div>';
					}
					gebi('bar').innerHTML=bar;
				}
			}
		}


		function setCookie(cname,cvalue,exdays)
		{
			var d = new Date();
			d.setTime(d.getTime() + (exdays*24*60*60*1000));
			var expires = 'expires=' + d.toGMTString();
			document.cookie = cname+'='+cvalue+'; '+expires;
		}


		function getCookie(cname)
		{
			var name = cname + '=';
			var ca = document.cookie.split(';');
			for(var i=0; i<ca.length; i++)
			{
				var c = ca[i];
				while (c.charAt(0)==' ')
				{
					c = c.substring(1);
				}
				if (c.indexOf(name) == 0)
				{
					return c.substring(name.length, c.length);
				}
			}
			return '';
		}


		function loadPlaylist()
		{
			var playlistCookie=getCookie('playlist');
			if (playlistCookie!='')
			{
				playlistTracks=playlistCookie.split('|');
			}
		}


		function savePlaylist()
		{
			setCookie('playlist',playlistTracks.join('|'),365);
		}


		function gebi(id)
		{
			return document.getElementById(id);
		}


		function skipSec(sec)
		{
			var skipTo=player.currentTime+sec;
			if (skipTo>player.duration)
			{
				skipTo=player.duration-1;
			}
			if (skipTo<0)
			{
				skipTo=0;
			}
			player.currentTime=skipTo;
		}


		function shuffleToggle()
		{
			shuffle=!shuffle;
			if(shuffle)
			{
				gebi('shuffle').className='shuffleOn';
			}
			else
			{
				gebi('shuffle').className='shuffleOff';
			}
		}


		function shuffleList(length)
		{
			var i, randomPlace, tmp;
			shuffledList=[];
			for (i=0; i<length; i++)
			{
				shuffledList[i]=i;
			}
			for (i=0; i<length; i++)
			{
				randomPlace=Math.floor(Math.random()*length);
				tmp=shuffledList[i];
				shuffledList[i]=shuffledList[randomPlace];
				shuffledList[randomPlace]=tmp;
			}
		}


		function changeTrack(dir)
		{
			if(shuffle)
			{
				if(playingFrom=='browser')
				{
					if(shuffledList.length!=browserPlaylistTitles.length)
					{
						shuffleList(browserPlaylistTitles.length);
					}
				}
				if(playingFrom=='list')
				{
					if(shuffledList.length!=playlistTracks.length)
					{
						shuffleList(playlistTracks.length);
					}
				}
				if(playingFrom=='search')
				{
					if(shuffledList.length!=searchplaylistTracks.length)
					{
						shuffleList(searchplaylistTracks.length);
					}
				}
				var ply=shuffledList.indexOf(playing)+dir;
				if(ply>shuffledList.length-1)
				{
					ply=0;
				}
				if(ply<0)
				{
					ply=shuffledList.length-1;
				}
				playing=shuffledList[ply];
			}
			else
			{
				playing+=dir;
			}
			if(playingFrom=='browser')
			{
				if (playing>browserPlaylistTitles.length-1)
				{
					playing=0;
				}
				else if (playing<0)
				{
					playing=browserPlaylistTitles.length-1;
				}
				setAndPlayTrack(browserPlaylistDir+browserPlaylistTitles[playing]);
			}
			else if (playingFrom=='list')
			{
				if (playing>playlistTracks.length-1)
				{
					playing=0;
				}
				else if (playing<0)
				{
					playing=playlistTracks.length-1;
				}
				setTrackFromPlaylist(playing);
			}
			else if (playingFrom=='search')
			{
				if (playing>searchplaylistTracks.length-1)
				{
					playing=0;
				}
				else if (playing<0)
				{
					playing=searchplaylistTracks.length-1;
				}
				setTrackFromSearch(playing);
			}
		}


		function markPlayingTab(tab)
		{
			playingFrom=tab;
			if (playingFrom=='browser')
			{
				gebi('markBrowser').style.visibility='visible';
				gebi('markList').style.visibility='hidden';
				gebi('markSearch').style.visibility='hidden';
			}
			else if (playingFrom=='list')
			{
				gebi('markBrowser').style.visibility='hidden';
				gebi('markList').style.visibility='visible';
				gebi('markSearch').style.visibility='hidden';
			}
			else if (playingFrom=='search')
			{
				gebi('markBrowser').style.visibility='hidden';
				gebi('markList').style.visibility='hidden';
				gebi('markSearch').style.visibility='visible';
			}
			else
			{
				gebi('markBrowser').style.visibility='hidden';
				gebi('markList').style.visibility='hidden';
				gebi('markSearch').style.visibility='hidden';
			}
		}


		function setTrackFromBrowser(id)
		{
			setAndPlayTrack(browserCurDir+browserTitles[id]);
			playing=id;
			markPlayingTab('browser');
			browserPlaylistDir=browserCurDir;
			browserPlaylistTitles=browserTitles;
		}


		function setTrackFromPlaylist(id)
		{
			playing=id;
			markPlayingTab('list');
			setAndPlayTrack(playlistTracks[id]);
		}


		function setTrackFromSearch(id,updateSearchPlaylist)
		{
			if(updateSearchPlaylist==true)
			{
				searchplaylistTracks=searchDirTracks;
			}
			playing=id;
			markPlayingTab('search');
			setAndPlayTrack(searchplaylistTracks[id]);
		}


		function updateAllLists()
		{
			updateBrowser();
			updatePlaylist();
			updateSearch();
		}


		function setAndPlayTrack(track)
		{
			gebi('trackName').innerHTML='&nbsp;'+getTrackTitle(track)+'<br>&nbsp;<smallPath>'+getTrackDir(track)+'</smallPath>';
			playingTrack=track;
			player.src=track;
			player.play();
			updateAllLists();
		}


		function getTrackTitle(track)
		{
			var name=track.split('/').pop();
			name=name.replace(new RegExp('_','g'),' ');
			name=name.substr(0,name.lastIndexOf('.'));
			return name;
		}


		function getTrackDir(track)
		{
			track='<?php echo TXT_ROOT; ?>/'+track.replace(new RegExp('_','g'),' ');
			var dirStr=track.split('/');
			var tmp=dirStr.pop();
			return dirStr.join('<?php echo TXT_ARROW; ?>');
		}


		function updateBrowser()
		{
			var list='';
			list+='<div class="pathContainer"><div class="browserPath" onClick="browseDir()">&nbsp;<?php echo TXT_ROOT; ?>&nbsp;</div>';
			for(var i=0;i<browserCurDirs.length;i++)
			{
				list+='<div class="browserPath" onClick="browseDirFromBreadCrumbBar('+i+')">&nbsp;'+browserCurDirs[i]+'&nbsp;</div>';
			}
			list+='</div>';
			for(var i=0;i<browserDirs.length;i++)
			{
				list+='<div class="listContainer"><div class="browserDir" onClick="browseDir('+i+')">&nbsp;'+browserDirs[i]+'&nbsp;<br>&nbsp;<smallPath>'+getTrackDir(browserCurDir)+'</smallPath></div></div>';
			}
			var playlistCount;
			for(var i=0;i<browserTitles.length;i++)
			{
				playlistCount=inPlaylist(browserCurDir+browserTitles[i]);
				list+='<div class="listContainer"><div class="'+(playingTrack==browserCurDir+browserTitles[i]?'browserTitleHL':'browserTitle')+'" onClick="setTrackFromBrowser('+i+')">&nbsp;'+getTrackTitle(browserTitles[i])+'&nbsp;<br>&nbsp;<smallPath>'+getTrackDir(browserCurDir)+'</smallPath></div><div class="browserAction" onClick="'+(playlistCount>0?'removeBrowserTrackFromPlaylist':'addTrackFromBrowser')+'('+i+')">'+(playlistCount>0?'<div class="mark"><?php echo TXT_MARK; ?></div>':'&nbsp;')+'</div></div>';
			}
			gebi('frameBrowser').innerHTML=list;
		}


		function updatePlaylist()
		{
			savePlaylist();
			var list='<div class="listContainer">';
			if (playlistTracks.length>0)
			{
				list+='<div class="browserDir" onClick="clearPlaylist()">&nbsp;<?php echo TXT_TRACKS; ?>: '+String(playlistTracks.length)+' <?php echo TXT_CLEAR; ?>';
			}
			else
			{
				list+='<div class="browserDir">&nbsp;<?php echo TXT_EMPTY; ?>';
			}
			list+='</div></div>';
			for(var i=0;i<playlistTracks.length;i++)
			{
				list+='<div class="listContainer"><div class="'+(playingTrack==playlistTracks[i]?'browserTitleHL':'browserTitle')+'" onClick="setTrackFromPlaylist('+i+');player.play()">&nbsp;'+getTrackTitle(playlistTracks[i])+'&nbsp;<br>&nbsp;<smallPath>'+getTrackDir(playlistTracks[i])+'</smallPath></div><div class="browserAction" onClick="removeTrack('+i+')"><div class="mark"><?php echo TXT_MARK; ?></div></div></div>';
			}
			gebi('framePlaylist').innerHTML=list;
		}


		function updateSearch(action)
		{
			if(action!=undefined)
			{
				searchAction=action;
			}
			var list='<div class="pathContainer"><div class="browserPath">&nbsp;<input class="inp" value="'+(searchAction=='clear'?'':searchString)+'" id="searchStr" name="searchStr" type="text"></div><div class="browserPath" onClick="searchString=gebi(\'searchStr\').value; searchForTitle(searchString); updateSearch(\'search\')"><div class="third"><?php echo TXT_TITLE; ?></div></div><div class="browserPath" onClick="searchString=gebi(\'searchStr\').value; searchForDir(searchString); updateSearch(\'search\')"><div class="third"><?php echo TXT_DIR; ?></div></div></div>';
			list+='<div class="listContainer"><div class="browserDir" onClick="updateSearch(\'clear\')">';
			if (searchAction=='dir')
			{
				list+='&nbsp;<?php echo TXT_DIR.' '.TXT_SEARCH_RES; ?>: '+String(searchDirs.length);
			}
			else if (searchAction=='title')
			{
				list+='&nbsp;<?php echo TXT_TITLE.' '.TXT_SEARCH_RES; ?>: '+String(searchDirTracks.length);
			}
			else if (searchAction=='search')
			{
				list+='&nbsp;<?php echo TXT_SEARCHING; ?>';
				searchDirs=[];
				searchDirTracks=[];
			}
			else if (searchAction=='clear')
			{
				list+='&nbsp;<?php echo TXT_SEARCH_DESC; ?>';
				searchDirs=[];
				searchDirTracks=[];
			}
			else
			{
				list+='&nbsp;<?php echo TXT_SEARCH_DESC; ?>';
			}
			list+='</div></div>';
			for(var i=0;i<searchDirs.length;i++)
			{
				list+='<div class="listContainer"><div class="browserDir" onClick="browseDirByStr(searchDirs['+i+'])">&nbsp;'+searchDirs[i].split('/').pop()+'&nbsp;<br>&nbsp;<smallPath>'+getTrackDir(searchDirs[i])+'</smallPath></div></div>';
			}
			var playlistCount;
			for(var i=0;i<searchDirTracks.length;i++)
			{
				playlistCount=inPlaylist(searchDirTracks[i]);
				list+='<div class="listContainer"><div class="'+(playingTrack==searchDirTracks[i]?'browserTitleHL':'browserTitle')+'" onClick="setTrackFromSearch('+i+',true)">&nbsp;'+getTrackTitle(searchDirTracks[i])+'&nbsp;<br>&nbsp;<smallPath>'+getTrackDir(searchDirTracks[i])+'</smallPath></div><div class="browserAction" onClick="'+(playlistCount>0?'removeSearchTrackFromPlaylist':'addTrackFromSearch')+'('+i+')">'+(playlistCount>0?'<div class="mark"><?php echo TXT_MARK; ?></div>':'&nbsp;')+'</div></div>';
			}
			gebi('frameSearch').innerHTML=list;
		}


		function inPlaylist(track)
		{
			var number=0;
			for(var i=0;i<playlistTracks.length;i++)
			{
				if(playlistTracks[i]==track)
				{
					number++;
				}
			}
			return number;	
		}


		function addTrackFromBrowser(id)
		{
			playlistTracks[playlistTracks.length]=browserCurDir+browserTitles[id];
			updateAllLists();
		}


		function addTrackFromSearch(id)
		{
			playlistTracks[playlistTracks.length]=searchDirTracks[id];
			updateAllLists();
		}


		function clearPlaylist()
		{
			if (confirm('Clear Playlist?')==true)
			{
				playlistTracks=[];
				updateAllLists();
			}
		}


		function playerStop()
		{
			if((player.paused)&&(player.currentTime==0))
			{
				player.src='';
				gebi('trackName').innerHTML='';
				playing=0;
				updateProgressBar();
				markPlayingTab('');
				playingTrack='';
			}
			else
			{
				player.pause();
				player.currentTime=0;
			}
			updateAllLists();
		}


		function removeBrowserTrackFromPlaylist(id)
		{
			var index=playlistTracks.lastIndexOf(browserCurDir+browserTitles[id])
			if(index>-1)
			{
				playlistTracks.splice(index,1);
			}
			updateAllLists();
		}


		function removeSearchTrackFromPlaylist(id)
		{
			var index=playlistTracks.lastIndexOf(searchDirTracks[id])
			if(index>-1)
			{
				playlistTracks.splice(index,1);
			}
			updateAllLists();
		}


		function removeTrack(id)
		{
			playlistTracks.splice(id,1);
			if (id<=playing)
			{
				playing--;
				if (playing<0)
				{
					playing=playlistTracks.length;
				}
			}
			updateAllLists();
		}


		function searchForTitle(search)
		{
			markLoading('search');
			loadFromServer('searchTitle',search);
		}


		function searchForDir(search)
		{
			markLoading('search');
			loadFromServer('searchDir',search);
		}


		function browseDirFromBreadCrumbBar(id)
		{
			var dir='';
			for(var i=0;i<=id;i++)
			{
				dir+=browserCurDirs[i]+'/';
			}
			markLoading('browser');
			loadFromServer('dir',dir);
		}


		function browseDir(id)
		{
			var dir='';
			if(id!==undefined)
			{
				dir+=browserCurDir+browserDirs[id]+'/';
			}
			markLoading('browser');
			loadFromServer('dir',dir);
		}


		function browseDirByStr(str)
		{
			markLoading('browser');
			loadFromServer('dir',str+'/');
			tabShowing=0;
			showTab(1);
		}


		function getPlayingDir()
		{
			if(playingTrack!=='')
			{
				var path=playingTrack.substr(0,playingTrack.lastIndexOf('/'))+'/';
				markLoading('browser');
				loadFromServer('dir',path);
				tabShowing=0;
				showTab(1);
			}
		}


		function loadFromServer(param,varia)
		{
			dataframeTime=<?php echo SERVER_TIMEOUT; ?>;
			loading=true;
			gebi('dffunc').value=param;
			gebi('dfdata').value=varia;
			gebi('dfform').submit();
		}


		function checkDataframe()
		{
			if(loading)
			{
				if(dataframeTime>0)
				{
					dataframeTime--;
				}
				else
				{
					loading=false;
					markLoading(false);
					window.frames['dataframe'].window.location.replace('about:blank');
					alert('<?php echo TXT_NO_RES; ?>');
				}
			}
			setTimeout(function () { checkDataframe(); }, 1000);
		}


		function showTab(id)
		{
			if(tabShowing==id)
			{
				if(id==1)
				{
					if(browserCurDirs.length>1)
					{
						browseDirFromBreadCrumbBar(browserCurDirs.length-2);
					}
					else if(browserCurDirs.length==1)
					{
						browseDir();
					}
				}
			}
			else
			{
				tabShowing=id;
				if (id==1)
				{
					gebi('frameBrowser').style.display='inline';
					gebi('framePlaylist').style.display='none';
					gebi('frameSearch').style.display='none';
					gebi('tabBrowser').style.background='<?php echo CLR_TAB_ACT; ?>';
					gebi('tabPlaylist').style.background='<?php echo CLR_TAB; ?>';
					gebi('tabSearch').style.background='<?php echo CLR_TAB; ?>';
				}
				if (id==2)
				{
					gebi('frameBrowser').style.display='none';
					gebi('framePlaylist').style.display='inline';
					gebi('frameSearch').style.display='none';
					gebi('tabBrowser').style.background='<?php echo CLR_TAB; ?>';
					gebi('tabPlaylist').style.background='<?php echo CLR_TAB_ACT; ?>';
					gebi('tabSearch').style.background='<?php echo CLR_TAB; ?>';
				}
				if (id==3)
				{
					gebi('frameBrowser').style.display='none';
					gebi('framePlaylist').style.display='none';
					gebi('frameSearch').style.display='inline';
					gebi('tabBrowser').style.background='<?php echo CLR_TAB; ?>';
					gebi('tabPlaylist').style.background='<?php echo CLR_TAB; ?>';
					gebi('tabSearch').style.background='<?php echo CLR_TAB_ACT; ?>';
				}
			}
		}

	</script>
</head>
<body onload="init()">
	<audio class="hideout" autoplay id="player" preload="auto" tabindex="0"></audio>
	<div class="hideout"><form id="dfform" target="dataframe" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post"><input type="hidden" name="dffunc" id="dffunc" value=""><input type="hidden" name="dfdata" id="dfdata" value=""></form><iframe src="about:blank" height="0" width="0" name="dataframe"></iframe></div>
	<div class="fixedMenu"><div class="timeBox" id="trackCurrentTime"></div><div class="timeBox" id="trackRemaining"></div><div class="timeBox" id="trackDuration"></div><div id="bar" class="bar"></div><div id="trackName" class="trackName" onClick="getPlayingDir()">&nbsp;</div><div class="button" onClick="(player.paused?player.play():player.pause())" id="buttonPlay"><?php echo TXT_PLAY; ?></div><div class="button" onClick="playerStop()" id="buttonStop"><?php echo TXT_STOP; ?></div><div class="button" onClick="changeTrack(-1);player.play()"><?php echo TXT_PREVIOUS; ?></div><div class="button" onClick="changeTrack(1);player.play()"><?php echo TXT_NEXT; ?></div><div id="shuffle" class="shuffleOff" onClick="shuffleToggle()"><?php echo TXT_SHUFFLE; ?></div><div class="landscape"><div class="collection"><div class="button" onclick="skipSec(5)">+5</div><div class="button" onclick="skipSec(10)">+10</div><div class="button" onclick="skipSec(30)">+30</div><div class="button" onclick="skipSec(60)">+60</div><div class="button" onclick="skipSec(-5)">-5</div><div class="button" onclick="skipSec(-10)">-10</div><div class="button" onclick="skipSec(-30)">-30</div><div class="button" onclick="skipSec(-60)">-60</div></div></div></div>
	<div class="tabBack"><div class="tabBrowser" id="tabBrowser" onClick="showTab(1)"><div id="markBrowser" class="markPlay"><?php echo TXT_PLAY; ?></div><div id="markLoadBrowser" class="markLoad"><?php echo TXT_LOAD; ?></div><?php echo TXT_BROWSER; ?></div><div class="tabPlaylist" id="tabPlaylist" onClick="showTab(2)"><div id="markList" class="markPlay"><?php echo TXT_PLAY; ?></div><?php echo TXT_PLAYLIST; ?></div><div class="tabSearch" id="tabSearch" onClick="showTab(3)"><div id="markSearch" class="markPlay"><?php echo TXT_PLAY; ?></div><div id="markLoadSearch" class="markLoad"><?php echo TXT_LOAD; ?></div><?php echo TXT_SEARCH; ?></div></div>
	<div class="tabFrameBack"></div>
	<div id="frameBrowser" class="tabFrame"></div>
	<div id="framePlaylist" class="tabFrame"></div>
	<div id="frameSearch" class="tabFrame"></div>
</body>
</html>