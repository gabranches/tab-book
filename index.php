<?php

// Get Initial Artists on Page Load

include('connect.php');

$query = "SELECT * FROM entries ORDER BY artist, title";
$result = $mysqli->query($query);       
$rows = array();

while($r= mysqli_fetch_assoc($result)) {
    $rows[] = $r;
}

$mysqli->close();

?>
<!DOCTYPE html>
<html lang="en">

<head>

<title>TabBook</title>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>

<link href='http://fonts.googleapis.com/css?family=Oswald:400,700' rel='stylesheet' type='text/css'>
<link href='http://fonts.googleapis.com/css?family=Droid+Sans:700' rel='stylesheet' type='text/css'>
<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
<link rel="stylesheet" href="style.css">


</head>

<body>


<div class="container">
	<div class="row">
		<div id="top-bar" class="col-md-12"></div>
	</div>
	
	<div class="row main-row">
		<div id="new-entry" class="col-md-3 form-container">
			
			<form id="insert-form" role='form'>
				<div class='form-group text-center'>
					<label id='artist-label' for='input-artist'>ARTIST</label>
					<input class='form-control' type="text" id="input-artist" name="artist">
				</div>
				<div class='form-group text-center'>
					<label id='title-label' for='input-title'>SONG TITLE</label>
					<input type="text" class='form-control' id="input-title" name="title">
				</div>
				<div class='form-group text-center'>
					<div class='radio-inline'>
						<label>
							<input type="radio" name="linkType" value="guitar tab" checked="checked"> Guitar Tab
						</label>
					</div>
					<div class='radio-inline'>
						<label>
							<input type="radio" name="linkType" value="bass tab"> Bass Tab
						</label>
					</div>
					<div class='radio-inline'>
						<label>
							<input type="radio" name="linkType" value="chords"> Chords
						</label>
					</div>
				</div>
				<div class='form-group text-center'>
					<p><input type="button" class="btn btn-primary" id='get-tab' value="Get Tab from Google"><img class='external-link' id='google-external' title='See Google results in separate window.' src='icons/expand43.png' /></p>
				</div>
				<div class='form-group text-center'>
					<label id='tab-link-label' for='input-url'>Tab Link</label><img class='external-link' id='tab-external' title='View tab in separate window.' src='icons/expand43.png' />
					<input type="text" class='form-control' id="input-url" name="url">
				</div>
				<div class='form-group text-center'>
					<p><input type="button" class="btn btn-primary" id="input-submit" value="Add Song to Collection"></p>
				</div>
				<div class='form-group text-center'>
					<p><input type="button" class="btn btn-danger" id="input-cancel" value="Cancel"  style="display: none"></p>
				</div>
				<input type="hidden" id="input-id" name="id" value="0">
				<input type="hidden" id="input-edit" name="editMode" value="0">
			</form>
			<div id="video-hide-button" class="text-center"><small>hide video tray</small></div>
		</div>
		<div class="col-md-9 right-side-container">
			<div class="row right-side-container">
				<div id="artists-container" class="col-md-5 text-right"></div>
				<div id="songs-container" class="col-md-7 ">
					
				</div>
			</div>
		</div>
	</div>
	<div class="row text-center">	
		<div id="video-tray" class="col-md-12" style="display: none;">
		
			<div id="video1" class="col-md-4 video-box"></div>
			<div id="video2" class="col-md-4 video-box"></div>
			<div id="video3" class="col-md-4 video-box"></div>
			
		</div>
	</div>
</div>

<script id="source" language="javascript" type="text/javascript">

// Document Ready

$(document).ready(function() {

	resizeContainer(40);

	// Initialize variables
	currentSong = {id:0,title:0,artist:0,url:0}
	songCount = 0;
	showvideo = 0;
	
	// Run on Load
	
	json = <?php echo json_encode($rows); ?>;
	startUp(json);
	
	
}); // End of DOM Ready


// Submit/Insert Song (Ajax)

$(document).on("click", "#input-submit", function(){
		var values = $("#insert-form").serialize();
		
		$.ajax({
			url: "insert.php",
			type: "post",
			data: values,
			success: function(){
				currentSong.artist = $("#input-artist").val()	
				currentSong.title = $("#input-title").val();
				StatusAlert(currentSong.artist + " - " + currentSong.title + " has been added to your collection.");
				getArtists();
				editMode(false);
			},
			error:function(){
				StatusAlert("Could not add song. Please try again later.");
			}
		});
	});

// Delete Song (Ajax)

