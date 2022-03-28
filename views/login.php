<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="/static/style.css">
    </head>
    <body>
        <?php
            require 'configs/config.php';
            require 'scripts/credit_footer.php';

            session_start();

            $error = FALSE;
            $rememberMe = FALSE;
            $errorMsg = "";
            $usernameError = "";
            $passwordError = "";
            $validationError = "";
        

            if (!isset($_SESSION["authenticated"])) $_SESSION["authenticated"] = false;

            if ($_SESSION["authenticated"] == true) 
                if ($_SESSION['timeout'] < time()) $_SESSION['authenticated'] = false;

            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $ip = $_SERVER['REMOTE_ADDR'];
                $username = $_POST["username"];
                $password = $_POST["password"];
                $remember = $_POST["remember"];


                //Check if form data is valid or not
                if (empty($username)) { $usernameError = "username empty"; $error = true; }
                if (empty($password)) { $passwordError = "password empty"; $error = true; }
                //if (isset($remember)) $rememberMe = true;
                
                if (strlen($username) > 255) { $username = "username too long"; $error = true; }
                if (strlen($password) > 255) { $password = "password too long"; $error = true; }




                if (!$error) { //No errors should exist
                    $sanitizedUsername = filter_var($username, FILTER_SANITIZE_STRING);
                    $sanitizedPassword = filter_var($password, FILTER_SANITIZE_STRING);

                    $database = new SQLite3("./database/users.db"); //Create a new database

                    //Copied from register.php. Who cares
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
                        UserBanned      INT                 NOT NULL,    --Is the user banned or not
                        UserType        INT                 NOT NULL
                    );");

                    $userExistsCheck = $database->query(
                        sprintf("SELECT UserID FROM Users WHERE UserName = '%s'", $sanitizedUsername)
                    );

                    //Check if a user exists
                    if (!is_null($userExistsCheck)) {
                        $passwordHashSqliteObj = $database->query(
                            sprintf(
                                "SELECT UserPass FROM Users WHERE UserName = '%s'",
                                $sanitizedUsername
                            )
                        );

                        $userTypeSqliteObj = $database->query(
                            sprintf(
                                "SELECT UserType FROM Users WHERE UserName = '%s'",
                                $sanitizedUsername
                            )
                        );

                        $userBannedSqliteObj = $database->query(
                            sprintf(
                                "SELECT UserBanned FROM Users WHERE UserName = '%s'",
                                $sanitizedUsername
                            )
                        );

                        $passwordHash = $passwordHashSqliteObj->fetchArray()[0];
                        $userType = $userTypeSqliteObj->fetchArray()[0];
                        $userBanned = $userBannedSqliteObj->fetchArray()[0];

                        //Check if a password matches the hash
                        if (password_verify($password, $passwordHash)) {

                            $_SESSION["banned"] = $userBanned;
                            $_SESSION["username"] = $sanitizedUsername;
                            $_SESSION["authenticated"] = true;
                            $_SESSION["timeout"] = time() + 604800016; //Timeout in 1 week
                            $_SESSION["type"] = $userType;
                            
                            //Set the error msg to a success msg
                            //This is because I cant be bothered to actually
                            //have a success message

                            //This has stabbed me in the back so many times
                            $errorMsg = "Login sucessful! Go <a href='/'>here</a> to return to the main page"; 
                            
                        //If a password does not match the hash, create a error
                        } else {
                            $_SESSION["authentiated"] = false;
                            $errorMsg = "Invalid password!";
                        }

                        
                    } else {
                        //Set the error message if the user does not exist
                        $errorMsg = "Account does not exist";
                    }
                }
            }
            
        ?>

        <div class="register-header">
            <h1> <?php echo $site_name; ?>  </h1>
        </div>

        <div class="main-content-center">
            <div class="index-format-flex">
                <div>
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <label class="board-label" for="username">Username: </label>
                        <input type="text" name="username" id="username"><br>

                        <label class="board-label" for="password">Password: </label>
                        <input type="password" name="password" id="password"><br>

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

        <div class="footer">
            <?php echoFooterText(); ?>
        </div>
    </body>
</html>