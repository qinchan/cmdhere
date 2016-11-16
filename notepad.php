<?php

// Directory to save user documents.
$data_directory = '_notepad';

/**
 * Sanitizes a string to include only alphanumeric characters.
 *
 * @param  string $string the string to sanitize
 * @return string         the sanitized string
 */
function sanitizeString($string) {
    return preg_replace("/[^a-zA-Z0-9]+/", "", $string);
}

/**
 * Generates a random string.
 *
 * @param  integer $length the length of the string
 * @return string          the new string
 *
 * Initially based on http://stackoverflow.com/a/4356295/1391963
 */
function generateRandomString($length = 5) {
    // Do not generate ambiguous characters. See http://ux.stackexchange.com/a/53345/25513
    $characters = '23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}


// Disable caching.
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (empty($_GET['f']) || sanitizeString($_GET['f']) !== $_GET['f']) {

    // User has not specified a valid name, generate one.
    header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/' . generateRandomString());
    die();
}

$name = sanitizeString($_GET['f']);
$path = $data_directory . '/' . $name;

if (isset($_POST['t'])) {

    // Update file.
    file_put_contents($path, $_POST['t']);
    die();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php print $name; ?></title>
    <link href="data:image/x-icon;base64,R0lGODlhEAAQAKIGAL7FzEBUaLK5wnOBkJCbp9nd4f///wAAACH5BAEAAAYALAAAAAAQABAAAANJaKrR7msFMga1o8UguvcBECyDUJ1lJjIdWgrharxfG49cDQL8SNeqXk4H4/V+n2BB9GjGjCOJcQrdUHmFZZRxnW4NzyvhC3ZCEgA7" rel="icon" type="image/x-icon" />
    <style type="text/css">
        body {
            margin: 0;
            background: #ebeef1;
        }

        #content {
            font-size: 100%;
            margin: 0;
            padding: 20px;
            overflow-y: auto;
            resize: none;
            width: 100%;
            height: 100%;
            min-height: 100%;
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
            border: 1px #ddd solid;
            outline: none;
        }

        .container {
            position: absolute;
            top: 20px;
            right: 20px;
            bottom: 20px;
            left: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <textarea id="content"><?php
            if (file_exists($path)) {
                print htmlspecialchars(file_get_contents($path), ENT_QUOTES, 'UTF-8');
            }
?></textarea>
    </div>
    <script>
        function uploadContent() {
            if (content !== textarea.value) {
                var temp = textarea.value;
                var request = new XMLHttpRequest();
                request.open('POST', window.location.href, true);
                request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
                request.onload = function() {
                    if (request.readyState === 4) {
                        content = temp;
                        setTimeout(uploadContent, 1000);
                    }
                }
                request.onerror = function() {
                    setTimeout(uploadContent, 1000);
                }
                request.send("t=" + encodeURIComponent(temp));
            }
            else {
                setTimeout(uploadContent, 1000);
            }
        }

        var textarea = document.getElementById('content');
        var content = textarea.value;
        textarea.focus();
        uploadContent();
    </script>
</body>
</html>

