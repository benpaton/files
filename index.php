<?php
// HC SVNT DRACONES
//Warning this script is pretty unrefined although it does work; it's far from object orientated. All the code is on one page on purpose.

//start session
session_start();

//set username and password
$user = "root";
$pass = "password";
	
//delete error files if they exist so they don't show in the dropbox (should probably stop them being created in the first place)
if (file_exists("error_log")) {
	unlink("error_log");
}
if (file_exists("500.shtml")) {
	unlink("500.shtml");
}

//set vars
$directoryPath = $_SERVER['DOCUMENT_ROOT'];
$upload = $_POST['upload'];
$delete = $_POST['delete'];
$uploadedFiles = $_FILES['userfile'];
$fileURL = $_POST['fileURL'];
$download = $_POST['download'];
$rename = $_POST['rename'];
$fileToRename = $_POST['fileToRename'];
$newFileName = $_POST['newFileName'];
$login = $_POST['login'];
$logout = $_GET['logout'];
$password = $_POST['password'];
$_SESSION['username'] = $_POST['username'];

//function to get the current url
function curPageURL() {
	$pageURL = 'http';
	if ($_SERVER["HTTPS"] == "on") {
		$pageURL .= "s";
	}
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	} else {
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}
	return $pageURL;
}
$domain = curPageURL();
$domain = strtok($domain, '?'); //remove query string from url and refresh value i.e. ?r=1234
$domainBase = basename($domain);

//login script (set loggedIn var in session if username and password match)
if ((isset($login)) && $_SESSION['username'] == $user && $password == $pass) {
	$_SESSION['loggedIn'] = true;
} else if ((isset($login)) && $_SESSION['username'] != $user && $password != $pass) {
	$loginFail = true;
}

//logout script (destory session if logout is set)
if ((isset($logout))) {
	session_destroy(); 
	header('Location: ' . $domain);
}

//random number for refresh
$randNum = rand(1000,9999);

// ################# delete the checked files #################
if ($delete == true && (isset($_SESSION['loggedIn']))) {
	foreach($_POST as $fileToDelete) {
		if ($fileToDelete != "true" && $fileToDelete != $password) { 
			unlink($fileToDelete);
			$deletedFilesArray[] = $fileToDelete;
		}
	}
	
	//compile appropriate string to say the file(s) have been deleted depending if more than 1 file has been deleted or not
	if (count($deletedFilesArray) >1 ) {
		$deletedFileString = "<p class=\"successMessage\">The files " . implode(", ", $deletedFilesArray) . " have been deleted. <a href=\"?r=" . $randNum . "\">Refresh.</a></p>";
	} else {
		$deletedFileString = "<p class=\"successMessage\">The file " . implode(", ", $deletedFilesArray) . " has been deleted. <a href=\"?r=" . $randNum . "\">Refresh.</a></p>";
	}
	
	$fileHasBeenDeleted = true;
	//header('Location: ' . $domain);
} else if ($delete == true && (!isset($_SESSION['loggedIn']))) {
	$youMustBeLoggedInToDoThat = true;	
}

//function to format filesizes
function format_bytes($a_bytes) {
    if ($a_bytes < 1024) {
        return $a_bytes .' B';
    } elseif ($a_bytes < 1048576) {
        return round($a_bytes / 1024, 2) .' Kb';
    } elseif ($a_bytes < 1073741824) {
        return round($a_bytes / 1048576, 2) . ' Mb';
    } elseif ($a_bytes < 1099511627776) {
        return round($a_bytes / 1073741824, 2) . ' Gb';
    } elseif ($a_bytes < 1125899906842624) {
        return round($a_bytes / 1099511627776, 2) .' Tb';
    } elseif ($a_bytes < 1152921504606846976) {
        return round($a_bytes / 1125899906842624, 2) .' Pb';
    } elseif ($a_bytes < 1180591620717411303424) {
        return round($a_bytes / 1152921504606846976, 2) .' Eb';
    } elseif ($a_bytes < 1208925819614629174706176) {
        return round($a_bytes / 1180591620717411303424, 2) .' Zb';
    } else {
        return round($a_bytes / 1208925819614629174706176, 2) .' Yb';
    }
}

