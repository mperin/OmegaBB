<?php
	/*OmegaBB*/
    include('config.php');
    include('common.php');
?>   

<html style="overflow-y:hidden;">
    <head>
        <title></title>
		<link id="main_css" href="<?php echo $settings->default_theme ?>" type="text/css" rel="stylesheet"> 
    </head>
    <body id="Panel" style="padding:0px;"> 
		<?php echo intext("Name or Number");?>:<br>
		<form method="post" name="loginform">
			<input type="text" maxlength="100" size="17" id="username" name="username"/>
			<?php echo intext("Password");?>:<br><input type="password" maxlength="20" size="17" id="password" name="password"/><br>
			<div style="font-size:12px;"><?php echo intext("Remember Me");?>: <input type="checkbox" name="rem" id="rem" value="rem2" checked /> </div>
			<input class="rtbutton" type="submit" onClick="javascript:window.parent.login();" value="<?php echo intext("Sign In");?>"> 
		</form>
	</body>
</html>