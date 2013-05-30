<?php
/*OmegaBB 0.9.3*/
@$ftmp = $_FILES['avatar']['tmp_name'];
@$oname = $_FILES['avatar']['name'];
@$fname = $_FILES['avatar']['name'];
@$fsize = $_FILES['avatar']['size'];
@$ftype = $_FILES['avatar']['type'];

if(IsSet($ftmp)) :
do {
    include('omegabb.php');
	$ustatus = 1;    
	$new_total_avatars = -1;

    $user_id = Check_Auth();

    if ($user_id <= 0) {$ustatus = -1; $message = intext("Not signed in"); break;}	
	if ($msg = IsBanned($user_id)) {$ustatus = -2; $message = intext("Avatar upload not permitted").". ".$msg; break;}
	if ($settings->avatars_allowed == 0 || $settings->allow_avatar_change == 0) {$ustatus = -3; $message = intext("Avatar upload not allowed"); break;}
	if ($settings->status_to_have_avatar > GetStatus($user_id)) {$ustatus = -4;  $message = intext("Avatar upload not permitted"); break;}
	if ($fsize > $settings->max_uploaded_file_size) {$ustatus = -5; $message = intext("File is too large"); break;}
	
	$mime_type = mime_check($_FILES['avatar']['name'],$_FILES['avatar']['tmp_name']);	
	if (!(preg_match('/^image\//',$mime_type))) {$ustatus = -6; $message = intext("Invalid file type"); break;}
	
	//unfortunately resizing bmps doesn't work at this time
	if (preg_match('/^image\/x-ms-bmp/',$mime_type)) {$ustatus = -7; $message = intext("Invalid file type"); break;}
		
	if ($settings->strip_exif && preg_match('/^image\/jpeg/',$mime_type)) {
		$img = imagecreatefromjpeg ($_FILES['avatar']['tmp_name']);
		imagejpeg ($img, $_FILES['avatar']['tmp_name'], 100);
		imagedestroy ($img);
	}
		
	$internal_id = md5(mt_rand());   

	$new_total_avatars = AddAvatar($user_id);
	
    $q = "insert file "
    		. "\n set "		
    		. "\n  author_id='" . $user_id . "',"
    		. "\n  ip_address='" . $_SERVER['REMOTE_ADDR'] . "',"
    		. "\n  filename='" . mysql_real_escape_string($fname) . "',"		
			. "\n  mime_type='" . $mime_type . "',"
     		. "\n  file_type='1',"   						
     		. "\n  avatar_number='$new_total_avatars',"   							
     		. "\n  internal_id='$internal_id';";   							
    	
    perform_query($q,INSERT);       	
	
	$uploadfile = getcwd() . "/files/avatar_".$user_id."_".$new_total_avatars."_".$internal_id;

	$im = thumbnail($_FILES['avatar']['tmp_name'], $settings->max_avatar_dimensions[0], $settings->max_avatar_dimensions[1], 1);
	if ($im) {
	   if (!imageToFile($mime_type, $im, $uploadfile)) {
		  $ustatus = -8; break;
	   }
	} else {
	   if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadfile)){
		  $ustatus = -9; break;
	   }
	}	   
} while (false);
?>

<html>
	<head>
		<script>
			var par = window.parent.document;
			var list = par.getElementById('list');
			var fileid = par.createElement('div');
			var inpid = par.createElement('input');
			var imgdiv = list.getElementsByTagName('div')[<?php echo $_POST['imgnum']?>];
			var image = imgdiv.getElementsByTagName('img')[0];

			imgdiv.removeChild(image);
			list.removeChild(imgdiv);

			if (<?php echo $ustatus?> == 1) {
			   window.parent.account_info.total_avatars = <?php echo $new_total_avatars?>;
			   window.parent.select_avatar(<?php echo $new_total_avatars?>);    
			} else {
			   window.parent.cleanup2(<?php echo $ustatus?>,"<?php echo $message?>");
			}
		</script>
	</head>
</html>

<?php
    exit();
endif;
?>

<html>
	<head>
		<script language="javascript">		
			function upload(){ 
				var par = window.parent.document;
				var num = par.getElementsByTagName('iframe').length - 1;
				var iframe = par.getElementsByTagName('iframe')[num];
				iframe.className = 'hidden';
				
				// create new iframe
				var new_iframe = par.createElement('iframe');
				new_iframe.src = 'upload_avatar.php';
				new_iframe.frameBorder = '0';
				new_iframe.setAttribute('style', 'height:50px;');
				par.getElementById('iframe').appendChild(new_iframe); 

				// add image progress
				var list = par.getElementById('list');
				var new_div = par.createElement('div');
				var new_img = par.createElement('img');
				new_img.src = 'img/indicator.gif';
				new_img.className = 'load';
				new_div.appendChild(new_img);
				list.appendChild(new_div);

				// send
				var imgnum = list.getElementsByTagName('div').length - 1;
				document.iform.imgnum.value = imgnum;
				document.iform.submit(); 
			}
		</script>
		<style>
			body {vertical-align:top;}
		</style>
	</head>
	<body>
		<form name="iform" action="" method="post" enctype="multipart/form-data">
			<input id="file" type="file" name="avatar" onchange="upload()" />
			<input type="hidden" name="imgnum" />
		</form>
	</body>
</html>
