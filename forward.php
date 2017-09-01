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

$parameters = explode('/', $_SERVER['REQUEST_URI']);
$key = isset($parameters[1]) ? $parameters[1] : false;
$option = isset($parameters[2]) ? $parameters[2] : false;
$password_user_raw = isset($parameters[3]) ? $parameters[3] : false;

if ($key !== false && !empty($key) && !SaucinCommon::stringEquals($key, 'forward.php', true))
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

    if ($found)
    {
        if (SaucinCommon::stringEquals($option, '?', true))
        {
            header("Content-type: text/plain");

            if (empty($password_db_hashed))
            {
                die($target_url);
            }
            else
            {
                if (password_verify($password_user_raw, $password_db_hashed))
                {
                    die($target_url);
                }
                else
                {
                    die('Incorrect password.');
                }
            }
        }
        else
        {
            if (empty($password_db_hashed))
            {
                header('HTTP/1.1 301 Moved Permanently');
                header('Status: 301 Moved Permanently');
                header("Location: $target_url");
                die();
            }
            else
            {
                if (SaucinCommon::stringEquals($option, 'P', true))
                {
                    if (password_verify($password_user_raw, $password_db_hashed))
                    {
                        header('HTTP/1.1 301 Moved Permanently');
                        header('Status: 301 Moved Permanently');
                        header("Location: $target_url");
                        die();
                    }
                    else
                    {
                        $message = 'Incorrect password.';
                    }
                }
                else
                {
                    printf('<html lang="en"><head><meta charset="utf-8"></head><body><form action="https://sauc.in/pw_redirect.php" method="post" target="_self"><input name="key" type="hidden" value="%s"/><label>Password<input type="text" name="password"/></label><input type="submit" name="submit" value="Submit"/></form></body></html>', $key);
                    die();
                }
            }
        }
    }
    else
    {
        $message = 'That key was not found in our database. Please note that keys are case sensitive.';
    }
}
else
{
    $message = 'No key was entered.';
}
printf('<html lang="en"><head><meta charset="utf-8"><title>Redirection Error</title></head><body><h1><a href="./" target="_self">Saucin!</a></h1><h2>There has been an error:</h2><p>%s</p></body></html>', $message);
