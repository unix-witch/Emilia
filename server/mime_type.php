<?php 

function set_mime_type($file) {                                                 //clusterfuck that tells browser file mime
    $extension = pathinfo($file, PATHINFO_EXTENSION);
    $mimetype = "";

    switch ($extension) {
        case "acc": $mimetype = "audio/aac";                    break;
        case "abw": $mimetype = "application/x-abiword";        break;
        case "arc": $mimetype = "application/x-freearc";        break;
        case "avif": $mimetype = "image/avif";                  break;
        case "avi": $mimetype = "video/x-msvideo";              break;
        case "bin": $mimetype = "application/octet-stream";     break;
        case "bmp": $mimetype = "image/bmp";                    break;
        case "bz": $mimetype = "application/x-bzip";            break;
        case "bz2": $mimetype = "application/x-bzip2";          break;
        case "css": $mimetype = "text/css";                     break;
        case "doc": $mimetype = "application/msword";           break;
        case "gz": $mimetype = "application/gzip";              break;
        case "jpeg": $mimetype = "image/jpeg";                  break;
        case "jpg": $mimetype = "image/jpeg";                   break;
        case "js": $mimetype = "text/javascript";               break;
        case "json": $mimetype = "application/json";            break;
        case "midi": $mimetype = "audio/midi";                  break;
        case "mp3": $mimetype = "audio/mpeg";                   break;
        case "mp4": $mimetype = "video/mp4";                    break;
        case "mpeg": $mimetype = "video/mpeg";                  break;
        case "oga": $mimetype = "video/ogg";                    break;
        case "ogv": $mimetype = "video/ogg";                    break;
        case "otf": $mimetype = "font/otf";                     break;
        case "png": $mimetype = "image/png";                    break;
        case "pdf": $mimetype = "application/pdf";              break;
        case "webm": $mimetype = "video/webm";                  break;
        case "webp": $mimetype = "image/webp";                  break;
        case "zip": $mimetype = "application/zip";              break;
        case "7z": $mimetype = "application/x-7z-compressed";   break;

        default:
            $mimetype = "text/html";                            break;          //Use HTML if unknown mimetype
    }

    header("Content-type: {$mimetype}");                                        //Set the header of the mim type
}

?>