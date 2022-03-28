<?php

include './configs/config.php'; //Config, containing boards
require './scripts/mime_type.php'; //Shit for mime types and static files

$userRequest = substr($_SERVER['REQUEST_URI'], 1);
$userRequest = strtok($userRequest, '/');
$userRequest = strtok($userRequest, '?');

session_start();

if ($_SESSION["banned"] == 1) { 
    require __DIR__ . '/views/banned.php';
    die;

}



switch ($userRequest) { //Get the request URI
    case '': require __DIR__ . '/views/index.php'; break;
    case 'login': require __DIR__ . '/views/login.php'; break;
    case 'register': require __DIR__ . '/views/register.php'; break;
    case 'admin-dash': require __DIR__ . '/views/admin-dash.php'; break;
    case 'reset-session': $_SESSION['authenticated'] = false; break;
    
    case 'tos': readfile("./text/terms.html"); break;
    case 'rules': readfile("./text/rules.html"); break;
    case 'cookies': readfile("./text/cookie.html"); break;
    case 'privacy': readfile("./text/privacy.html"); break;

    default: 
        if (in_array($userRequest, array_keys($boards))) {
            $threadCheck = explode('/', $_SERVER['REQUEST_URI']);

            if (sizeof($threadCheck) >= 2)
                //What the actual fuck how the hell does
                //this happen

                if (is_numeric($threadCheck[2])) {
                    if (file_exists("database/posts/{$userRequest}/{$threadCheck[2]}.thread")) {
                        require __DIR__ . '/views/thread.php';
                        break;
                    }
                }


            require __DIR__ . '/views/board.php';
        
    
        
        
        
        } elseif (is_file(substr($_SERVER['REQUEST_URI'], 1))) {
            $file = substr($_SERVER['REQUEST_URI'], 1);

            if (in_array(pathinfo($file, PATHINFO_EXTENSION), $dontSendFiles)) {

            
                http_response_code(404);
                require __DIR__ . '/views/errors/404.php';
                die;
            }
            
            set_mime_type($file);
            readfile($file);


        } else {
            http_response_code(404); 
            require __DIR__ . '/views/errors/404.php';
        }


        break;
}

?>