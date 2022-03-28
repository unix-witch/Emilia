<!DOCTYPE html>
<html>
    <head>
        <title></title>

        <link rel="stylesheet" href="/static/style.css">
    </head>
    <body>
        <?php
            session_start();


            $loggedIn = false;
            $sanitizedUsername = "";

            $errorMsg = "";

            if (is_null($_SESSION['authenticated'])) 
                $_SESSION['authenticated'] = false;
        
            $loggedIn = $_SESSION['authenticated'];
        ?>

        <div class="header">
            <?php 
                if ($loggedIn) {
                    echo "[<a href=\"profile\">{$_SESSION['username']}</a>]\n";
                
                } else {
                    echo "[<a href=\"login\">login</a>]\n";
                    echo "[<a href=\"register\">register</a>]\n";
                }
            
                
                foreach (array_keys($boards) as $currentBoard) {
                    echo "[<a href=/{$currentBoard}>{$currentBoard}</a>]\n";
                }    
            ?>
        </div>
        
        <?php
            readfile("text/announcments.html");
        ?>

        <div class="main-content-center">
            <div class="index-format-flex">
                <div>         
                    <h2 class="text-header">Most Recent Posts</h2>
                    <div class="post-list-div">
                        <?php 
                            require 'configs/config.php';
                            require 'scripts/credit_footer.php';

                            //Create if it does not exist
                            foreach (array_keys($boards) as $currentBoardCreator) {
                                if (!is_dir("database/posts/{$currentBoardCreator}/")) {
                                    mkdir("database/posts/{$currentBoardCreator}", 0777, true);
                                    
                                }
                                
                                if (!is_file("database/posts/{$currentBoardCreator}/data.postData")) {
                                    $dataFile = fopen("database/posts/{$currentBoardCreator}/data.postData", "w");
                                    fclose($dataFile);
                                }
                                
                            }

                            //Open the data file containing the most recent posts
                            $postDataFile = fopen("database/posts/recent.postData", "r");
                        
                            $postData = array();

                            $postIds = array();
                            $pinnedPostData = array();
                            $nonPinnedData = array();
                            

                            if ($postDataFile) {
                                while (($line = fgets($postDataFile)) !== false) {
                                    $lineData = explode(':', $line);

                                    
                                    

                                    //Funny variable's loaded from array
                                    $board = $lineData[0]; //Which board was it posted on
                                    $author = $lineData[1]; //Which author posted it
                                    $postID = (int) $lineData[2]; //what id is the post on
                                    $mod    = (int) $lineData[3]; //Was the post made by a mod?s
                                    $type   = (int) $lineData[4]; //Explained in docs/types.md
                                    $date   = str_replace(";", ":", $lineData[5]);
                                    $content = $lineData[6];
                                    $content = (strlen($content) > 9) ? substr($content,0,10).'...' : $content;

                                    
                                    //Set up the post data array
                                    $currentPostData = array(
                                        "postID"        => $postID,
                                        "author"        => $author,
                                        "postBoard"     => $board,
                                        "isPinned"      => 0,
                                        "isUserMod"     => $mod,
                                        "userType"      => $type,
                                        "date"          => $date,
                                        "contents"      => $content
                                    );

                                    array_push($postData, $currentPostData);
                                }

                                fclose($postDataFile);
                            }

                            //Merge the 2 arrays, so that pinned posts are
                            //on the top, and non pinned posts are not on the top

                            if (!filesize("database/posts/recent.postData") < 2) {
                                foreach ($postData as $data) {
                                    if ($data["isPinned"]) echo "📌";
                                    else echo "⠀&nbsp;";
                                        
                                    
                                    //echo the contents
                                    //Notice: changing the $data thing to variables
                                    //causes it to break. Dont touch it, no idea
                                    //how it works
                                    echo "
                                        /<a href='/{$data["postBoard"]}'>{$data["postBoard"]}</a>/ >> 
                                        (<a href='/profile/{$data["author"]}'>{$data["author"]}</a>):
                                        <a href='/{$data["postBoard"]}/{$data["postID"]}'>{$data["contents"]}</a>
                                        <br>\n";
                                }
                            } else 
                                echo "<p>There are no recent posts</p>"
                        ?>
                    </div>
                </div>
                <div>
                    <div class="index-sidebar" style="margin-left: auto;">
                        <?php
                            readfile("text/sidebar.html");
                        ?>
                    </div>   
                </div>
            </div>
        </div>
        <br>

        <div class="footer">
            <?php echoFooterText(); ?>
        </div>
    </body>
</html>