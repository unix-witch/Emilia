<!DOCTYPE html>
<html>
    <head>
        <title>Register</title>

        <style>.error {color: #FF0000}</style>

        <link rel="stylesheet" href="/static/style.css">
    </head>

    <body>
        <?php
            require 'configs/config.php';
            require 'scripts/credit_footer.php';

            session_start();

            $error = false;

            $registerMessage = "";    

            $usernameError = "";
            $passwordError = "";
            $emailError = "";
            $bioError = "";

            $tosBox = "";
            $coppaBox = "";

            if (is_null($_SESSION["authenticated"]))
                $_SESSION["authenticated"] = false;

            if ($_SESSION["authenticated"] == true) 
                if ($_SESSION['timeout'] < time()) 
                    $_SESSION['authenticated'] = false;



            if ($_SERVER['REQUEST_METHOD'] == "POST") { //Check if its a post request
                $username = $_POST["username"];
                $password = $_POST["password"];
                $emailadr = $_POST["emailadr"];
                $bio = $_POST["bio"];


                //Check if every input has been set up correctly
                if (empty($username))  { $usernameError = "Username does not exist"; $error = true; }
                if (empty($password))  { $passwordError = "Password does not exist"; $error = true; }
                if (empty($emailadr))  { $emailError = "Email does not exist"; $error = true; }
                if (empty($bio))       { $bioError = "Bio does not exist"; $error = true; }

                if (strlen($username) > 256) { $usernameError = "Username is too long"; $error = true; }
                if (strlen($emailadr) > 256) { $passwordError = "Email is too long"; $error = true; }
                if (strlen($bio) > 256) { $bioError = "bio is too long"; $error = true; }

                if (preg_match('/[\'^£$%&*()}{@#~?><>:,|=_+¬-]/', $username)) {
                    $usernameError = "Username has special characters";
                    $error = true;
                }
                
                if (!isset($_POST["coppa"])) { 
                    $coppaBox = "You need to be 13+ or have permision to join"; 
                    $error = true; 
                }
                
                if (!isset($_POST["tos"])) { 
                    $tosBox = "You must to agree to the TOS to join"; 
                    $error = true; 
                }
                

                //Check if the email is valid
                if (
                    !filter_var($_POST["emailadr"], FILTER_VALIDATE_EMAIL) && 
                    empty($_POST["email"])) $emailError = "Email is invalid";

                if (!$error) {
                    $currentDate = date("Y-m-d H:i:s"); //Get the current date
                    $password = password_hash($password, PASSWORD_BCRYPT); //Hash the password

                    //Sanitize the inputs just in case they are a sql injection attack
                    $sanitizedUsername  = filter_var($username,    FILTER_SANITIZE_STRING);
                    $sanitizedEmailAdr  = filter_var($emailadr,    FILTER_SANITIZE_STRING);
                    $sanitizedBio       = filter_var($bio,         FILTER_SANITIZE_STRING);
                    $sanitizedBio       = nl2br($sanitizedBio);

                    $ip = $_SERVER['REMOTE_ADDR'];                    


                    $database = new SQLite3("./database/users.db"); //Create a new database class

                    

                    //Init a table. 
                    //This looks less horrible than checking if a file exists than creating it
                    $database->query("CREATE TABLE IF NOT EXISTS Users (
                        UserID          INTEGER             NOT NULL PRIMARY KEY AUTOINCREMENT,
                        UserName        VARCHAR(256)        NOT NULL,   --Max username limit
                        UserPass        VARCHAR(80)         NOT NULL,   --Use Bcrypt
                        UserEmail       VARCHAR(256)        NOT NULL,   --Email of the user
                        UserBiogr       VARCHAR(256)        NOT NULL,   --Bio of the user
                        UserAddr        VARCHAR(13)         NOT NULL,   --IP address, used for bans
                        UserCDate       VARCHAR(256)        NOT NULL,   --Date the user register
                        UserLDate       VARCHAR(256)        NOT NULL,   --Date the user was last online
                        UserCoins       INT                 NOT NULL,   --Coins
                        UserBanned      INT                 NOT NULL,   --Is the user banned or not
                        UserType        INT                 NOT NULL    --Just read the docs dont have time to explain
                    );");

                    //Check if ther are any ID's where the username is the same
                    $usernameExistCheck = $database->querySingle(
                        sprintf("SELECT UserID FROM Users WHERE UserName = '%s'", $sanitizedUsername)
                    );

                    //If the username is null, create it
                    if (is_null($usernameExistCheck)) {

                        $database->query("INSERT INTO Users (
                                UserID, 
                                UserName, 
                                UserPass, 
                                UserEmail, 
                                UserBiogr, 
                                UserAddr,
                                UserCDate,  --Account created date
                                UserLDate,  --Account last online date
                                UserCoins, 
                                UserBanned,
                                UserType
                            ) VALUES (
                                NULL, 
                                '$sanitizedUsername', 
                                '$password', 
                                '$sanitizedEmailAdr', 
                                '$sanitizedBio', 
                                '$ip',
                                '$currentDate', 
                                '$currentDate',
                                0, --Every user starts out with 0 coins,
                                0, --Banned
                                0) --User Type, something something fucking docs"
                            );
                            
                            $_SESSION["authenticated"] = true;
                            $_SESSION["username"] = $sanitizedUsername;
                            $_SESSION["timeout"] = time() + 604800016;
                            $_SESSION["type"] = 0;
                        //Set the message to a success message 
                        $registerMessage = "Account created. <a href='login'>login</a> to continue";
                    } else {
                        
                        //Set error to true and set the error message
                        $error = TRUE;
                        $registerMessage = "User already exists. Choose a different username";
                    }
                }
            }
            
        ?>

        <div class="register-header">
            <h1><?php echo $site_name; ?></h1>
            
        </div>


            
        
        
        <div class="main-content-center">
            <div class="index-format-flex">
                <div>
                    <form id="reg" method="post" actions="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) ?>">
                        
                        <label class="board-label" for="username">Username: </label>
                        <input id="username" type="text" name="username" class="md-form-input">
                        <span class="error"><?php echo $usernameError; ?></span><br>
                        
                        <label class="board-label" for="password">Password: </label>
                        <input id="password" type="password" name="password" class="md-form-input">
                        <span class="error"><?php echo $passwordError; ?></span><br>
                        
                        <label class="board-label" for="eaddr">Email: </label>
                        <input id="eaddr" type="email" name="emailadr" class="md-form-input">
                        <span class="error"><?php echo $emailError; ?></span><br>

                        <label class="board-label" for="bio">Bio: </label>
                        <textarea id="bio" name="bio" form="reg">Enter in your bio here</textarea>
                        <span class="error"><?php echo $bioError; ?></span><br>
                        
                        
                        <input type="checkbox" name="coppa"> 
                        <label>I am 16+ or have my parents permission</label>
                        <span class="error"><?php echo $coppaBox; ?></span><br>

                        <input type="checkbox" name="tos"> <label>
                            By signing up, I acknowledge that I have read and agree to beautification club's<br>
                            <a href="privacy">Privacy Policy</a>, <a href="tos">Terms of Use</a>, and
                            <a href="rules">Rules</a>
                        </label>
                        <span class="error"><?php echo $tosBox; ?></span><br>

                        <input type="submit">
                    </form>
                </div>

                <div style="margin-left: auto;" class="index-sidebar">
                    <?php 
                        readfile("text/sidebar.html");
                    ?>
                </div>
            </div>
        </div>

        <div class="register-errors">
            <?php 
                echo "<span class=\"error\">" . $registerMessage . "</span>";
            ?>
        </div>

        <div class="footer">
            <?php echoFooterText(); ?>
        </div>
    </body>
</html>