// ################# Function to get directory info including total filesize and number of files #################
function getDirectorySize($path)
{
  $totalsize = 0;
  $totalcount = 0;
  $dircount = 0;
  if($handle = opendir($path))
  {
    while (false !== ($file = readdir($handle)))
    {
      $nextpath = $path . '/' . $file;
      if($file != '.' && $file != '..' && !is_link ($nextpath))
      {
        if(is_dir($nextpath))
        {
          $dircount++;
          $result = getDirectorySize($nextpath);
          $totalsize += $result['size'];
          $totalcount += $result['count'];
          $dircount += $result['dircount'];
        }
        else if(is_file ($nextpath))
        {
          $totalsize += filesize ($nextpath);
          $totalcount++;
        }
      }
    }
  }
  closedir($handle);
  $total['size'] = $totalsize;
  $total['count'] = $totalcount;
  $total['dircount'] = $dircount;
  return $total;
}

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<link rel="shortcut icon" href="favicon.ico" />
<title><?php echo $domainBase; ?></title>
<style type="text/css">
/* Reset CSS */
html, body, div, ul, ol, li, dl, dt, dd, form, fieldset, label, input, textarea, p, h1, h2, h3, h4, h5, h6, pre, code, blockquote, hr, th, td {
	margin:0px;
	padding:0px;
}
/* Clearfix CSS */
.clearfix:after {
    content: ".";
    display: block;
    clear: both;
    visibility: hidden;
    line-height: 0;
    height: 0;
}
.clearfix {
    display: inline-block;
}
html[xmlns] .clearfix {
    display: block;
}
* html .clearfix {
    height: 1%;
}
body {
	color:#333333;
	font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
	height:100%;
	background-color:#FFFFFF;
	padding:20px;
	margin:0;
}
.clear {
	clear:both;
}
.left {
	float:left;
}
.right {
	float:right;
}
img {
	border:none;
}
h1 {
	font-size:1.3em;
	font-weight:bold;
}
h2 {
	margin-bottom:30px;
	font-size:1em;
	font-weight:bold;
}
p {
	margin-bottom:30px;
	font-size:0.75em;
}
ul {
	margin-bottom:30px;
}
ul li {
	margin:0 0 10px 20px;
	font-size:0.8em;
	list-style-type:none;
}
#selectall {
	font-size:0.9em;
}
a {
	text-decoration:none;
}
a:hover {
	text-decoration:underline;
}
#controls div {
	float:left;	
	cursor:pointer;
	margin:40px 20px 40px 0;
	font-size:1.2em;
}
#controls div a,
#controls div a:hover {
	text-decoration:none;
	color:inherit;
}
#controls div:hover {
	opacity:0.5;
}
#renameFile,
#upload,
#serverToServerTransfer,
#login {
    background-color:#FFFFFF;
    border:3px solid #000000;
	border-radius:15px;
    color:#000000;
    display:block;
    height:50%;
    left:25%;
    overflow:auto;
    padding:10px;
    position:absolute;
    top:50px;
    width:50%;
    z-index:1002;
}
p.close {
	width:15px;
	height:15px;
	padding:2px;
	text-align:center;
	background-color:#FF0000;
	border:1px solid #FF0000;
	border-radius:8px;
	cursor:pointer;
}
p.close:hover {
	border:1px solid #C0C0C0;
	background-color:#C0C0C0;
}
p.close a {
	color:#FFFFFF;
	text-decoration:none;
}
#loadingGif,
#loadingGifLogin {
	width:24px;
	height:24px;
	margin-top:120px;
	margin-left:auto;
	margin-right:auto;
}
.successMessage {
    background-color:#66FF66;
    border:3px solid #33CC33;
	border-radius:15px;
    color:#000000;
    display:block;
    left:25%;
    overflow:auto;
    padding:10px;
    position:absolute;
    top:50px;
    width:50%;
    z-index:1003;
}
.failureMessage {
    background-color:#FF0000;
    border:3px solid #D00000;
	border-radius:15px;
    color:#FFFFFF;
    display:block;
    left:25%;
    overflow:auto;
    padding:10px;
    position:absolute;
    top:50px;
    width:50%;
    z-index:1003;
}
#filesList li img {
	padding:0 8px;
}
#filesList li input.delete {
	margin-right:10px;
}
#javascriptRequired {
    background-color:#FFFFFF;
    color:#000000;
    display:block;
    overflow:auto;
    padding:10px;
    position:absolute;
    top:0;
	left:0;
    width:1366px;
	height:768px;
    z-index:9999;	
}
</style>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<script type="text/javascript">
$(function () {
	//close button for the overlays
	$('.close').click(function() {
				$('#upload').hide();
				$('#showUpload').css({ opacity: 1 });
				$('#serverToServerTransfer').hide();
				$('#showServerToServer').css({ opacity: 1 });
				$('#renameFile').hide();
				$('#showRename').css({ opacity: 1 });
				$('#login').hide();
				$('#showLogin').css({ opacity: 1 });
    });
    // upload toggle
	$('#upload').hide();
    $('#showUpload').toggle(function() {
				$('#upload').show();
				$('#serverToServerTransfer').hide();
				$('#showServerToServer').css({ opacity: 1 });
				$('#renameFile').hide();
				$('#showRename').css({ opacity: 1 });
				$('#showUpload').css({ opacity: 0.5 });
				$('#login').hide();
				$('#showLogin').css({ opacity: 1 });
          },function(){
				$('#upload').hide();
				$('#showUpload').css({ opacity: 1 });
    });
	// server to server toggle
	$('#serverToServerTransfer').hide();
    $('#showServerToServer').toggle(function() {
				$('#serverToServerTransfer').show();
				$('#upload').hide();
				$('#showUpload').css({ opacity: 1 });
				$('#renameFile').hide();
				$('#showRename').css({ opacity: 1 });
				$('#showServerToServer').css({ opacity: 0.5 });
				$('#login').hide();
				$('#showLogin').css({ opacity: 1 });
          },function(){
				$('#serverToServerTransfer').hide();
				$('#showServerToServer').css({ opacity: 1 });
    });
	// rename toggle
	$('#renameFile').hide();
    $('#showRename').toggle(function() {
				$('#renameFile').show();
				$('#serverToServerTransfer').hide();
				$('#showServerToServer').css({ opacity: 1 });
				$('#upload').hide();
				$('#showUpload').css({ opacity: 1 });
				$("#showRename").css({ opacity: 0.5 });
				$('#login').hide();
				$('#showLogin').css({ opacity: 1 });
          },function(){
				$('#renameFile').hide();
				$('#showRename').css({ opacity: 1 });
    });
	//login toggle
	$('#login').hide();
    $('#showLogin').toggle(function() {
				$('#login').show();
				$("#showLogin").css({ opacity: 0.5 });
				$('#serverToServerTransfer').hide();
				$('#showServerToServer').css({ opacity: 1 });
				$('#upload').hide();
				$('#showUpload').css({ opacity: 1 });
				$('#renameFile').hide();
				$('#showRename').css({ opacity: 1 });
          },function(){
				$('#login').hide();
				$('#showLogin').css({ opacity: 1 });
    });
	// delete toggle
	$('.delete').hide();
    $('#showDelete').toggle(function() {
				$('.delete').show();
				$('#showDelete').css({ opacity: 0.5 });
          },function(){
				$('.delete').hide();
				$('#showDelete').css({ opacity: 1 });
    });
	//select all toggle
	$('#selectall').toggle(
        function() {
            $('#filesList .delete').prop('checked', true);
			$(this).html('Deselect all');
        },
        function() {
            $('#filesList .delete').prop('checked', false);
			$(this).html('Select all');
        }
    );
	//clone file upload box
	$('#add-more-files').click(function() {
		var cloned = $(this).parent().prev().clone();
		cloned.val(null);
		$(cloned).insertBefore($(this).parent());
    });
	//hide loadingGif on initial page load
	$('#loadingGif').hide();
	$('#uploadingTitle').hide();
	$('#loadingGifLogin').hide();
	$('#loggingInTitle').hide();
	
	//login failure message fade out after display
	$('.failureMessage').fadeOut(2000);
	
});
//function to hide upload form, show ajax loading gif when upload button is clicked and turn the border colour to green
function hideUpload() {
	document.getElementById("hideContentForLoadingGifToShow").style.display = "none";
	document.getElementById("uploadingTitle").style.display = "block";
	document.getElementById("loadingGif").style.display = "block";
	document.getElementById("upload").style.border = "3px solid #33CC33";
}
function hideLogin() {
	document.getElementById("hideContentForLoadingGifToShowLogin").style.display = "none";
	document.getElementById("loggingInTitle").style.display = "block";
	document.getElementById("loadingGifLogin").style.display = "block";
	document.getElementById("login").style.border = "3px solid #33CC33";
}
</script>
</head>
<body>
<?php
// ################# List all the files in the directory #################
$fullPath = $directoryPath . '/' . $domainBase;
//echo $fullPath;
$directoryInfo = getDirectorySize($fullPath);
$directoryInfo['size'] = $directoryInfo['size'] - 126945.28; //minus the difference to make the indvidual files and total tie up
//reset directory size to 0 if less than 1Kb
if ($directoryInfo['size'] < 1024) {
	$directoryInfo['size'] = 0;
}
$directorySize = format_bytes($directoryInfo['size']);

