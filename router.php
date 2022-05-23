<?php
ob_start();
ini_set('display_startup_errors', '1');
ini_set('file_uploads', '1');
ini_set('post_max_size', '100M');
ini_set('upload_max_filesize', '100M');

require './configs/router.php';
require './configs/boards.php';
require './configs/site.php';

require './server/mime_type.php';                                               //Script for file mime types

require __DIR__ . '/http_codes/403.php';                                        //403 status code
require __DIR__ . '/http_codes/404.php';                                        //404 status code

$posts_database = array("recent" => array(), "max" => array());
foreach ($boards as $board_name => $board_desc) {                               //Loop over boards to init database
    if (!file_exists("./database/posts/$board_name/")){                         //Check if folder exists for board
        mkdir("./database/posts/$board_name");                                  //Create board if not exists
        $posts_database["recent"][$board_name] = array();                       //Set recent if it does not exist
        $posts_database["max"][$board_name] = 0;                                //No posts so the max is 0

        $fp = fopen("./database/posts/posts.json", "w");
        fwrite($fp, json_encode($posts_database));
        
        fclose($fp);
    }
}

$user_request = substr($_SERVER['REQUEST_URI'], 1);                             //Remove the / in front of the request
$user_request = strtok($user_request, '/');                                     //Remove any other / in the request
$user_request = strtok($user_request, '?');                                     //remove any ? in the request

ini_set("session.gc_maxlifetime", $session_storage_time);                       //How long session is stored
session_set_cookie_params($session_storage_time);                               //Ditto, php fuckery

session_start();                                                                //Start a PHP session



if (empty($_SESSION)) {
    $_SESSION['banned'] = 0;
    $_SESSION['authed'] = 0;
    $_SESSION['perms']  = -1;
}

elseif ($_SESSION['banned'])    require __DIR__ . '/views/banned.php';          //Show this page if user is banned

switch ($user_request) {
    case '':                require __DIR__ . '/views/index.php';       break;  //Include the index file
    case 'login':           require __DIR__ . '/views/login.php';       break;  //Include the login file
    case 'register':        require __DIR__ . '/views/register.php';    break;  //Include the register file
    case 'session':         require __DIR__ . '/server/reset.php';      break;  //File that will reset the session if needed
    case 'updates':         require __DIR__ . '/server/update.php';     break;  //Updates that the server has

    case 'graph-image.png': readfile("./images/graph-image.png");       break;  //Send the open graph image

    default:
        $request      = $_SERVER['REQUEST_URI'];                                //Server URI used for special paths
        $file         = '.' . $request;                                         //File data
        $extension    = pathinfo($file, PATHINFO_EXTENSION);                    //get the extension of the file
        $thread_check = explode('/', $request);
        $thread_file  = "";

        $thread_check = \array_filter($thread_check, static function ($value){
            return $value !== "/";
        });

        set_mime_type($file);                                                   //Set the mime type of the file


        if (sizeof($thread_check) >= 2 && $thread_check[2] != '/')
            if (is_numeric($thread_check[2]))                                   //Threads can only be numbers
                $thread_file = 
                    "./database/posts/$user_request/$thread_check[2].json";
            
            elseif (
                strcmp($thread_check[1], "usr") == 0 && 
                !is_numeric($thread_check[2])) {
                    
                    require __DIR__ . '/views/user.php';
                    die;
                }


        if      (in_array($extension, $blacklisted_files)) c404();              //Check if blacklisted file extension
        elseif  (is_file($file)) readfile($file);                               //Send the file if it exists
        elseif  (sizeof($thread_check) > 1 && is_numeric($thread_check[2]))
            require __DIR__ . '/views/thread.php';
        elseif  (in_array($user_request, array_keys($boards)))                  //Check if the user wants to visit a board
            require __DIR__ . '/views/board.php';                               //Send them to the board
            
        else    c404();                                                         //Send a 404 if the request isnt valid
}

?>