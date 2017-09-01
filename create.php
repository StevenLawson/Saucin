<?php
/*
  Copyright (C) 2010-2017 Steven Lawson

  Licensed under the Apache License, Version 2.0 (the "License");
  you may not use this file except in compliance with the License.
  You may obtain a copy of the License at

  http://www.apache.org/licenses/LICENSE-2.0

  Unless required by applicable law or agreed to in writing, software
  distributed under the License is distributed on an "AS IS" BASIS,
  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
  See the License for the specific language governing permissions and
  limitations under the License.
 */
require_once './vendor/autoload.php';
require_once './common.php';

use ReCaptcha\ReCaptcha;

$success = false;

$user_ip = $_SERVER['REMOTE_ADDR'];

$target_url = SaucinCommon::filterInputSafe(INPUT_REQUEST, 'target_url');
$recaptcha_response = SaucinCommon::filterInputSafe(INPUT_POST, 'g-recaptcha-response');
$use_custom_url = SaucinCommon::stringEquals(SaucinCommon::filterInputSafe(INPUT_POST, 'use_custom_url'), 'true', true);
$use_password = SaucinCommon::stringEquals(SaucinCommon::filterInputSafe(INPUT_POST, 'use_password'), 'true', true);
$custom_key = SaucinCommon::filterInputSafe(INPUT_POST, 'custom_key');
$password = SaucinCommon::filterInputSafe(INPUT_POST, 'password');

if ($target_url === false || strlen($target_url) <= 4)
{
    die('Invalid URL');
}
$target_url = substr(trim($target_url), 0, 1990);

try
{
    $mysqli = SaucinCommon::getSQLConnection();
}
catch (Exception $ex)
{
    die($ex->getMessage());
}

$is_verified = false;

$stmt = $mysqli->prepare('SELECT banned FROM users WHERE user_ip = ?');
$stmt->bind_param('s', $user_ip);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($banned);
$stmt->fetch();
$num_rows = $stmt->num_rows;
$stmt->close();

if ($num_rows)
{
    if ($banned)
    {
        die("Your IP ($user_ip) has been blocked from accessing this system.");
    }
    else
    {
        $is_verified = true;
    }
}

if (!$is_verified)
{
    $recaptcha_passed = false;

    if ($recaptcha_response !== false)
    {
        $recaptcha = (new ReCaptcha(GoogleSettings::RECAPTCHA_SECRET_KEY))->verify($recaptcha_response, $user_ip);
        if ($recaptcha->isSuccess() && $recaptcha->getHostName() === $_SERVER['HTTP_HOST'])
        {
            $recaptcha_passed = true;
        }
    }

    if ($recaptcha_passed)
    {
        $stmt = $mysqli->prepare('INSERT INTO users (user_ip) VALUES (?)');
        $stmt->bind_param('s', $user_ip);
        $stmt->execute();
        $stmt->close();
    }
    else
    {
        die('Captcha solve required. Please solve the captcha @ https://sauc.in');
    }
}

$parsed_url = parse_url($target_url);
if (!isset($parsed_url['scheme']))
{
    $target_url = "http://$target_url";
}

$stmt = $mysqli->prepare('SELECT lookup_key FROM urls WHERE target_url = ? AND is_custom = 0');
$stmt->bind_param('s', $target_url);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($key);
$stmt->fetch();
$exists = $stmt->num_rows > 0;
$stmt->close();

if ($exists && !($use_custom_url || $use_password))
{
    $success = true;
    $new_url = "https://sauc.in/$key";
}
else
{
    if ($use_custom_url)
    {
        if ($custom_key === false)
        {
            $use_custom_url = false;
        }
        else
        {
            $custom_key = trim($custom_key);
            if (strlen($custom_key) < 2)
            {
                $use_custom_url = false;
            }
        }
    }

    if ($use_custom_url)
    {
        $custom_key = '-' . substr(preg_replace('%[\W]%', '_', $custom_key), 0, 20);

        $stmt = $mysqli->prepare('SELECT lookup_key FROM urls WHERE lookup_key = ?');
        $stmt->bind_param('s', $custom_key);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0)
        {
            $use_custom_url = false;
        }
        $stmt->close();
    }

    $new_key = "";

    if ($use_custom_url)
    {
        $new_key = $custom_key;
    }
    else
    {
        $stmt = $mysqli->prepare("SELECT AUTO_INCREMENT FROM information_schema.tables WHERE TABLE_NAME = 'urls'");
        $stmt->execute();
        $stmt->bind_result($next_id);
        $stmt->fetch();
        $stmt->close();

        $new_key = SaucinCommon::intToKey($next_id);
    }

    if ($use_password)
    {
        if ($password === false)
        {
            $use_password = false;
        }
        else
        {
            $password = trim($password);
            if (strlen($password) < 2)
            {
                $use_password = false;
            }
        }
    }

    if ($use_password)
    {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare('INSERT INTO urls (user_ip, lookup_key, target_url, password, is_custom) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('ssssi', $user_ip, $new_key, $target_url, $hashed_password, $use_custom_url);
        $stmt->execute();
        $stmt->close();
    }
    else
    {
        $stmt = $mysqli->prepare('INSERT INTO urls (user_ip, lookup_key, target_url, is_custom) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('sssi', $user_ip, $new_key, $target_url, $use_custom_url);
        $stmt->execute();
        $stmt->close();
    }

    $stmt = $mysqli->prepare('UPDATE users SET num_urls = num_urls + 1 WHERE user_ip = ?');
    $stmt->bind_param('s', $user_ip);
    $stmt->execute();
    $stmt->close();

    $success = true;

    $new_url = "https://sauc.in/$new_key";
}

if ($success)
{
    $old_len = strlen($target_url);
    $new_len = strlen($new_url);
    ?>
    <!doctype html>

    <html lang="en">
        <head>
            <meta charset="utf-8">
            <title>Saucin! - URL Shortening Service</title>
            <link rel="stylesheet" type="text/css" href="black.css"/>
        </head>
        <body>
            <h1>A shortened URL has been created.</h1>
            <h2>Please note that this URL is case sensitive, and if you used a custom URL the system might have modifed it.</h2>
            <p>Old URL <?php echo '(' . $old_len . ' characters)' ?></p>
            <input name="old" type="text" value="<?php echo $target_url; ?>" size="60" readonly="readonly" onFocus="this.select()" />
            <p>New URL <?php echo '(' . $new_len . ' characters)' ?></p>
            <input name="new" type="text" value="<?php echo $new_url; ?>" size="60" readonly="readonly" onFocus="this.select()" />
            <?php
            if ($use_password)
            {
                ?>
                <p>You have chosen to password-protect this redirect. Your password is:</p>
                <input name="new" type="text" value="<?php echo $password; ?>" size="60" readonly="readonly" onFocus="this.select()" />
                <?php
            }
            if ($old_len - $new_len > 0)
            {
                printf('<p>This new URL is %u%% (%u characters) shorter than your old URL!</p>', round((100 * $old_len - 100 * $new_len) / $old_len), $old_len - $new_len);
                echo PHP_EOL;
            }
            ?>
            <hr />
            <p><a href="./" target="_self">Back to Saucin!</a></p>
        </body>
    </html>
    <?php
}
