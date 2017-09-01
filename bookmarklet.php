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
        <title>Saucin! - Bookmarklet</title>
        <link rel="stylesheet" type="text/css" href="black.css" />
    </head>
    <body>
        <h1><a href="./" target="_self">Saucin!</a> - Bookmarklet</h1>
        <p>Saucin provides a bookmarklet so you may quickly create a Saucin URL of whatever page you are viewing in your web browser. For more information on what a bookmarklet is, see the <a href="https://en.wikipedia.org/wiki/Bookmarklet" target="_blank">Wikipedia article</a>.</p>
        <h2><a href="javascript:void(location.href='https://sauc.in/create.php?target_url='+encodeURIComponent(location.href))" target="_blank">Create Saucin URL</a></h2>
        <p>Add the above link to your bookmark's toolbar, or wherever it is most accessible. All you have to do is click it to create a Saucin URL of your current page. Don't just click this link from this page, it will give you an error.</p>
        <?php include('footer.php'); ?>
    </body>
</html>
