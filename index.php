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
        <title>Saucin! - URL Shortening Service</title>

        <meta name="description" content="Sauc.in is a free URL shortening service. Turn a long URL into something that you can easily remember or send to a friend!" />
        <meta name="keywords" content="url,shortening,short url,short,url shortening,sauc.in,sauc,saucin" />
        <meta name="author" content="Steven Lawson" />

        <link rel="stylesheet" type="text/css" href="black.css"/>

        <script src='https://www.google.com/recaptcha/api.js'></script>
    </head>

    <body>
        <h1 align="center">Saucin!</h1>
        <form id="create" name="create" method="post" action="https://sauc.in/create.php" onsubmit="return validate();">
            <table width="100%" border="0" align="center" cellpadding="2" cellspacing="2">
                <tr>
                    <th align="center" valign="middle" colspan="2">Create a Saucin (shortened) URL:</th>
                </tr>
                <tr>
                    <td width="50%" align="right" valign="middle">Your long URL:</td>
                    <td align="left" valign="middle"><input id="target_url" name="target_url" type="text" size="60" maxlength="1990" /></td>
                </tr>
                <tr>
                    <td width="50%" align="right" valign="middle">Would you like to use a custom shortcut for this URL?</td>
                    <td align="left" valign="middle"><label><input id="use_custom_url" name="use_custom_url" type="checkbox" value="true" />Yes</label></td>
                </tr>
                <tr>
                    <td width="50%" align="right" valign="middle">Enter your custom shortcut here (3 to 20 characters):</td>
                    <td align="left" valign="middle"><input id="custom_key" name="custom_key" type="text" size="60" maxlength="20" onkeyup="checkCustURL();"/></td>
                </tr>
                <tr>
                    <td width="50%" align="right" valign="middle">Would you like to protect this URL with a password?</td>
                    <td align="left" valign="middle"><label><input id="use_password" name="use_password" type="checkbox" value="true" />Yes</label></td>
                </tr>
                <tr>
                    <td width="50%" align="right" valign="middle">Enter your desired password here (2 characters min):</td>
                    <td align="left" valign="middle"><input id="password" name="password" type="text" size="60" maxlength="20" onkeyup="checkPassword();"/></td>
                </tr>
                <?php
                try
                {
                    $mysqli = SaucinCommon::getSQLConnection();
                }
                catch (Exception $ex)
                {
                    die($ex->getMessage());
                }

                $is_verified = false;
                $is_banned = false;

                $stmt = $mysqli->prepare('SELECT banned FROM users WHERE user_ip = ?');
                $stmt->bind_param('s', $_SERVER['REMOTE_ADDR']);
                $stmt->execute();
                $stmt->store_result();
                $stmt->bind_result($is_banned);
                $stmt->fetch();
                $is_verified = $stmt->num_rows > 0;
                $stmt->close();

                $mysqli->close();

                if (!$is_verified)
                {
                    ?>
                    <tr><td align="center" valign="middle" colspan="2">Please solve the captcha:</td></tr>
                    <tr><td align="center" valign="middle" colspan="2"><div class="g-recaptcha" data-sitekey="<?php echo GoogleSettings::RECAPTCHA_SITE_KEY; ?>"></div></td></tr>
                    <tr><td align="center" valign="middle" colspan="2"><input id="submit" name="submit" type="submit" value="Shorten URL" /></td></tr>
                    <?php
                }
                else
                {
                    if (!$is_banned)
                    {
                        ?>
                        <tr>
                            <td align="center" valign="middle" colspan="2"><input id="submit" name="submit" type="submit" value="Shorten URL" /></td>
                        </tr>
                        <?php
                    }
                    else
                    {
                        ?>
                        <tr>
                            <td align="center" valign="middle" colspan="2">Sorry, this IP has been blocked from accessing the system. Please review https://sauc.in/tos.php</td>
                        </tr>
                        <?php
                    }
                }
                ?>
            </table>
        </form>

        <hr />

        <p align="center"><a href="previous.php" target="_self">Your Previous Saucin URLS</a> -- <a href="bookmarklet.php" target="_self">Bookmarklet</a> -- <a href="tos.php#contact" target="_self">Contact Information</a></p>

        <?php include('footer.php'); ?>

        <script src='index.js'></script>
    </body>
</html>
