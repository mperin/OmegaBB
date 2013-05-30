<?php
/*OmegaBB 0.9.3*/
@$ftmp = $_FILES['file']['tmp_name'];
@$oname = $_FILES['file']['name'];
@$fname = $_FILES['file']['name'];
@$fsize = $_FILES['file']['size'];
@$ftype = $_FILES['file']['type'];

include('omegabb.php');

function filename_check(&$filename) {
	$bad_chars = array("'", "\\", ' ', '/', ':', '*', '?', '"', '<', '>', '|');
	if ($filename != "") {
	   $filename = str_replace($bad_chars, '_', $filename);
	}
}

function add_file($user_id,$thread_id,$forum_id,$filename,$mime_type){
   global $settings;

   $cur = perform_query("select * from file where author_id=$user_id and post_id < 1 and is_deleted = 0 and avatar_number is null and thread_id != $thread_id",MULTISELECT);
   while ($row = mysql_fetch_array( $cur )) {  
	  unlink("files/tmp/from_" . $row["author_id"]."_".$row["file_id"]);  
	  unlink("files/tmp/from_" . $row["author_id"]."_t_".$row["file_id"]);  
	  perform_query("update file set is_deleted=2 where file_id=".$row["file_id"],UPDATE); 
   }   
   
   $row=perform_query("SELECT count( * ) as total_record FROM file where author_id=$user_id and post_id < 1 and is_deleted = 0 and avatar_number is null",SELECT);
   if ($row->total_record >= $settings->max_file_attachments) {
      return -1;
   }
   
   $q = "insert file "
    		. "\n set "
     		. "\n  post_id=0,"   					
    		. "\n  author_id='" . $user_id . "',"
    		. "\n  ip_address='" . $_SERVER['REMOTE_ADDR'] . "',"
    		. "\n  filename='" . mysql_real_escape_string($filename) . "',"		
			. "\n  mime_type='" . $mime_type . "',"
     		. "\n  file_type='0',"   						
    		. "\n  forum_id='" . $forum_id . "',"			
    		. "\n  thread_id='" . $thread_id . "'";
    	
    $file_id = perform_query($q,INSERT);            			
    return $file_id;
}

function add_file2($user_id,$forum_id,$filename,$mime_type){
   global $settings;

   $cur = perform_query("select * from file where author_id=$user_id and post_id = 0 and is_deleted = 0 and avatar_number is null",MULTISELECT);
   while ($row = mysql_fetch_array( $cur )) {  
	  unlink("files/tmp/from_" . $row["author_id"]."_".$row["file_id"]);  
	  unlink("files/tmp/from_" . $row["author_id"]."_t_".$row["file_id"]);  
	  perform_query("update file set is_deleted=2 where file_id=".$row["file_id"],UPDATE); 
   }   
   
   $row=perform_query("SELECT count( * ) as total_record FROM file where author_id=$user_id and post_id < 1 and is_deleted = 0 and avatar_number is null",SELECT);
   if ($row->total_record >= $settings->max_file_attachments) {
      return -1;
   }
   
   $q = "insert file "
    		. "\n set "
    		. "\n  author_id='" . $user_id . "',"
     		. "\n  thread_id='-1',"   		
     		. "\n  post_id='-1',"   			
    		. "\n  ip_address='" . $_SERVER['REMOTE_ADDR'] . "',"
    		. "\n  filename='" . mysql_real_escape_string($filename) . "',"			
			. "\n  mime_type='" . $mime_type . "',"			
     		. "\n  file_type='0',"   						
    		. "\n  forum_id='" . $forum_id . "'";
    	
    $file_id = perform_query($q,INSERT);            			
    return $file_id;
}

function add_image($user_id,$thread_id,$forum_id,$filename,$mime_type){
   global $settings;
   
   $cur = perform_query("select * from file where author_id=$user_id and post_id < 1 and is_deleted = 0 and avatar_number is null and thread_id != $thread_id",MULTISELECT);
   while ($row = mysql_fetch_array( $cur )) {  
	  unlink("files/tmp/from_" . $row["author_id"]."_".$row["file_id"]);  
	  unlink("files/tmp/from_" . $row["author_id"]."_t_".$row["file_id"]);  
	  perform_query("update file set is_deleted=2 where file_id=".$row["file_id"],UPDATE); 
   }   
   
   $row=perform_query("SELECT count( * ) as total_record FROM file where author_id=$user_id and post_id < 1 and is_deleted = 0 and avatar_number is null",SELECT);
   if ($row->total_record >= $settings->max_file_attachments) {
      return -1;
   }
   
   $q = "insert file "
    		. "\n set "
     		. "\n  post_id=0,"   					
    		. "\n  author_id='" . $user_id . "',"
    		. "\n  ip_address='" . $_SERVER['REMOTE_ADDR'] . "',"
    		. "\n  filename='" . mysql_real_escape_string($filename) . "',"		
			. "\n  mime_type='" . $mime_type . "',"			
     		. "\n  file_type='1',"   					
     		. "\n  forum_id='" . $forum_id . "',"   					
    		. "\n  thread_id='" . $thread_id . "'";
    	
    $file_id = perform_query($q,INSERT);            			
    return $file_id;
}

