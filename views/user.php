<?php
    require './configs/router.php';
    require './configs/boards.php';
    require './configs/site.php';

    require './server/board_display.php';
    require './server/uptime.php';
    require './server/footer.php';


    $username = filter_var($thread_check[2], FILTER_SANITIZE_STRING);           //Sanitize username because SQL used later
    $database = new SQLite3("./database/users/users.db");                       //Create a new sqlite3 database thing
    $user_exists = true;

    $user_banned = false;
    $user_address = "";
    $user_bio = "";
    $user_account_created = "";
    $user_perms = 0;

    $username_error = "";
    $bio_error = "";
    $password_error = "";
    $password_conf_error = "";

    $form_action = htmlspecialchars($_SERVER["PHP_SELF"]);

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

    $user_exist = $database->query(sprintf(                             //Query to check if the user exists
        "SELECT UserID FROM Users WHERE UserName = '%s'", $username
    ));

    if (is_null($user_exist)) $account_exist = false;
    else {
        $account_exist = true;
        $user_banned_sql = sprintf(
            "SELECT UserBanned FROM Users WHERE UserName IS '%s'",
            $username
        );

        $user_address_sql = sprintf(
            "SELECT UserAddr FROM Users WHERE UserName IS '%s'",
            $username
        );

        $user_bio_sql = sprintf(
            "SELECT UserBio FROM Users WHERE UserName IS '%s'",
            $username
        );
        
        $creationdate_sql = sprintf(
            "SELECT UserCDate FROM Users WHERE UserName is '%s'",
            $username
        );

        $user_perms_sql = sprintf(
            "SELECT UserPerms FROM Users WHERE UserName is '%s'",
            $username
        );


        $user_banned_sql = $database->query($user_banned_sql);
        $user_address_sql = $database->query($user_address_sql);
        $user_bio_sql = $database->query($user_bio_sql);
        $creationdate_sql = $database->query($creationdate_sql);
        $user_perms_sql = $database->query($user_perms_sql);

        $user_banned = $user_banned_sql->fetchArray()[0];                       //Fetch if the user is banned
        $user_address = $user_address_sql->fetchArray()[0];                     //Fetch the user IP address
        $user_bio = $user_bio_sql->fetchArray()[0];                             //Fetch the user bio
        $user_account_created = $creationdate_sql->fetchArray()[0];             //Fetch account creation date
        $user_perms = $user_perms_sql->fetchArray()[0];                         //Fetch user perms
    }


    if ($_SERVER["REQUEST_METHOD"] == "POST") {                                 //Check if form was sent
        $username_update  = $_POST["username"];                                 //Get form username
        $bio_update       = $_POST["bio"];                                      //Get form bio
        $pass_update      = $_POST["password"];                                 //Get first form password
        $pass_conf_update = $_POST["pass_conf"];                                //Get second form password

        $new_username  = filter_var($username_update, FILTER_SANITIZE_STRING);  //Filter the new username
        $new_bio       = filter_var($bio_update, FILTER_SANITIZE_STRING);       //Filter the new bio
        $new_pass      = filter_var($pass_update, FILTER_SANITIZE_STRING);      //Filter the first password provided
        $new_pass_conf = filter_var($pass_conf_update, FILTER_SANITIZE_STRING); //Filter the second password provided

        $username_take = sprintf(
            "SELECT UserName FROM Users WHERE UserName='%s';", $new_username
        );

        $username_take = $database->querySingle($username_take);
        
        if (!empty($new_username))                                              //Check if user wants to update username
            if (is_null($username_take)) {                                      //Check if username was not taken
                $update_username_query = sprintf(                               //Format the new SQL query
                    "UPDATE Users SET UserName = '%s' WHERE UserName = '%s';",
                    $new_username,
                    $username
                );


                $database->query($update_username_query);                       //Query the update username statment

                $_SESSION["username"] = $new_username;                          //Update so that form listing works
                header("Refresh:0");
            } else $username_error = "Username is already taken";               //Update username error
    
    
        if (!empty($new_bio))
            if (strlen($new_bio) < 255) {
                $update_bio_query = sprintf(
                    "UPDATE Users SET UserBio = '%s' WHERE UserName = '%s';",
                    $new_bio,
                    $username
                );

                $database->query($update_bio_query);
                header("Refresh:0");
            } else $bio_error = "Bio is too long!";
    
    
        if (!empty($new_pass) || !empty($new_pass_conf))
            if (strcmp($new_pass, $new_pass_conf) == 0) {
                $password_hash = password_hash($new_pass, PASSWORD_BCRYPT);
                
                $update_pass_query = sprintf(
                    "UPDATE Users SET UserPass = '%s' WHERE UserName = '%s';",
                    $password_hash,
                    $username
                );


                $database->query($update_pass_query);
                header("Refresh:0");
            } else $password_error = "Passwords do not match";
    }
    
