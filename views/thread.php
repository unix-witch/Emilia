<!DOCTYPE html>
<html>
    <head>
        <title>
            <?php
                session_start();

                require 'scripts/credit_footer.php';
                require 'configs/config.php';
                
                $boardName = substr($_SERVER['REQUEST_URI'], 1);
                $boardName = strtok($boardName, '/');
                $boardName = strtok($boardName, '?');
                
                $threadID = explode('/', $_SERVER['REQUEST_URI'])[2];
                    
                echo htmlspecialchars($boardName . ':' . $threadID); 

                if (!isset($_SESSION['authenticated']))
                    $_SESSION['authenticated'] = false;
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

            $loggedIn = $_SESSION['authenticated'];

            //Just print the banner 
            echo "<div style=\"text-align: center;\">";
            if ($loggedIn) {
                echo "[<a href=\"/profile\">{$_SESSION["username"]}</a>]\n";
            
            } else {
                echo "[<a href=\"/login\">login</a>]\n";
                echo "[<a href=\"/register\">register</a>]\n";
            }

            
            foreach (array_keys($boards) as $currentBoard) {
                echo "[<a href=/{$currentBoard}>{$currentBoard}</a>]\n";
            }
            
            echo "</div>";
        ?>

        <link rel="stylesheet" href="/static/style.css">



        <?php
            $targetDir = "cdn/";

            $error = false;
            $authError = "";
            //$titleError = "";
            $contentError = "";
            $imageError = "";
            $imageSet = false;

            //$title =    filter_var($_POST["title"],         FILTER_SANITIZE_STRING);
            $content =  filter_var($_POST["content"],       FILTER_SANITIZE_STRING);
            //$image =    filter_var($_POST["image"],         FILTER_SANITIZE_STRING);
            
            //Filter ***just in case***
            $username = filter_var($_SESSION["username"],   FILTER_SANITIZE_STRING);


            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if ($_SESSION["authenticated"] !== true) {
                    $authError = "You must login/create a account to post"; 
                    $error = true; 
                }



                

                //if (empty($title)) { $titleError = "Title cannot be empty"; $error = true; }
                if (empty($content)) { $contentError = "content cannot be empty"; $error = true; }
                
                //$check = getimagesize($_FILES["image"]["tmp_name"]);

                
                if (!$error) {
                    if ($_SESSION['authenticated'] === true) {
                        $isMod = $_SESSION['type'] > 0;
                        $username = $_SESSION["username"];
                        $type = $_SESSION['type'];
                        $date = str_replace(":", ";", date('Y-m-d H:i:s'));

                        $board = $boardName; //Which board was it posted on
                        $author = $username; //Which author posted it
                        $postID = $threadID; //what id is the post on
                        $pinned = 0; //is the post pinned?
                        $mod    = $isMod; //Was the post made by a mod?s
                        $type   = $type; //Explained in docs/types.md
                        $date   = $date;

                        $newestPostData = "\n{$board}:{$author}:0:{$mod}:{$type}:{$date}:{$content}";
                        $lineData = "{$boardName}:{$username}:0:{$isMod}:{$type}:{$date}:{$content}\n";

                        file_put_contents(
                            "database/posts/{$boardName}/{$threadID}.thread",
                            $lineData,
                            FILE_APPEND
                        );

                        file_put_contents(
                            "database/posts/recent.postData",
                            $newestPostData,
                            FILE_APPEND
                        );
                    }
                }
            }
        ?>
    </head>
    <body>
        <div style="text-align: center;">
            <h1><?php echo htmlspecialchars($boardName); ?></h1>
            <h5> <?php echo $boards[htmlspecialchars($boardName)]; ?> </h5>
        </div>

        <div class="form-submit-content">
            <div class="board-form">
                <form id="post" method="post" actions="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) ?>">
                    <div style="text-align: center">
                        <span class="error">
                            <?php 
                                //Fuck it. Be quirky and cool 
                                //and put all the errors in 1 section
                                echo $authError . "<br>"; 
                                //echo $titleError . "<br>";
                                echo $contentError . "<br>";
                                echo $imageError . "<br>";

                            ?>
                        </span>
                    </div>
                
                    <!--
                    <label class="board-label" for="title">Title: </label>
                    <input name="title" class="board-input" id="title" type="text" />
                    -->

                    <label class="board-label" for="content">Content: </label>
                    <textarea name="content" style="width: 90%; height: 30vh" class="board-input" id="content"></textarea>


                    <!-- going to implement this later -->
                    <!-- <label name="image" class="board-label" for="image">Image: </label> -->
                    <!--<input class="board-input" id="image" type="file"> -->

                    <div style="text-align: center;">
                    <input type="submit">

                    <!--No idea what the fuck this does, but shit breaks if removed -->
                    </div>
                </form>
            </div>
        </div>

        <div class="main-content-center">
            <div class="index-format-flex">
                <div>
                    <?php 

                        $postDataFile = fopen("database/posts/{$boardName}/data.postData", "r");
                        $recentDataFile = fopen("database/posts/recent.postData", "r");
                        $threadDataFile = fopen("database/posts/{$boardName}/{$threadID}.thread", "r");

                        $postData           = array();
                        $pinnedPostData     = array();
                        $nonPinnedPostData  = array();


                        while (($line = fgets($threadDataFile)) !== false) {
                            $lineData = explode(":", $line);

                            $board = $lineData[0]; //Which board was it posted on
                            $author = $lineData[1]; //Which author posted it
                            $postID = (int) $lineData[2]; //what id is the post on
                            $pinned = (int) $lineData[3]; //is the post pinned?
                            $mod    = (int) $lineData[4]; //Was the post made by a mod?
                            $date   = str_replace(";", ":", $lineData[5]);
                            $postcontent = $lineData[6];


                            $postIDStr = $lineData[2];
                        
                            $postData = array(
                                "board"         => $board,
                                "author"        => $author,
                                "postID"        => $postID,
                                "pinned"        => $pinned,
                                "mod"           => $mod,
                                "date"          => $date,
                                "content"       => $postcontent
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



                            echo "{$author}: ${content}<br>";
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
    </body>
</html>