function add_image2($user_id,$forum_id,$filename,$mime_type){
   global $settings;
   
   $cur = perform_query("select * from file where author_id=$user_id and post_id = 0 and is_deleted = 0 and avatar_number is null",MULTISELECT);
   while ($row = mysql_fetch_array( $cur )) {  
	  unlink("files/tmp/from_" . $row["author_id"]."_".$row["file_id"]);  
	  unlink("files/tmp/from_" . $row["author_id"]."_t_".$row["file_id"]);  
	  perform_query("update file set is_deleted=2 where file_id=".$row["file_id"],UPDATE); 
   }   
   
   $row=perform_query("SELECT count( * ) as total_record FROM file where author_id=$user_id and post_id < 1 and is_deleted = 0 and avatar_number is null",SELECT);
   if ($row->total_record >= $settings->max_file_attachments) {
      return -1;
   }
   
   $q = "insert file "
    		. "\n set "
    		. "\n  author_id='" . $user_id . "',"
     		. "\n  thread_id='-1',"   		
     		. "\n  post_id='-1',"   			
    		. "\n  ip_address='" . $_SERVER['REMOTE_ADDR'] . "',"
    		. "\n  filename='" . mysql_real_escape_string($filename) . "',"			
			. "\n  mime_type='" . $mime_type . "',"			
     		. "\n  file_type='1',"   						
    		. "\n  forum_id='" . $forum_id . "'";
    	
    $file_id = perform_query($q,INSERT);            			
    return $file_id;
}

