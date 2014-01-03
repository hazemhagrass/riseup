<?php
/* EDIT EMAIL AND PASSWORD */
$EMAIL      = "hazem.hagrass@yahoo.com";
$PASSWORD   = "confirmmymail";
function cURL($url, $header=NULL, $cookie=NULL, $p=NULL)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, $header);
    curl_setopt($ch, CURLOPT_NOBODY, $header);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    if ($p) {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $p);
    }
    $result = curl_exec($ch);
    if ($result) {
        return $result;
    } else {
        return curl_error($ch);
    }
    curl_close($ch);
}
$a = cURL("https://login.facebook.com/login.php?login_attempt=1",true,null,"email=$EMAIL&pass=$PASSWORD");
preg_match('%Set-Cookie: ([^;]+);%',$a,$b);
$c = cURL("https://login.facebook.com/login.php?login_attempt=1",true,$b[1],"email=$EMAIL&pass=$PASSWORD");
preg_match_all('%Set-Cookie: ([^;]+);%',$c,$d);
for($i=0;$i<count($d[0]);$i++)
    $cookie.=$d[1][$i].";";
/*
NOW TO JUST OPEN ANOTHER URL EDIT THE FIRST ARGUMENT OF THE FOLLOWING FUNCTION.
TO SEND SOME DATA EDIT THE LAST ARGUMENT.
*/
echo cURL("http://www.facebook.com/",null,$cookie,null);
?>