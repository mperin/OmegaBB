<?php

/**
 * Project:     Securimage: A PHP class for creating and managing form CAPTCHA images<br />
 * File:        securimage_show.php<br />
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or any later version.<br /><br />
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.<br /><br />
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA<br /><br />
 *
 * Any modifications to the library should be indicated clearly in the source code
 * to inform users that the changes are not a part of the original software.<br /><br />
 *
 * If you found this script useful, please take a quick moment to rate it.<br />
 * http://www.hotscripts.com/rate/49400.html  Thanks.
 *
 * @link http://www.phpcaptcha.org Securimage PHP CAPTCHA
 * @link http://www.phpcaptcha.org/latest.zip Download Latest Version
 * @link http://www.phpcaptcha.org/Securimage_Docs/ Online Documentation
 * @copyright 2009 Drew Phillips
 * @author drew010 <drew@drew-phillips.com>
 * @version 2.0.1 BETA (December 6th, 2009)
 * @package Securimage
 *
 */
error_reporting(0);
@ini_set('display_errors', 0);

include 'securimage.php';
include('../config.php');

$img = new securimage();

$img->image_width = 150;
$img->code_length = 5;
$img->wordlist_file = 'omegabb_words.txt';

if ($settings->weak_captcha == 1) {
   $img->use_wordlist = true;
} else {
   $img->use_wordlist = false;
}

if ($settings->captcha_distortion == 1) {
   $img->perturbation = 0.4; 
} else if ($settings->captcha_distortion == 2) {
   $img->perturbation = 0.65; 
} else if ($settings->captcha_distortion == 3) {
   $img->perturbation = 0.9; 
}

$img->show(); 









