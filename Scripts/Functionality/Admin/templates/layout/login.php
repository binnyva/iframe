<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $config['site_title'] ?> : Admin : Login</title>
    <!-- Core CSS - Include with every page -->
    <link href="<?php echo $config['site_url'] ?>bower_components/bootstrap/dist/css/bootstrap.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo $config['site_url'] ?>bower_components/bootstrap/dist/css/bootstrap-theme.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo $config['site_url'] ?>images/silk_theme.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo $config['site_url'] ?>css/style.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo $config['site_url'] ?>admin/themes/sb-admin-v2/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="<?php echo $config['site_url'] ?>admin/themes/sb-admin-v2/css/sb-admin.css" rel="stylesheet">
    <link href="<?php echo $config['site_url'] ?>admin/css/style.css" rel="stylesheet">
    <?php echo $css_includes ?>
</head>
<body>
    <div id="wrapper">

        <nav class="navbar navbar-default navbar-fixed-top" role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".sidebar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="dashboard.php"><?php echo $config['site_title'] ?> Admin Area</a>
            </div>
            <!-- /.navbar-header -->
        </nav>

        <div id="page-wrapper">
<?php 
/////////////////////////////////// The Template file will appear here ////////////////////////////

if(isset($GLOBALS['page'])) {
    print $GLOBALS['page']->code['top'];
    $GLOBALS['page']->printAction();
    print $GLOBALS['page']->code['bottom'];
} else {
    include($GLOBALS['template']->template); 
}

/////////////////////////////////// The Template file will appear here ////////////////////////////
?>        
        </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->

    <script src="<?php echo $config['site_url'] ?>bower_components/jquery/dist/jquery.min.js" type="text/javascript"></script>
    <script src="<?php echo $config['site_url'] ?>bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="<?php echo $config['site_url'] ?>js/application.js" type="text/javascript"></script>
    <script src="<?php echo $config['site_url'] ?>admin/themes/sb-admin-v2/js/sb-admin.js"></script>
    <script src="<?php echo $config['site_url'] ?>admin/themes/sb-admin-v2/js/plugins/metisMenu/jquery.metisMenu.js"></script>

    <?php echo $js_includes ?>

    


</body>

</html>
