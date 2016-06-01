<?php
define('ASSETS_URL', '/assets/');
define('ASSETS_LIBS_URL', '/assets/libs/');
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>CSV to Json</title>

        <!-- ================================ -->
        <!-- ========== CSS INCLUDES ========== -->
        <!-- ================================ -->

        <link href="<?php echo ASSETS_LIBS_URL; ?>/css/bootstrap.css" rel="stylesheet"/>
        <link href="<?php echo ASSETS_LIBS_URL; ?>/css/bootstrap.css" media="screen" rel="stylesheet" type="text/css">
        <link href="<?php echo ASSETS_LIBS_URL; ?>/css/buttons.css" media="screen" rel="stylesheet" type="text/css">
        <link href="<?php echo ASSETS_LIBS_URL; ?>/css/font-awesome.css" media="screen" rel="stylesheet" type="text/css">
        <link href="<?php echo ASSETS_LIBS_URL; ?>/css/icomoon.css" media="screen" rel="stylesheet" type="text/css">        
        <link href="<?php echo ASSETS_URL; ?>/css/frontpage.css" rel="stylesheet" type="text/css">

        <!-- ================================ -->
        <!-- ========== JS INCLUDES ========== -->
        <!-- ================================ -->

        <script type="text/javascript" src="<?php echo ASSETS_LIBS_URL; ?>/js/modernizr.custom.js"></script>

    </head>
    <body>
        <div class="container">
            <div class="header clearfix">            
                <h3 class="text-muted">Csv To Json</h3>
            </div>

            <form class="jumbotron" action="download" method="post" enctype="multipart/form-data">
                <h2>Build Language File</h2>
                <p class="lead">Select csv File to upload:<input type="file" name="fileToUpload" id="fileToUpload"></p>                
                <p><input class="btn btn-lg btn-success" type="submit" value="submit" name="submit"></p>
            </form>

            <footer class="footer">
                <p>© 2016 DataLocker Inc. All rights reserved</p>
            </footer>

        </div>       

        <script type="text/javascript" src="<?php echo ASSETS_LIBS_URL; ?>/js/jquery-1.11.0.min.js"></script>
        <script type="text/javascript" src="<?php echo ASSETS_LIBS_URL; ?>/js/bootstrap.min.js"></script>
    </body>
</html>