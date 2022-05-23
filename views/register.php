<?php
    require './configs/router.php';
    require './configs/boards.php';
    require './configs/site.php';

    require './server/board_display.php';
    require './server/uptime.php';
    require './server/footer.php';

    $form_action = htmlspecialchars($_SERVER["PHP_SELF"]);

    session_start();

    function error($error) {                                                    //Echo error messages a bit better
            echo "<span class=\"error\">$error</span>";                         //Print the span that contains error info
    }

    $username_error   = "";                                                     //Error string to display if any error
    $password_error   = "";                                                     //dittow
    $pass_conf_error  = "";                                                     //ditto
    $bio_error        = "";                                                     //ditto

    $registered = false;
    $error = false;                                                             //Boolean used to prevent database fuckery

    if ($_SERVER["REQUEST_METHOD"] == "POST") {                                 //Do form data sent logic below
        $username  = filter_var($_POST["username"],  FILTER_SANITIZE_STRING);   //Filter any inputs just in case
        $password  = filter_var($_POST["password"],  FILTER_SANITIZE_STRING);   //Ditto
        $pass_conf = filter_var($_POST["pass_conf"], FILTER_SANITIZE_STRING);   //Ditto
        $bio       = filter_var($_POST["bio"],       FILTER_SANITIZE_STRING);   //Ditto
        $bio       = nl2br($bio);                                               //Newlines to br. Seperate line for col lim
    
        if (empty($username)) { $username_error = "Username cannot be empty"; } //Check if username empty
        if (empty($password)) { $password_error = "Password cannot be emtpy";}  //Check if password empty
        if (empty($pass_conf)){ $pass_conf_error = "Password cannot be empty"; }//Check if second password is emtpy
        if (empty($bio))      { $bio_error = "Bio cannot be empty"; }           //Check if the bio is empty

        if (strcmp($password, $pass_conf)!=0){$pass_conf="Passwords not equal";}//Check if password conf is not empty

        if (strlen($username) > 255)  $username_error = "Username is too long";
        if (strlen($bio) > 255)       $bio_error = "Bio is too long";

        if ( !empty($username_error)  || 
             !empty($password_error)  || 
             !empty($pass_conf_error) || 
             !empty($bio_error) ) $error = true;                                //Say that there is error if error str set


        if (!$error) {                                                          //Execute this if there was no error
            $current_date = date("Y-m-d H:i:s");                                //Get the date for account creation date
            $password_hash = password_hash($password, PASSWORD_BCRYPT);         //Hash passwords using bcrypt
            $user_ip = $_SERVER['REMOTE_ADDR'];                                 //Get the IP of the user ip address
            
            $database = new SQLite3("./database/users/users.db");               //Create a new database for user login data
            $database->query("CREATE TABLE IF NOT EXISTS Users (
                UserID      INTEGER         NOT NULL PRIMARY KEY AUTOINCREMENT, --
                UserName    VARCHAR(256)    NOT NULL,                           --Username. 256 char limit
                UserPass    VARCHAR(80)     NOT NULL,                           --Password. 80 is bcrypt's hash size
                UserBio     VARCHAR(256)    NOT NULL,                           --User's bio. 256 limit
                UserAddr    VARCHAR(13)     NOT NULL,                           --User's IP address
                UserCDate   VARCHAR(256)    NOT NULL,                           --Date the account was created
                UserLDate   VARCHAR(256)    NOT NULL,                           --Last time user was online
                UserBanned  INT             NOT NULL,                           --Is the user banned or not
                UserPerms   INT             NOT NULL                            --Is the user mod, admin, dev, etc
            );");

            $username_exist = $database->querySingle(sprintf(                   //Check if a username exists
                "SELECT UserID FROM Users WHERE UserName = '%s'", $username     //Get any users with said username
            ));

            if (is_null($username_exist)) {                                     //Check if the username exists
                $database->query("INSERT INTO Users
                    (                                                           --Insert user data into user table
                        UserID,                                                 --Autoincrement my beloved
                        UserName,
                        UserPass,
                        UserBio,
                        UserAddr,
                        UserCDate,
                        UserLDate,
                        UserBanned,  
                        UserPerms    
                    ) VALUES (                                                  --Insert the proper values here
                        NULL,                                                   --id is autoincrement. set to null
                        '$username',                                            --Set the username field
                        '$password_hash',                                       --Set the password field
                        '$bio',                                                 --Set the bio field
                        '$user_ip',                                             --Do a little trolling here
                        '$current_date',                                        --Account created at this date
                        '$current_date',                                        --Technically online when account created
                        0,                                                      --Dont ban accounts when they are created
                        0                                                       --Dont give any special perms to new users
                    );
                ");

                $_SESSION["banned"]   = false;                                  //Dont just ban users right after register
                $_SESSION["authed"]   = true;                                   //User is now authed
                $_SESSION["username"] = $username;                              //Set the username
                $_SESSION["perms"]    = 0;                                      //Cannot access admin boards
                $registered = true;                                             //used to display the nice message

            } else $username_error = "Username already exists.";                //Notify user that username is taken 
        }
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Register</title>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="robots" content="noindex, nofollow">

        <meta property="og:title"   content="Ellie: Register">
        <meta property="og:type"    content="website">
        <meta property="og:url"     content="https://beau.tification.club">
        <meta property="og:image"   content="graph-image.png">

        <link rel="stylesheet"      href="/static/style.css">
        <link rel="stylesheet"      href="/static/register.css">
        <link rel="stylesheet"      href="/static/board-display.css">
    </head>
    <body>
        <?php display_board($boards); ?>
        <div class="register-form-contents">
            <div class="header-div">
                <h1 class="register-header">Register</h1>
            </div>

            <div class="header-div">
                <?php
                    if ($registered)
                        echo "Registered!. Login <a href=\"/login\">here</a>";
                ?>
            </div>

            <form  method="POST" action="<?php echo $form_action ?>">
                <label for="username">
                    Username <?php error($username_error) ?>
                </label>
                <input type="text"     id="username"  name="username"><br>
                
                <label for="bio">
                    Bio <?php error($bio_error); ?>
                </label>
                <input type="text"     id="text"      name="bio"><br>
                
                <label for="password">
                    Password <?php error($password_error); ?>
                </label>
                <input type="password" id="password"  name="password"><br>
                
                <label for="pass_conf">
                    Confirm password <?php error($pass_conf_error); ?>
                </label>
                <input type="password" id="pass_conf" name="pass_conf"><br>
                
                
                <div style="text-align: center">
                    <input type="submit" value="Register"><br>
                </div>
            
                
            </form>
            
            <div style="text-align: center; width: 45%; margin: auto;">
                <p>
                    By clicking register you will be creating a account
                    and therefore agree to the privacy policy and terms
                    of service
                </p><br>

                <p>Already have an account? You can 
                    <a class="link" href="/login">login instead</a>
                </p>
            </div>
        </div>

        <?php display_footer(); ?>
    </body>
</html>