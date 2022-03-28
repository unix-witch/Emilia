<!DOCTYPE html>
<html>
    <head>
        <title>
            <?php 
                session_start();

                require 'scripts/credit_footer.php';
                require 'configs/permission.php';
                require 'configs/config.php';

                $boardName = substr($_SERVER['REQUEST_URI'], 1);
                $boardName = strtok($boardName, '/');
                $boardName = strtok($boardName, '?');
            
                
                    



                
                    

                echo htmlspecialchars($boardName); 

            ?>
        </title>
        <?php
            if (in_array($boardName, array_keys($boardPermissions))) {
                if ($_SESSION['authenticated'] == false) {
                    require 'views/errors/403.php';
                    die;
                }


                if (!($_SESSION['type'] > $boardPermissions[$boardName])) {
                    require 'views/errors/403.php';
                    die;
                }
            }
            

            $loggedIn = false;
            $sanitizedUsername = "";

            $errorMsg = "";

            if (is_null($_SESSION['authenticated'])) 
                $_SESSION['authenticated'] = false;
        
            $loggedIn = $_SESSION['authenticated'];

            echo "<div style=\"text-align: center;\">";
                if ($loggedIn) {
                    echo "[<a href=\"profile\">{$_SESSION['username']}</a>]\n";
                
                } else {
                    echo "[<a href=\"/login\">login</a>]\n";
                    echo "[<a href=\"/register\">register</a>]\n";
                }
            
                
                foreach (array_keys($boards) as $currentBoard) {
                    echo "[<a href=/{$currentBoard}>{$currentBoard}</a>]\n";
                }
                
            echo "</div>";

            $targetDir = "cdn/";

            $error = false;
            $authError = "";
            $titleError = "";
            $contentError = "";
            $imageError = "";
            $imageSet = false;

            $title =    filter_var($_POST["title"],         FILTER_SANITIZE_STRING);
            $content =  filter_var($_POST["content"],       FILTER_SANITIZE_STRING);
            $image =    filter_var($_POST["image"],         FILTER_SANITIZE_STRING);
            
            //Filter ***just in case***
            $username = filter_var($_SESSION["username"],   FILTER_SANITIZE_STRING);


            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if ($_SESSION["authenticated"] !== true) {
                    $authError = "You must login/create a account to post"; 
                    $error = true; 
                }





                if (empty($title)) { $titleError = "Title cannot be empty"; $error = true; }
                if (empty($content)) { $contentError = "content cannot be empty"; $error = true; }
                
                $check = getimagesize($_FILES["image"]["tmp_name"]);

                if (!empty($title) && strpos($title, "\n") !== false) {
                    $titleError = "The title cannot have any newlines! (how did that happen?)";
                    $error = true;
                }


                
                if (!$error) {
                    $content = nl2br($content);    

                    foreach ($banned_words as $wordCheck) {
                        if (strpos($title, $wordCheck) !== false || 
                            strpos($content, $wordCheck) !== false) 
                            
                            {
                                $database = new SQLite3("./database/users.db");
                            
                            
                            
                                $database->query(sprintf(
                                    "UPDATE Users SET UserBanned = 1 WHERE UserName = '%s'",
                                    filter_var($_SESSION['username'], FILTER_SANITIZE_STRING)
                                ));
                            }
                    }

                    $dataFileContents = file_get_contents("database/posts/{$boardName}/data.postData");

                    //Just gets the amount of files in a dir
                    $postIDIterator = new FilesystemIterator(
                        "database/posts/{$boardName}/",
                        FilesystemIterator::SKIP_DOTS
                    );

                    $postID = iterator_count($postIDIterator); //ID is amount of files in the board dir

                    $username = $_SESSION["username"]; //Username should be stored in session

                    $type = $_SESSION["type"]; //user type should be stored in session
                    $date = str_replace(":", ";", date('Y-m-d H:i:s'));
                    $isMod = (int) ($type > 0);

                    $lineData = "{$boardName}:{$username}:{$postID}:{$isMod}:{$type}:{$date}:{$content}:{$title }\n";
                    $dataFileContents = $lineData . $dataFileContents;

                    $newestPostData = "\n{$boardName}:{$username}:0:{$isMod}:{$type}:{$date}:{$content}";

                    file_put_contents(
                        "database/posts/{$boardName}/data.postData", 
                        $dataFileContents
                    );

                    file_put_contents(
                        "database/posts/{$boardName}/{$postID}.thread",
                        $lineData
                    );

                    file_put_contents(
                        "database/recent.postData",
                        $newestPostData,
                        FILE_APPEND
                    );


                }
            }
        ?>
        <link rel="stylesheet" href="/static/style.css">
    </head>
    <body>
        <div style="text-align: center;">
            <h1><?php echo htmlspecialchars($boardName); ?></h1>
            <h5> <?php echo $boards[htmlspecialchars($boardName)]; ?> </h5>
        </div>
        
        <!--Actual mess of a form-->
        <div class="form-submit-content">
            <div class="board-form">
                <form id="post" method="post" actions="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) ?>">
                    <span class="error">
                        <?php 
                            //Fuck it. Be quirky and cool 
                            //and put all the errors in 1 section
                            echo $authError . "<br>"; 
                            echo $titleError . "<br>";
                            echo $contentError . "<br>";
                            echo $imageError . "<br>";
                        
                        ?>
                    </span>
                
                    <label class="board-label" for="title">Title: </label>
                    <input name="title" class="board-input" id="title" type="text" />

                    <label class="board-label" for="content">Content: </label>
                    <textarea name="content" style="width: 90%; height: 30vh" class="board-input" id="content"></textarea>

                    <!--
                    <label name="image" class="board-label" for="image">Image: </label>
                    <input class="board-input" id="image" type="file">
                    -->

                    <div style="text-align: center;">
                        <input type="submit">
                    </div>
                </form>
            </div>
        </div>


        <div class="main-content-center">
            <div class="index-format-flex">
                <div>
                    <?php 
                        //2 just in case theres only a whitespace char
                        if (filesize("database/posts/{$boardName}/data.postData") < 2) {
                            echo "there are no posts";
                        
                        } else {
                            $postDataFile = fopen("database/posts/{$boardName}/data.postData", "r");

                            $postData           = array();
                            $pinnedPostData     = array();
                            $nonPinnedPostData  = array();

                            while (($line = fgets($postDataFile)) !== false) {
                                $lineData = explode(":", $line);

                                $board = $lineData[0]; //Which board was it posted on
                                $author = $lineData[1]; //Which author posted it
                                $postID = (int) $lineData[2]; //what id is the post on
                                $pinned = (int) $lineData[3]; //is the post pinned?
                                $mod    = (int) $lineData[4]; //Was the post made by a mod?
                                $date   = str_replace(";", ":", $lineData[5]);
                                $title = $lineData[6];
                                $content = $lineData[7];


                                $postIDStr = $lineData[2];
                            
                                $postData = array(
                                    "board"         => $board,
                                    "author"        => $author,
                                    "postID"        => $postID,
                                    "pinned"        => $pinned,
                                    "mod"           => $mod,
                                    "date"          => $date,
                                    "content"       => $content
                                );

                                if ($pinned == 1)
                                    array_push($pinnedPostData, $postData);

                                else 
                                    array_push($nonPinnedPostData, $postData);
                                
                            }

                            $postData = array_merge($pinnedPostData, $nonPinnedPostData);


                            foreach ($postData as $currentPost) {
                                $author = $currentPost["author"];
                                $board = $currentPost["board"];
                                $postID = $currentPost["postID"];
                                $content = $currentPost["content"];

                                echo "
                                    <div class=\"post\">
                                        <a href=\"/profile/{$author}\">{$author}</a>: 
                                        <a href=\"/{$board}/{$postID}\">{$content}</a>
                                    </div>
                                ";
                            }
                        }
                    ?>
                </div>
                <div class="index-sidebar" style="margin-left: auto;">
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