//get all files
$files = glob($directory . "*.*");
 
//count the number of files
$numOfFiles = count($files);

//number of files without the index.php and favicon.ico
$numOfFilesWithoutIndexAndIco = $numOfFiles - 2;
?>

<h1>Dropbox<?php if ($numOfFiles > 1) { echo " - {$numOfFilesWithoutIndexAndIco} files available ({$directorySize})"; } ?></h1>

<?php //icons are from http://www.iconfinder.com/search/?q=iconset%3Aledicons ?>

<div id="controls"><? if ($numOfFiles > 1) { ?><div id="showDelete"><img src="<?php echo $domain ?>icons/delete.png" alt="delete" /> Delete</div><?php } ?> <div id="showUpload"><img src="<?php echo $domain ?>icons/upload.png" alt="upload" /> Upload</div> <div id="showServerToServer"><img src="<?php echo $domain ?>icons/server.png" alt="transfer" /> Transfer</div> <div id="showRename"><img src="<?php echo $domain ?>icons/rename.png" alt="rename" /> Rename</div> <div><a href="<?php echo "?r=" . $randNum; ?>"><img src="<?php echo $domain ?>icons/refresh.png" alt="refresh" /> Refresh</a></div> <?php if (isset($_SESSION['loggedIn'])) { echo "<div><img src=\"" . $domain . "icons/login.png\" alt=\"logout\" /> <a href=\"?logout=true\">Logout</a></div>"; } else { ?><div id="showLogin"><img src="<?php echo $domain ?>icons/login.png" alt="login" /> Login</div><?php } ?></div>