if(IsSet($ftmp)) :
do {
	$thread_id=GetParam($_REQUEST,'thread_id','');
	$forum_id=GetParam($_REQUEST,'forum_id','');	
	$file_id = 0;
	$ustatus = 1;    

    $user_id = Check_Auth();

	if (!($settings->file_upload_allowed)) {$ustatus = -1; $message = intext("File upload not permitted"); break;}
    if (($settings->status_to_upload_file > GetStatus($user_id))) {$ustatus = -2; $message = intext("File upload not permitted"); break;}	   
    if ($user_id <= 0) {$ustatus = -3; $message = intext("Not signed in"); break;} 
	if ($msg = IsBanned($user_id)) {$ustatus = -4; $message = intext("File upload not permitted").". ".$msg; break;}	
	if ($fsize > $settings->max_uploaded_file_size) {$ustatus = -5; $message = intext("File is too large"); break;}

	$mime_type = mime_check($_FILES['file']['name'],$_FILES['file']['tmp_name']);

	if ($mime_type == -1) {$ustatus = -6; $message = intext("Invalid file type"); break;}

	if ((preg_match('/^image\//',$mime_type))) {$is_image = true;} else {$is_image = false;}

	if ($settings->strip_exif && preg_match('/^image\/jpeg/',$mime_type)) {
		$img = imagecreatefromjpeg ($_FILES['file']['tmp_name']);
		imagejpeg ($img, $_FILES['file']['tmp_name'], 100);
		imagedestroy ($img);
	}
	
    filename_check($fname);

	if ($is_image && $settings->thumbnail_uploaded_images) {
	   if ($thread_id) {
		  if (IsBannedFromThisThread($user_id,$thread_id)) {$ustatus = -7; $message = intext("You are banned from this thread"); break;}
		  if (!IsValidThread($thread_id,1)) {$ustatus = -8; $message = intext("Invalid thread"); break;}
          $row = perform_query("select forum_id, block_allow_list from thread where thread_id='". $thread_id."'", SELECT);
		  if (($row->forum_id == 12) && (!preg_match('/,' . $user_id . ';/',$row->block_allow_list))) {$ustatus = -26; $message = intext("Invalid thread"); break;} 
          if (!($settings->file_upload_in_pt_allowed) && ($row->forum_id == 12)) {$ustatus = -9; $message = intext("File upload not permitted"); break;}				  
		  $file_id = add_image($user_id,$thread_id,$row->forum_id,$fname,$mime_type);
		  if  ($file_id == -1) {$ustatus = -10; $message = intext("Maximum number of file attachments reached"); break;}	
	   } else if ($forum_id) {
		  if (!IsValidForum($forum_id)) {$ustatus = -11; $message = intext("Invalid thread"); break;}
		  if (($forum_id == 12) && !($settings->file_upload_in_pt_allowed)) {$ustatus = -12; $message = intext("File upload not permitted"); break;}	
		  if (($forum_id == 12) && ($settings->status_to_start_pt > GetStatus($user_id))) {$ustatus = -13; $message = intext("File upload not permitted"); break;}			  
		  $file_id = add_image2($user_id,$forum_id,$fname,$mime_type);
		  if  ($file_id == -1) {$ustatus = -14; $message = intext("Maximum number of file attachments reached"); break;}	
	   } else {
		  $ustatus = -15; break;
	   }

	   $im = thumbnail($_FILES['file']['tmp_name'], $settings->thumb_width, $settings->thumb_height, 0);
	   if ($im) {
		  imageToFile($mime_type, $im, 'files/tmp/from_' . $user_id . '_t_' . $file_id);
	   } 

	   $uploadfile = getcwd() . '/files/tmp/' . 'from_' . $user_id . '_' . $file_id;

	   if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
		   chmod($uploadfile, 0600);
		   if ($im) {
			  chmod(getcwd() . '/files/tmp/from_' . $user_id . '_t_' . $file_id, 0600);
		   }		  
	   } else {
		  $ustatus = -16; break;
	   }
	} else {
	   if ($thread_id) {
		  if (IsBannedFromThisThread($user_id,$thread_id)) {$ustatus = -17; $message = intext("You are banned from this thread"); break;}
		  if (!IsValidThread($thread_id,1)) {$ustatus = -18; $message = intext("Invalid thread"); break;}
          $row = perform_query("select forum_id, block_allow_list from thread where thread_id='". $thread_id."'", SELECT);
		  if (($row->forum_id == 12) && (!preg_match('/,' . $user_id . ';/',$row->block_allow_list))) {$ustatus = -26; $message = intext("Invalid thread"); break;} 
          if (!($settings->file_upload_in_pt_allowed) && ($row->forum_id == 12)) {$ustatus = -19; $message = intext("File upload not permitted"); break;}		
		  $file_id = add_file($user_id,$thread_id,$row->forum_id,$fname,$mime_type);
		  if  ($file_id == -1) {$ustatus = -20; $message = intext("Maximum number of file attachments reached"); break;}	
	   } else if ($forum_id) {
		  if (!IsValidForum($forum_id)) {$ustatus = -21; $message = intext("Invalid thread"); break;}
		  if (($forum_id == 12) && !($settings->file_upload_in_pt_allowed)) {$ustatus = -22; $message = intext("File upload not permitted"); break;}	
		  if (($forum_id == 12) && ($settings->status_to_start_pt > GetStatus($user_id))) {$ustatus = -23; $message = intext("File upload not permitted"); break;}	
		  $file_id = add_file2($user_id,$forum_id,$fname,$mime_type);  
		  if  ($file_id == -1) {$ustatus = -24; $message = intext("Maximum number of file attachments reached"); break;}	
	   } else {
		  $ustatus = -25; break;
	   }

	   $uploaddir = getcwd() . '/files/tmp/'; 
	   $uploadfile = $uploaddir . 'from_' . $user_id . '_' . $file_id;

	   if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
		  chmod($uploadfile, 0600); 
	   } else {
		  $ustatus = -26; break;
	   }
	}
} while(false);


?>

<html>
	<head>
		<script>
			var par = window.parent.document;
			var list = par.getElementById('list2');
			var fileid = par.createElement('div');
			var inpid = par.createElement('input');
			var imgdiv = list.getElementsByTagName('div')[<?php echo $_POST['imgnum']?>];
			var image = imgdiv.getElementsByTagName('img')[0];

			imgdiv.removeChild(image);
			list.removeChild(imgdiv); 

			if (<?php echo $ustatus?> == 1) {
			   fileid.setAttribute('id', 'upfile<?php echo $file_id?>'); 
			   fileid.innerHTML = "Uploaded <?php echo $oname?> sucessfully  (file_id:<?php echo $file_id?>)  (type:<?php echo $ftype?>) <?php echo uploadfile?> <?php echo user_id?>";

			   window.parent.cleanup(<?php echo $file_id . ",\"" . $fname . "\""?>);

			   inpid.type = 'hidden';
			   inpid.name = 'filename[]';
			   inpid.value = '<?php echo $file_id?>';
			   list.appendChild(fileid);
			   fileid.appendChild(inpid);
			} else {
			   window.parent.cleanup(<?php echo $ustatus?>,"<?php echo $message?>");
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
				var num = window.parent.document.getElementsByTagName('iframe').length;
				var iframe = par.getElementsByTagName('iframe')[num];

				// add image progress
				var list = par.getElementById('list2');
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
			<input id="file" type="file" name="file" onchange="upload();    " />
			<input type="hidden" name="imgnum" />
		</form>
	</body>	
</html>
