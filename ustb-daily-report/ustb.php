<!DOCTYPE html >
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<html>
<head>
<title>Automatic USTB Dailay Report with HASS</title>
</head>
<body>
    <h4> WHAT IS IT </h4>
        An automatic USTB daily report integrated with HASS - a home automation platform, for a precise report. The auto report only functions when (your phone reports that) you are in the announced zone, this program now supports multiple zones and WeChat notification.
    <h4> AGREEMENT </h4>
        <b> Using this system exposes your phone location, report UA/Cookie/Data to the website manager. </b>
    <h4> HASS SETUP </h4>
         Download <a href="https://www.home-assistant.io/integrations/mobile_app/">HomeAssistant(HA) App</a>, login with given user and password, with server address <a href="http://ustb.terrance.top:8134/">http://ustb.terrance.top:8134/</a>, make sure your location information can be uploaded to the HA server.
    <h4> DATA CAPTURE </h4>
         Tools: stream (iOS) / httpcanary (Android) or anyelse, please Google "how to". Find the POST package to <a href="isport.ustb.edu.cn">isport.ustb.edu.cn</a>, grep the User-Agent, Cookie, and Context content, fill the blank below with them.
    <h4> BUTTONS </h4>
         'submit' to start your automation, all fields required.
    <br> 'update cookie' for cookie update, user name, location, and cookie are required. <b>NOTE: cookie update only designed for cookie expiration for some reason (e.g., server reboot). If your are going back to somewhere (e.g., ustb-home-ustb), use submit for the full data update. </b>
    <br> 'checklog' for the user name corresponding logs, user name required.
    <h4> NEW LOCATION </h4>
         Find and right click your home on <a href="https://www.openstreetmap.org/">this map</a>, you will obtain the latitude and longitude of your point. Since the location zone is a circle ranged 100km (30km for zone.USTB), the location name is suggested to be in characters of the level of county ("XIAN") e.g., haidian, wenshui, zhengding. However, cities like chongqing, shanghai, tianjin doesn't matter.
    <h4> WeChat NOTIFICATION </h4>
         Thanks to the author of IYUU, you can receive error and report message. Obtain a token <a href="http://iyuu.cn/">here</a> in around 10 seconds.
    <HR>
    <h4> TABLE </h4>
    <form method="POST">
        user name:
    <br><input type="text" name="user_name" placeholder="Your Home Assistant User"/>
    <br>IYUU token (for WeChat notification): <a href="http://iyuu.cn/">get one</a> or leave blank
    <br><input type="text" name="iyuu_token" placeholder="e.g., IYUU2...f2bef"/>
    <br>location:<a href="https://www.openstreetmap.org/">find your home</a>
    <br><input type="text" name="user_loc" value="ustb"/>
    <br>latitude:
    <br><input type="text" name="user_lati" value="39.9887"/>
    <br>longitude:
    <br><input type="text" name="user_long" value="116.3533"/>
    <br>user agent:
    <br><textarea name="user_agent" rows="5" cols="40" placeholder="Your User Agent, e.g., Mozilla/5.0 (Linux; Android 1 ... leWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/86.0.4240.99 XWEB/3171 ... Type/WIFI Language/zh_CN ABI/arm64" /></textarea>
    <br>user cookie:
    <br><textarea type="text" name="user_cookie" rows="5" cols="40" placeholder="Your Cookie Value, e.g., SECKEY_ABVK=AAAA; BMAP_SECKEY=BBBB; JSESSIONID=CCCC" /></textarea>
    <br>user data:
    <br><textarea type="text" name="user_data" rows="5" cols="40" placeholder="Your Report Data, e.g., m=yqinfo&c=index&a=submit&phone=....&sfzjwgfxdqszx=否&sfzjwgfxdqqtx=否"/></textarea>
    <br><br><input type="submit" name="submit" value="Submit"> 
    <input type="submit" name="update" value="Update (for cookie only)">
    <input type="submit" name="checklog" value="CheckLog">
    <!-- <input type="button" name="test" value="TEST"> -->
    <br><br>
    </form>

    <?php
        function print_data(){
            echo "$user_name"."<br/>";
            echo "$user_agent"."<br/>";
            echo "$user_cookie"."<br/>";
            echo "$user_data"."<br/>";
        }
        if(isset($_POST['test'])) {
            $user_name=$_POST["user_name"]; 
            $user_agent=$_POST["user_agent"]; 
            $user_cookie=$_POST["user_cookie"]; 
            $user_data=$_POST["user_data"]; 
            if(empty($user_name)) 
                echo "<script language=\"JavaScript\">\r\n";
                echo "alert ('user name can not be empty')";
                echo "</script>"; 
                return false;
        }
        if(isset($_POST['checklog'])) {
            $user_name=$_POST["user_name"];
            if(empty($user_name)) {echo "user name can not be empty"; return;}
            echo "Log of user: ".$user_name." (desc, last 50 lines)<br>";

            $str = file_get_contents("ustb-log/$user_name.log");
            $array = explode("\n",$str);
            $prt_start = sizeof($array) - 2;
            $prt_stop = (sizeof($array) - 52 < -1) ? -1 : (sizeof($array) - 50);
            for($i = $prt_start; $i > $prt_stop; $i--){
                echo $array[$i];
                echo "<br>";
            }

        }
        if(isset($_POST['update'])) {
            $user_action="update";
            $user_name=$_POST["user_name"]; 
            $user_agent=$_POST["user_agent"]; 
            $user_cookie=$_POST["user_cookie"]; 
            $user_data=$_POST["user_data"]; 
            $user_loc=$_POST["user_loc"]; 
            $user_lati=$_POST["user_lati"]; 
            $user_long=$_POST["user_long"]; 

            if(empty($user_name)) {echo "user name can not be empty"; return;}
            if(empty($user_cookie)) {echo "user cookie can not be empty"; return;}
            if(empty($user_loc)) {echo "user location can not be empty"; return;}
            
            $user_name = str_replace(array("\r\n", "\r", "\n"), '', $user_name);
            $user_agent = str_replace(array("\r\n", "\r", "\n"), '', $user_agent);
            $user_cookie = str_replace(array("\r\n", "\r", "\n", "<br />"), '', $user_cookie);
            $user_data = str_replace(array("\r\n", "\r", "\n"), '', $user_data);
            $user_loc = str_replace(array("\r\n", "\r", "\n"), '', $user_loc);
            $user_lati = str_replace(array("\r\n", "\r", "\n"), '', $user_lati);
            $user_long = str_replace(array("\r\n", "\r", "\n"), '', $user_long);
    
            $user_name = trim($user_name);
            $user_agent = trim($user_agent);
            $user_cookie = trim($user_cookie);
            $user_data = trim($user_data);
            $user_loc = trim($user_loc);
            $user_lati = trim($user_lati);
            $user_long = trim($user_long);

            $bash_cmd = "bash /volume1/docker/hass/ustb-daily-report/user-manage.sh '$user_action' '$user_name' '$user_agent' '$user_cookie' '$user_data' '$user_loc' '$user_lati' '$user_long'";
            file_put_contents("./ustb-log/cmd-$user_action.log", date("Y-m-d H:i:s")." $bash_cmd \n", FILE_APPEND);
            system("ssh terrance@192.168.0.2 \"$bash_cmd\"");
        }
        if(isset($_POST['submit'])) {
            $user_action="submit";
            $user_name=$_POST["user_name"]; 
            $user_agent=$_POST["user_agent"]; 
            $user_cookie=$_POST["user_cookie"]; 
            $user_data=$_POST["user_data"]; 
            $user_loc=$_POST["user_loc"]; 
            $user_lati=$_POST["user_lati"]; 
            $user_long=$_POST["user_long"]; 
            $iyuu_token=$_POST["iyuu_token"]; 

            if(empty($user_name)) {echo "user name can not be empty"; return;}
            if(empty($user_agent)) {echo "user agent can not be empty"; return;}
            if(empty($user_cookie)) {echo "user cookie can not be empty"; return;}
            if(empty($user_data)) {echo "user data can not be empty"; return;}
            if(empty($user_loc)) {echo "user location can not be empty"; return;}
            if(empty($user_lati)) {echo "user latitude can not be empty"; return;}
            if(empty($user_long)) {echo "user longitude can not be empty"; return;}
            
            $user_name = str_replace(array("\r\n", "\r", "\n"), '', $user_name);
            $user_agent = str_replace(array("\r\n", "\r", "\n"), '', $user_agent);
            $user_cookie = str_replace(array("\r\n", "\r", "\n", "<br />"), '', $user_cookie);
            $user_data = str_replace(array("\r\n", "\r", "\n"), '', $user_data);
            $user_loc = str_replace(array("\r\n", "\r", "\n"), '', $user_loc);
            $user_lati = str_replace(array("\r\n", "\r", "\n"), '', $user_lati);
            $user_long = str_replace(array("\r\n", "\r", "\n"), '', $user_long);
            $iyuu_token = str_replace(array("\r\n", "\r", "\n"), '', $iyuu_token);
    
            $user_name = trim($user_name);
            $user_agent = trim($user_agent);
            $user_cookie = trim($user_cookie);
            $user_data = trim($user_data);
            $user_loc = trim($user_loc);
            $user_lati = trim($user_lati);
            $user_long = trim($user_long);
            $iyuu_token = trim($iyuu_token);

            $bash_cmd = "bash /volume1/docker/hass/ustb-daily-report/user-manage.sh '$user_action' '$user_name' '$user_agent' '$user_cookie' '$user_data' '$user_loc' '$user_lati' '$user_long' '$iyuu_token'";
            file_put_contents("./ustb-log/cmd-$user_action.log", date("Y-m-d H:i:s")." $bash_cmd \n", FILE_APPEND);
            system("ssh terrance@192.168.0.2 \"$bash_cmd\"");
        }
        
    ?>
</body>
</html>