<div class="clear"></div>

<?php
// ################# Upload the files #################

// Where the file is going to be placed 
$target_path = "";

// ################# Display the saved files #################

//only list out the files if there is more than 1 file in the folder (more than just index.php)
if ($numOfFiles > 1) {
 
	//list files with checkboxes for delete
	echo "<form name=\"fileList\" method=\"post\">";
	echo "<input type=\"hidden\" name=\"delete\" value=\"true\" />";
	echo "<ul id=\"filesList\">";
	
	//interger count
	$i = 0;

	//print each file name except index.php and favicon.ico with checkboxes for delete
	foreach($files as $file) {
		if ($file != "index.php" && $file != "favicon.ico") {
			
			//check which filetype and select appropriate icon
			if (strpos($file,'zip') !== false) {
				$fileIcon = $domain . 'icons/zip.png';
				$fileAlt = 'zip file icon';
			} else if (strpos($file,'pdf') !== false) {
				$fileIcon = $domain . 'icons/pdf.png';
				$fileAlt = 'pdf file icon';
			} else if (strpos($file,'jpg') !== false) {
				$fileIcon = $domain . 'icons/image.png';
				$fileAlt = 'jpg file icon';
			} else if (strpos($file,'png') !== false) {
				$fileIcon = $domain . 'icons/image.png';
				$fileAlt = 'png file icon';
			} else if (strpos($file,'gif') !== false) {
				$fileIcon = $domain . 'icons/image.png';
				$fileAlt = 'gif file icon';
			} else {
				$fileIcon = $domain . 'icons/doc.png';
				$fileAlt = 'file icon';
			}
			
			//compile url of the file and encode so spaces become %20 etc
			$urlOfFile = rawurlencode($file);
			
			//write out the li with the link to the file
			$fileNum = $i + 1;
			echo "<li><input type=\"checkbox\" name=\"delete{$i}\" value=\"{$file}\" class=\"delete\" />{$fileNum}. <a href=\"{$urlOfFile}\"><img src=\"{$fileIcon}\" alt=\"{$fileAlt}\" />{$domain}{$file}</a> - " . format_bytes(filesize($file)) . " </li>";
			$i++;
		}
	}
	echo "<li class=\"delete\"><a href=\"\" id=\"selectall\">Select all</a></li>";
	echo "<li class=\"delete\"><input type=\"submit\" value=\"Delete selected file(s)\" /></li>";
	echo "</ul>";
	echo "</form>";
}

