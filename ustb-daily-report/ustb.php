<!DOCTYPE html >
<meta charset="UTF-8">
<html>
<body>
    How to use
    <br> login with given user and password on HomeAssistant(HA) App with this url http://ustb.terrance.top:8134/
    <br> make sure your location information can be uploaded to the HA server
    <br> capture your report data of your first 'automation day', and paste the required data below
    <br> click 'submit' to start your automation, 'update' for data update, and 'checklog' for the user name corresponding logs.
    <br> new location: right click your home on this map, https://www.openstreetmap.org/, you can obtain the latitude and longitude of your point.
    <br> The location must be in characters of the level of county ("XIAN") e.g., haidian, wenshui, zhengding.
    <br>
    <form method="POST">
    user name:<br><input type="text" name="user_name" placeholder="Your Home Assistant User"/><br>
    location:<br><input type="text" name="user_loc" value="bj"/><br>
    latitude:<br><input type="text" name="user_lati" value="39.9887"/><br>
    longitude:<br><input type="text" name="user_long" value="116.3533"/><br>
    user agent:<br>
    <textarea name="user_agent" rows="5" cols="80" placeholder="Your User Agent, e.g., Mozilla/5.0 (Linux; Android 11; SM-G9810 Build/RP1A.200720.012; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/86.0.4240.99 XWEB/3171 MMWEBSDK/20211001 Mobile Safari/537.36 MMWEBID/7956 MicroMessenger/8.0.16.2040(0x2800105B) Process/toolsmp WeChat/arm64 Weixin NetType/WIFI Language/zh_CN ABI/arm64" /></textarea><br>
    user cookie:<br>
    <textarea type="text" name="user_cookie" rows="5" cols="80" placeholder="Your Cookie Value, e.g., SECKEY_ABVK=AAAA; BMAP_SECKEY=BBBB; JSESSIONID=CCCC" /></textarea><br>
    user data:<br>
    <textarea type="text" name="user_data" rows="5" cols="80" placeholder="Your Report Data, e.g., m=yqinfo&c=index&a=submit&phone=....&sfzjwgfxdqszx=否&sfzjwgfxdqqtx=否"/></textarea>
    <br><br><input type="submit" name="submit" value="Submit"> 
    <!-- <input type="submit" name="update" value="Update"> -->
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
            echo "Log of user: ".$user_name."<br>";
            system("tac ustb-log/$user_name.log | sed 's/\./<br>/g' ");
        }
        if(isset($_POST['update'])) {
            $user_name=$_POST["user_name"]; 
            $user_agent=$_POST["user_agent"]; 
            $user_cookie=$_POST["user_cookie"]; 
            $user_data=$_POST["user_data"]; 
            
            if(empty($user_name)) {echo "user name can not be empty"; return;}
            if(empty($user_agent)) {echo "user agent can not be empty"; return;}
            if(empty($user_cookie)) {echo "user cookie can not be empty"; return;}
            if(empty($user_data)) {echo "user data can not be empty"; return;}

            $user_name = str_replace(array("\r\n", "\r", "\n"), '', $user_name);
            $user_agent = str_replace(array("\r\n", "\r", "\n"), '', $user_agent);
            $user_cookie = str_replace(array("\r\n", "\r", "\n", "<br />"), '', $user_cookie);
            $user_data = str_replace(array("\r\n", "\r", "\n"), '', $user_data);
    
            $user_name = trim($user_name);
            $user_agent = trim($user_agent);
            $user_cookie = trim($user_cookie);
            $user_data = trim($user_data);
            $bash_cmd = "bash /volume1/docker/hass/ustb-daily-report/user-manage.sh update '$user_name' '$user_agent' '$user_cookie' '$user_data'";
            file_put_contents("./ustb-log/cmd-update.log", date("Y-m-d H:i:s")." $bash_cmd \n", FILE_APPEND);
            system("ssh terrance@192.168.0.2 \"$bash_cmd\"");
        }
        if(isset($_POST['submit'])) {
            $user_name=$_POST["user_name"]; 
            $user_agent=$_POST["user_agent"]; 
            $user_cookie=$_POST["user_cookie"]; 
            $user_data=$_POST["user_data"]; 
            $user_loc=$_POST["user_loc"]; 
            $user_lati=$_POST["user_lati"]; 
            $user_long=$_POST["user_long"]; 

            if(empty($user_name)) {echo "user name can not be empty"; return;}
            if(empty($user_agent)) {echo "user agent can not be empty"; return;}
            if(empty($user_cookie)) {echo "user cookie can not be empty"; return;}
            if(empty($user_data)) {echo "user data can not be empty"; return;}
            
            $user_name = str_replace(array("\r\n", "\r", "\n"), '', $user_name);
            $user_agent = str_replace(array("\r\n", "\r", "\n"), '', $user_agent);
            $user_cookie = str_replace(array("\r\n", "\r", "\n", "<br />"), '', $user_cookie);
            $user_data = str_replace(array("\r\n", "\r", "\n"), '', $user_data);
    
            $user_name = trim($user_name);
            $user_agent = trim($user_agent);
            $user_cookie = trim($user_cookie);
            $user_data = trim($user_data);
            $user_loc = trim($user_loc);
            $user_lati = trim($user_lati);
            $user_long = trim($user_long);

            $bash_cmd = "bash /volume1/docker/hass/ustb-daily-report/user-manage.sh 'submit' '$user_name' '$user_agent' '$user_cookie' '$user_data' '$user_loc' '$user_lati' '$user_long'";
            file_put_contents("./ustb-log/cmd-submit.log", date("Y-m-d H:i:s")." $bash_cmd \n", FILE_APPEND);
            system("ssh terrance@192.168.0.2 \"$bash_cmd\"");
        }
        
    ?>
</body>
</html>