$(document).on("click", ".delete-song", function(){
		currentSong.id = $(this).parent().attr("data-id");
		currentSong.title = replaceDashes($(this).parent().attr("data-title"));
		
		var result = confirm("Are you sure you want to remove this song from your collection?");
		if (result==true) {
			$.ajax({
				url: "deletesong.php",
				type: "post",
				data: { id : currentSong.id },
				success: function(){
					StatusAlert(currentSong.artist + " - " + currentSong.title + " has been deleted successfully.");
					$("#"+currentSong.id).parent().remove();
					songCount--;
					if(songCount == 0){
						$("#"+currentSong.artist).remove();
					}
					getArtists();
					highlightArtist(currentSong.artist);
				},
				error:function(){
					StatusAlert("Could not delete song. Please try again later.");
				}
			});
		}
	}); 
	
// Edit Song (Click Function)

$(document).on("click", ".edit-button", function(){
	currentSong.id = $(this).parent().attr("data-id");
	currentSong.title = replaceDashes($(this).parent().attr("data-title"));
	$("#input-artist").val(currentSong.artist);
	$("#input-title").val(currentSong.title);
	$("#input-url").val($(this).parent().attr("data-url"));
	$("#input-id").val($(this).parent().attr("data-id"));
	editMode(true);
});

// Hide video tray

$(document).on("click", "#video-hide-button", function(){
	if(showvideo == 1){
		$("#video-tray").hide();
		$("#video-hide-button").html("<small>show video tray</small>");
		resizeContainer(40);
		showvideo = 0;
	} else {
		resizeContainer(220);
		$("#video-tray").show();
		$("#video-hide-button").html("<small>hide video tray</small>");
		showvideo = 1;
	}
});

// Toggle Edit Mode

function editMode(onoff){
	if(onoff == true){
		$("#input-edit").attr("value", "1");
		$("#artists-container").css("opacity", "0.2");
		$("#songs-container").css("opacity", "0.2");
		$("#video-tray").css("opacity", "0.2");
		$("#top-bar").css("opacity", "0.2");
		$("#input-submit").val("Edit");
		$("#input-cancel").show();
		$("#video-hide-button").css("opacity", "0.0");
	} else {
		$("#input-edit").attr("value", "0");
		$("#artists-container").css("opacity", "1");
		$("#songs-container").css("opacity", "1");
		$("#video-tray").css("opacity", "1");
		$("#top-bar").css("opacity", "1");
		$("#input-submit").val("Add Song to Collection");
		$("#input-cancel").hide();
		$("#input-artist").val("");
		$("#input-title").val("");
		$("#input-url").val("");
		$("#video-hide-button").css("opacity", "1");
	}
}


// Select Artist (Click Function)

$("#artists-container").on("click", "div", function(){
    currentSong.artist = replaceDashes($(this).attr("id"));
    $(".artist").css("background-color", "#FFFEDF");
	getSongs(currentSong.artist); 
	
 });

// Cancel (Click Function)

$(document).on("click", "#input-cancel", function(){
	editMode(false);
});


// Get YouTube Videos (Click Function)
	
$(document).on("click", ".youtube-button", function(){
	resizeContainer(220);
	currentSong.title = replaceDashes($(this).parent().attr("data-title"));
	getVideo(currentSong.artist, currentSong.title);
});

// Get Tab from Google (Click Function)
	
$(document).on("click", "#get-tab", function(){
	console.log('clicked');
	currentSong.artist = $("#input-artist").val();
	currentSong.title = $("#input-title").val();
	var linkType = $("input[name=linkType]:checked").val();
	console.log(currentSong.title);
	getGoogle(currentSong.artist, currentSong.title, linkType);
});

// Open Tab in separate window (Click Function)
	
$(document).on("click", "#tab-external", function(){
	window.open($("#input-url").val());
});

// Open Google Results in separate window (Click Function)
	
$(document).on("click", "#google-external", function(){
	currentSong.artist = $("#input-artist").val();
	currentSong.title = $("#input-title").val();
	var linkType = $("input[name=linkType]:checked").val();
	window.open("http://www.google.com/search?q="+currentSong.artist+"+"+currentSong.title+"+"+linkType);
});
	

// Trim on blur

$(".form-control").blur(function(){
	var newval = $.trim($(this).val());
	$(this).val(newval);
});

// Status Alert Bar

function StatusAlert(msg){
	//$("#status-bar").append(msg);
}

	
// Get Songs
  
function getSongs(selectedArtist) {
	
	$("#songs-container").empty();
	
	// Print all songs associated with the artist
	
	$.each( json, function(i, item) {

		if(selectedArtist.toLowerCase() == json[i].artist.toLowerCase()){
			appendSong(json[i].title.toLowerCase(), json[i].url.toLowerCase(), json[i].id);
			songCount++;
		}
		
	});
	highlightArtist(selectedArtist);
}