// ################# Rename a file  on the server #################

//rename a file  on the server if the form has been submitted and rename is set to true
if ($rename == true && (isset($_SESSION['loggedIn']))) {
	rename ($fileToRename , $newFileName);
	$fileRenameHasHappened = true;
} else if ($rename == true && (!isset($_SESSION['loggedIn']))) {
	$youMustBeLoggedInToDoThat = true;
}

// ################# Download a file to the server #################

//download the file to the server if the form has been submitted and download is set to true
if ($download == true && (isset($_SESSION['loggedIn']))) {
	$url = $fileURL;
	$fh = fopen(basename($url), "wb");
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_FILE, $fh);
	curl_exec($ch);
	curl_close($ch);

	$fileDownloaded = true;
} else if ($download == true && (!isset($_SESSION['loggedIn']))) {
	$youMustBeLoggedInToDoThat = true;	
}
?>

<div id="upload">
	<h2 id="uploadingTitle">Uploading</h2>
	<div id="loadingGif"><img src="<?php $domain ?>icons/ajax-loader.gif" alt="loading" /></div>
	<div id="hideContentForLoadingGifToShow">
		<h2 class="left">Upload new file:</h2>
		<p class="right close"><a href="javascript:void(0)">X</a></p>
		<div class="clear"></div>
		<form enctype="multipart/form-data" method="post">
			<input type="hidden" name="upload" value="true" />
			<ul>
				<li>Choose a file to upload:</li>
				<li><input name="userfile[]" type="file" /></li>
				<li><a href="javascript: void(0);" id="add-more-files">Add another file upload box</a></li>
				<li><input type="submit" value="Upload file(s)" onClick="hideUpload()" /></li>
			</ul>
		</form>
	</div><!-- closes #hideContentForLoadingGifToShow -->
</div><!-- closes #upload -->

