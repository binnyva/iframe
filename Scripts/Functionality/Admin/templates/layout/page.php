<!DOCTYPE html>
<html>
<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php echo $config['site_title'] ?> : Admin</title>

    <!-- Core CSS - Include with every page -->
    <link href="<?php echo $config['site_url'] ?>bower_components/bootstrap/dist/css/bootstrap.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo $config['site_url'] ?>bower_components/bootstrap/dist/css/bootstrap-theme.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo $config['site_url'] ?>images/silk_theme.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo $config['site_url'] ?>css/style.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo $config['site_url'] ?>admin/themes/sb-admin-v2/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="<?php echo $config['site_url'] ?>admin/themes/sb-admin-v2/css/sb-admin.css" rel="stylesheet">
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

            <ul class="nav navbar-top-links navbar-right">
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="fa fa-gear fa-fw"></i>  <i class="fa fa-caret-down"></i>
                    </a>
                    
                    <ul class="dropdown-menu dropdown-user">
                        <li><a href="<?php echo $config['site_url'] ?>"><i class="fa fa-home fa-fw"></i> View Site</a>
                        </li>
                        <li><a href="setting.php"><i class="fa fa-gear fa-fw"></i> Settings</a>
                        </li>
                        <li class="divider"></li>
                        <li><a href="index.php?action=logout"><i class="fa fa-sign-out fa-fw"></i> Logout</a>
                        </li>
                    </ul>
                    <!-- /.dropdown-user -->
                </li>
                <!-- /.dropdown -->
            </ul>
            <!-- /.navbar-top-links -->

            <div class="navbar-default navbar-static-side" role="navigation">
                <div class="sidebar-collapse">
                    <ul class="nav" id="side-menu">
                        <li class="sidebar-search">
                            <div class="input-group custom-search-form">
                                <input type="text" class="form-control" placeholder="Search...">
                                <span class="input-group-btn">
                                <button class="btn btn-default" type="button">
                                    <i class="fa fa-search"></i>
                                </button>
                            </span>
                            </div>
                            <!-- /input-group -->
                        </li>
                        <li><a href="index.php"><i class="fa fa-dashboard fa-fw"></i> Dashboard</a></li>
                    </ul>
                    <!-- /#side-menu -->
                </div>
                <!-- /.sidebar-collapse -->
            </div>
            <!-- /.navbar-static-side -->
        </nav>

        <div id="page-wrapper">
<?php 
/////////////////////////////////// The Template file will appear here ////////////////////////////

if(!empty($GLOBALS['page'])) {
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
