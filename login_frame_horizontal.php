<?php
/*OmegaBB 0.9.2*/
    include('config.php');
    include('common.php');
?>   

<html style="overflow-y:hidden;">
    <head>
        <title></title>
		<link id="main_css" href="<?php echo $settings->default_theme ?>" type="text/css" rel="stylesheet"> 
    </head>
    <body style="padding:0px;"> 
		<span style="font-size:12px;" class="message_center1"><?php echo intext("Name");?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo intext("Password");?></span>
		<br><form style="display: inline;" method="post" name="loginform">
			<input type="text" maxlength="100" size="12" id="username" name="username"/>
			<input type="password" maxlength="20" size="12" id="password" name="password"/>
			<span style="display:none;" style="font-size:12px;"><?php echo intext("Remember");?>: <input type="checkbox" name="rem" id="rem" value="rem2" checked /> </span>
			<input class="rtbutton" type="submit" onClick="javascript:window.parent.login();" value="<?php echo intext("Login");?>"> 
		</form>
	</body>
</html>