<?php if ($fileDownloaded != true) { ?>
<div id="serverToServerTransfer">
    <h2 class="left">Transfer file from another server:</h2>
    <p class="right close"><a href="javascript:void(0)">X</a></p>
    <div class="clear"></div>
	<form enctype="multipart/form-data" method="post">
    	<input type="hidden" name="download" value="true" />
        <ul>
			<li>Choose a file to download to the server from another server:</li>
			<li><input name="fileURL" id="fileURL" type="text" size="40" value="http://" /></li>
			<li><input type="submit" value="Transfer file" /></li>
		</ul>
	</form>
</div><!-- closes #serverToServerTransfer -->
<?php } else { ?>

	<p class="successMessage">File downloaded: <?php echo $fileURL; ?>  <a href="?r=<?php echo $randNum; ?> ">Refresh.</a></p>

<?php } ?>

<div id="renameFile">
    <h2 class="left">Rename a file:</h2>
    <p class="right close"><a href="javascript:void(0)">X</a></p>
    <div class="clear"></div>
	<form enctype="multipart/form-data" method="post">
    	<input type="hidden" name="rename" value="true" />
        <ul>
			<li>Enter file to be renamed:</li>
			<li><input name="fileToRename" id="fileToRename" type="text" size="40" /></li>
			<li>Enter new name:</li>
            <li><input name="newFileName" id="newFileName" type="text" size="40" /></li>
			<li><input type="submit" value="Rename file" /></li>
		</ul>
    </form>
</div><!-- closes #renameFile -->

<?php
//echo message if a file has been renamed
if ($fileRenameHasHappened == true) {
	echo "<p class=\"successMessage\">File {$fileToRename} renamed to {$newFileName}. <a href=\"?r={$randNum}\">Refresh.</a></p>"; 
}
?>

<?php
//upload the files
$margin = 0;
$refreshLink = "<a href=\"?r={$randNum}\">Refresh.</a>";
if ($upload == true && (isset($_SESSION['loggedIn']))) {
	foreach(array_combine($uploadedFiles["name"], $uploadedFiles["tmp_name"]) as $name => $tmp_name) {
		$target_path = $base_path .$name; 
	
		if(move_uploaded_file($tmp_name, $target_path)) {
			echo "<p class=\"successMessage\" style=\"margin-top:{$margin}px\">The file " . $name . " has been uploaded. {$refreshLink}</p>";
			$margin =+ 60;
			$refreshLink = null;
		}
		else
		{
			echo "<p>There was an error uploading the {$name}, please try again!</p>";
		}
	}
} else if ($upload == true && (!isset($_SESSION['loggedIn']))) {
	$youMustBeLoggedInToDoThat = true;
}
?>

<?php
//output the delete file text
if ($fileHasBeenDeleted == true) {
	echo $deletedFileString;
}
?>

<div id="login">
	<h2 id="loggingInTitle">Logging in</h2>
	<div id="loadingGifLogin"><img src="<?php $domain ?>icons/ajax-loader.gif" alt="loading" /></div>
    <div id="hideContentForLoadingGifToShowLogin">
        <h2 class="left">Login:</h2>
        <p class="right close"><a href="javascript:void(0)">X</a></p>
        <div class="clear"></div>
        <form enctype="multipart/form-data" method="post">
            <input type="hidden" name="login" value="true" />
            <ul>
                <li>Enter username:</li>
                <li><input name="username" id="username" type="text" size="40" /></li>
                <li>Enter password:</li>
                <li><input name="password" type="password" size="40" /></li>
                <li><input type="submit" value="Login" onClick="hideLogin()" /></li>
            </ul>
        </form>
    </div><!-- closes #hideContentForLoadingGifToShowLogin -->
</div><!-- closes #login -->

<?php
//output login fail message
if ($loginFail == true) {
	echo "<p class=\"failureMessage\">Your login was unsuccessful!</p>";
}
?>

<?php
//output you must be logged in to do that
if ($youMustBeLoggedInToDoThat == true) {
	echo "<p class=\"failureMessage\">You must be logged in to do that!</p>";
}
?>

<noscript>
	<div id="javascriptRequired">JavaScript required to display this site.</div>
<noscript/>
</body>
</html>