// Append Song to List

function appendSong(title, url, id){
	var str = "<div data-id='"+id+"' data-url='"+url+"' data-title='"+replaceSpaces(title)+"' class='row title-row'>";
	str += "<div class='col-xs-9 title' id='"+title+"'><a target='_blank' href='"+url+"'>"+title+"</a></div>";
	str += "<div class='col-xs-1 song-button edit-button'><span class='helper'></span><img title='Edit' src='icons/edit45.png' /></div>";
	str += "<div  class='col-xs-1 song-button delete-song'><span class='helper'></span><img title='Delete' src='icons/delete81.png' /></div>";
	str += "<div class='col-xs-1 song-button youtube-button'><span class='helper'></span><img title='Search YouTube' src='icons/youtube28.png' /></div>";
	str += "</div>";
	
	$("#songs-container").append(str);

}
   
// StartUp
  
function startUp(json) {
	var lastArtist = '';
	$.each( json, function(i, item) {
		var artist = json[i].artist.toLowerCase();
		var artistNoSpaces = replaceSpaces(artist);
		if(artist != lastArtist){
			$("#artists-container").append("<div class='artist' id='"+artistNoSpaces+"'>"+artist+"</div>");	
		}
		lastArtist = artist;
	});
}
  
// Get Artists (Ajax)
  
function getArtists() {
	
	$.ajax({                                      
		url: 'getartists.php',                    
		data: "",
		dataType: 'json',   
		success: function(data) {
			$("#artists-container").empty();
			json = data;
			var lastArtist = '';
			$.each( json, function(i, item) {
				var artist = json[i].artist.toLowerCase();
				var artistNoSpaces = replaceSpaces(artist);
					if(artist != lastArtist){
					$("#artists-container").append("<div class='artist' id='"+artistNoSpaces+"'>"+artist+"</div>");	
			}
		lastArtist = artist;
			});
			if(currentSong.artist != 0){
				getSongs(currentSong.artist);
			}
		} 
	});
}

function highlightArtist(artist){
	$("#"+replaceSpaces(artist)).css("background-color", "#DFBA69");
}
  
function addHttp(url) {
   if (!/^(f|ht)tps?:\/\//i.test(url)) {
      url = "http://" + url;
   }
   return url;
}

// Get YouTube Video

function getVideo(artist, title){
	$.ajax({
		dataType: "json",
		url: "https://www.googleapis.com/youtube/v3/search",
		data: "part=snippet&q="+artist+"+"+title+"&type=video&key=AIzaSyAUjB7bVO25PU-ie6B2w3K1fV0wF4eldl0",
		success: function(data) {
			youtuberesult = data;
			var j = 1;
			$.each(youtuberesult.items, function(i, item) {
				$("#video"+j).html("<iframe width='300' height='180' frameborder='no' src='//www.youtube.com/embed/"+item.id.videoId+"' allowfullscreen></iframe>");
				j++;
			});
			$("#video-tray").show();
			$("#video-hide-button").show();
			$("#video-hide-button").html("<small>hide video tray</small>");
			showvideo = 1;
		}
	});
}

// Fix main container to 100% of window height

function resizeContainer(offset){
	var wdo = $(document).height();
	wdo = wdo-offset-5;
	wdo = wdo + "px";
	$(".container").css("height",wdo);
}

// Get Google Search Results

function getGoogle(artist, title, type){
	$.ajax({
		dataType: "jsonp",
		url: "http://ajax.googleapis.com/ajax/services/search/web",
		data: "q="+artist+"+"+title+"+"+type+"&v=1.0&key=AIzaSyAUjB7bVO25PU-ie6B2w3K1fV0wF4eldl0",
		success: function(data) {
			googleresult = data;
			for(i=0; i<4; i++){
				if(googleresult.responseData.results[i].visibleUrl != "www.youtube.com"){
					tabLink = googleresult.responseData.results[i].url;
					$("#input-url").val(tabLink);
					break;
				}
			}
		}
	});
}

// Replace spaces with dashes

function replaceSpaces(text){
	str = text.replace(/\s+/g, '-');
	return str;
}

// Replace dashes with spaces

function replaceDashes(text){
	str = text.replace(/-/g, ' ');
	return str;
}

// Select all on focus

$("#input-url").focus(function() {
    var $this = $(this);
    $this.select();
    // Work around Chrome's little problem
    $this.mouseup(function() {
        // Prevent further mouseup intervention
        $this.unbind("mouseup");
        return false;
    });
});

</script>

</body>

</html>