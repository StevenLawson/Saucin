<?php require_once './common.php'; ?>
<!doctype html>
<!--
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
-->
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Saucin! - Your Saucin URLs</title>
        <link rel="stylesheet" type="text/css" href="black.css" />
    </head>
    <body>
        <h1><a href="./" target="_self">Saucin!</a> - Your Saucin URLs</h1>
        <p>Saucin URLs that have been registered from this IP (<?php echo $_SERVER['REMOTE_ADDR']; ?>):</p>
        <?php
        try
        {
            $mysqli = SaucinCommon::getSQLConnection();
        }
        catch (Exception $ex)
        {
            die($ex->getMessage());
        }

        $stmt = $mysqli->prepare('SELECT lookup_key, target_url, password FROM urls WHERE user_ip = ?');
        $stmt->bind_param('s', $_SERVER['REMOTE_ADDR']);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($key, $target_url, $password_db_hashed);
        if ($stmt->num_rows > 0)
        {
            ?>
            <table width="100%" border="1" cellspacing="1" cellpadding="1">
                <tr>
                    <th width="33%">Saucin URL</th>
                    <th width="33%">Target URL</th>
                    <th>Password Protected</th>
                </tr>
                <?php
                while ($stmt->fetch())
                {
                    if (!empty($row['password']))
                    {
                        $url_password = 'Yes';
                    }
                    else
                    {
                        $url_password = 'No';
                    }
                    printf('
<tr onMouseOver="this.bgColor=\'#333333\'" onMouseOut="this.bgColor=\'#000000\'">
<td align="center" width="33%%"><a href="%s" target="_blank">%s</a></td>
<td align="center" width="33%%"><a href="%s" target="_blank">%s</a></td>
<td align="center">%s</td>
</tr>' . PHP_EOL, "https://sauc.in/$key", "https://sauc.in/$key", empty($password_db_hashed) ? $target_url : "#", empty($password_db_hashed) ? $target_url : "****", empty($password_db_hashed) ? 'No' : 'Yes' );
                }
                ?>
            </table>
            <?php
        }
        else
        {
            echo '<p>There are no Saucin URLs registered for this IP.</p>';
        }
        $stmt->close();
        $mysqli->close();
        ?>
        <p>Please note that if you were not the first person to register a Saucin URL, it will not be shown in this list!</p>
        <?php include('footer.php'); ?>
    </body>
</html>
