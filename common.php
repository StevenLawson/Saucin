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
class MySQLSettings
{
    const HOSTNAME = '';
    const USERNAME = '';
    const PASSWORD = '';
    const DATABASE = '';

}

class GoogleSettings
{
    const RECAPTCHA_SITE_KEY = '';
    const RECAPTCHA_SECRET_KEY = '';
    const ANALYTICS_ID = '';

}

class SaucinCommon
{

    public static function getSQLConnection()
    {
        $mysqli = new mysqli(MySQLSettings::HOSTNAME, MySQLSettings::USERNAME, MySQLSettings::PASSWORD, MySQLSettings::DATABASE);
        if ($mysqli->connect_error)
        {
            throw new Exception("DATABASE_CONNECTION_ERROR,$mysqli->connect_errno,$mysqli->connect_error");
        }
        return $mysqli;
    }

    public static function intToKey($seed)
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $base = strlen($chars);
        $buffer = '';
        do
        {
            $buffer = substr($chars, $seed % $base, 1) . $buffer;
            $seed = floor($seed / $base);
        }
        while ($seed >= 1);
        return $buffer;
    }

    public static function filterInputSafe($type, $variable_name)
    {
        if ($type === INPUT_REQUEST)
        {
            $post = self::filterInputSafe(INPUT_POST, $variable_name);
            if ($post === false)
            {
                return self::filterInputSafe(INPUT_GET, $variable_name);
            }
            else
            {
                return $post;
            }
        }
        else
        {
            $value = filter_input($type, $variable_name);
            if (!isset($value) || is_null($value) || $value === false)
            {
                return false;
            }
            return $value;
        }
    }

    public static function stringEquals($str1, $str2, $ignoreCase = false)
    {
        if ($str1 === false || $str2 === false)
        {
            return false;
        }
        if ($ignoreCase)
        {
            $str1 = strtolower($str1);
            $str2 = strtolower($str2);
        }
        return (strcmp($str1, $str2) === 0);
    }
}