?>
<!DOCTYPE html>
<html>
    <head>
        <title>User Profile</title>
        
    
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="robots" content="noindex, nofollow">

        <meta property="og:title"   content="Ellie">
        <meta property="og:type"    content="website">
        <meta property="og:url"     content="https://beau.tification.club">
        <meta property="og:image"   content="graph-image.png">

        <link rel="stylesheet"      href="/static/style.css">
        <link rel="stylesheet"      href="/static/user.css">
        <link rel="stylesheet"      href="/static/board-display.css">
    </head>
    <body>
        <?php display_board($boards); ?>
        <br><br>
        
        <?php
            if (!$account_exist) {
                c404();
                die;
            }

            


            if ($user_banned) {
                echo "<div class=\"user-banned\">";
                echo "<h1 class=\"user-banned-title\">Rip Bozo</h1>";
                echo "This user, $username ($user_address) has been banned";
                echo "</div><br><br>";
            }
            
            
        ?>

        <div class="user-content">
            <h1 class="user-content-title"><?php echo $username ?></h1>
            <div class="user-data">
                <?php 
                    switch ($user_perms) {                                      //Switch case for permission level
                        case 0: echo "This user is a regular user"; break;
                        case 1: echo "This user is a trial mod"; break;
                        case 2: echo "This user is a regular mod"; break;
                        case 3: echo "This user is a admin"; break;
                        case 4: echo "This user is a developer"; break;

                        default: echo "This users permissions are unknown";     //Shoudnt happen but here anyway
                    }
                    echo "<br>Account created on $user_account_created<br>";
                    echo "Bio: $user_bio";

                    

                ?>
                <br><br>
            </div>
        </div>
        <br>
        <?php
            if (strcmp($username, $_SESSION["username"]) == 0)
                echo "
                    <div class=\"user-content\">
                        <h1 class=\"user-content-title\">Change user data</h1>
                        <div>
                            <p>
                                note: You only have to change 1 value. 
                                you do not need to change all    
                            </p>
                            
                            <form method=\"POST\" action=$form_action>
                                <label>
                                    username 
                                    <span class=\"error\">$username_error
                                    </span>
                                </label>
                                <input 
                                    type=\"text\" 
                                    id=\"username\" 
                                    name=\"username\">
                                
                                <label for=\"bio\">bio</label>
                                <textarea id=\"bio\" name=\"bio\"></textarea>

                                <label for=\"password\">
                                    Password
                                </label>
                                <input 
                                    type=\"password\" 
                                    id=\"password\"
                                    name=\"password\"
                                >
                                
                                <label for=\"pass_conf\">Confirm Password</label>
                                <input 
                                    type=\"password\" 
                                    id=\"pass_conf\"
                                    name=\"pass_conf\"
                                >

                                <div style=\"text-align: center\">
                                    <input type=\"submit\" value=\"Update\">
                                </div>
                            </form>
                        </div>
                    </div>
                ";
        ?>

        <div style="text-align: center">
            <br><a href="/">Go back<a>
        </div>

        <?php display_footer(); ?>
    </body>
</html>