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
require_once './common.php';

if (!preg_match('#https?://(?:www\.)?sauc\.in#i', $_SERVER['HTTP_REFERER']))
{
    header('HTTP/1.1 301 Moved Permanently');
    header('Status: 301 Moved Permanently');
    header('Location: https://sauc.in');
    die();
}

$key = SaucinCommon::filterInputSafe(INPUT_POST, 'key');
$password_user_raw = SaucinCommon::filterInputSafe(INPUT_POST, 'password');

if ($key !== false && !empty($key))
{
    try
    {
        $mysqli = SaucinCommon::getSQLConnection();
    }
    catch (Exception $ex)
    {
        die($ex->getMessage());
    }
    $stmt = $mysqli->prepare('SELECT target_url, password FROM urls WHERE lookup_key = ?');
    $stmt->bind_param('s', $key);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($target_url, $password_db_hashed);
    $stmt->fetch();
    $found = $stmt->num_rows > 0;
    $stmt->close();
    $mysqli->close();

    $message = 'An unknown error occured.';
    $redirect = false;

    if ($found)
    {
        if (empty($password_db_hashed))
        {
            $redirect = $target_url;
        }
        else
        {
            if (password_verify($password_user_raw, $password_db_hashed))
            {
                $redirect = $target_url;
            }
            else
            {
                $message = 'Incorrect password.';
            }
        }
    }
    else
    {
        $message = 'That key was not found in our database. Please note that keys are case sensitive.';
    }

    if ($redirect !== false)
    {
        header('HTTP/1.1 301 Moved Permanently');
        header('Status: 301 Moved Permanently');
        header("Location: $redirect");
        die();
    }
    else
    {
        printf('<html lang="en"><head><meta charset="utf-8"><title>Redirection Error</title></head><body><h1><a href="./" target="_self">Saucin!</a></h1><h2>There has been an error:</h2><p>%s</p></body></html>', $message);
    }
}
