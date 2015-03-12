<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title><?php echo _get('page.title');?> | <?php echo _get('site.title');?></title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <!-- Theme style -->
        <link href="<?php echo m_turl();?>css/adminlte.css" rel="stylesheet" type="text/css" />
		<?= Metrofw_Template::parseSection('pagecss');?>

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
          <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->
    </head>
    <body class="skin-blue">
        <!-- header logo: style can be found in header.less -->
        <header class="header">
            <a href="<?php echo m_appurl();?>" class="logo">
                <!-- Add the class icon to your logo image or logo icon to add the margining -->
                <?php echo _get('site.name'); ?>
            </a>
            <!-- Header Navbar: style can be found in header.less -->
            <nav class="navbar navbar-static-top" role="navigation">
                <!-- Sidebar toggle button-->
                <a href="#" class="navbar-btn sidebar-toggle" data-toggle="offcanvas" role="button">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </a>
                <div class="navbar-right">
                    <ul class="nav navbar-nav">
                        <!-- Messages: style can be found in dropdown.less-->
<!--
                        <li class="dropdown messages-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <i class="fa fa-envelope"></i>
                                <span class="label label-success">4</span>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="header">You have 4 messages</li>
                                <li>
-->
                                    <!-- inner menu: contains the actual data -->
<!--
                                    <ul class="menu">
                                        <li>
-->
                                            <!-- start message -->
<!--
                                            <a href="#">
                                                <div class="pull-left">
                                                    <img src="<?php echo m_turl();?>img/avatar3.png" class="img-circle" alt="User Image"/>
                                                </div>
                                                <h4>
                                                    Support Team
                                                    <small><i class="fa fa-clock-o"></i> 5 mins</small>
                                                </h4>
                                                <p>Why not buy a new awesome theme?</p>
                                            </a>
                                        </li>
-->
                                        <!-- end message -->
<!--
                                        <li>
                                            <a href="#">
                                                <div class="pull-left">
                                                    <img src="<?php echo m_turl();?>img/avatar2.png" class="img-circle" alt="user image"/>
                                                </div>
                                                <h4>
                                                    AdminLTE Design Team
                                                    <small><i class="fa fa-clock-o"></i> 2 hours</small>
                                                </h4>
                                                <p>Why not buy a new awesome theme?</p>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <div class="pull-left">
                                                    <img src="<?php echo m_turl();?>img/avatar.png" class="img-circle" alt="user image"/>
                                                </div>
                                                <h4>
                                                    Developers
                                                    <small><i class="fa fa-clock-o"></i> Today</small>
                                                </h4>
                                                <p>Why not buy a new awesome theme?</p>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <div class="pull-left">
                                                    <img src="<?php echo m_turl();?>img/avatar2.png" class="img-circle" alt="user image"/>
                                                </div>
                                                <h4>
                                                    Sales Department
                                                    <small><i class="fa fa-clock-o"></i> Yesterday</small>
                                                </h4>
                                                <p>Why not buy a new awesome theme?</p>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <div class="pull-left">
                                                    <img src="<?php echo m_turl();?>img/avatar.png" class="img-circle" alt="user image"/>
                                                </div>
                                                <h4>
                                                    Reviewers
                                                    <small><i class="fa fa-clock-o"></i> 2 days</small>
                                                </h4>
                                                <p>Why not buy a new awesome theme?</p>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="footer"><a href="#">See All Messages</a></li>
                            </ul>
                        </li>
-->
<?php
$user = $request->getUser();
?>
                        <!-- User Account: style can be found in dropdown.less -->
                        <li class="dropdown user user-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <i class="glyphicon glyphicon-user"></i>
                                <span><?php echo $user->getDisplayName();?><i class="caret"></i></span>
                            </a>
                            <ul class="dropdown-menu">
                                <!-- User image -->
                                <li class="user-header bg-light-blue">
<!--
                                    <img src="<?php echo m_turl();?>metrou/img/show/profile.png" class="img-circle" alt="User Image" />
-->
                                    <p>
                                       <?php echo $user->getDisplayName();?>
                                    </p>
                                </li>
                                <!-- Menu Footer-->
                                <li class="user-footer">
<!--
                                    <div class="pull-left">
                                        <a href="<?php echo m_appurl('olam');?>" class="btn btn-default btn-flat">Profile</a>
                                    </div>
-->
                                    <div class="pull-right">
                                        <a href="<?php echo m_appurl('dologout');?>" class="btn btn-default btn-flat">Sign Out</a>
                                    </div>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>
        <div class="wrapper row-offcanvas row-offcanvas-left">
            <!-- Left side column. contains the logo and sidebar -->
            <aside class="left-side sidebar-offcanvas">
                <!-- sidebar: style can be found in sidebar.less -->
                <section class="sidebar">
                    <!-- search form -->
<!--
                    <form action="#" method="get" class="sidebar-form">
                        <div class="input-group">
                            <input type="text" name="q" class="form-control" placeholder="Search..."/>
                            <span class="input-group-btn">
                                <button type='submit' name='search' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i></button>
                            </span>
                        </div>
                    </form>
-->
                    <!-- /.search form -->
                    <!-- sidebar menu: : style can be found in sidebar.less -->


					<ul class="sidebar-menu">
						<li <?php echo (strlen($request->requestedUrl) == 0) ? 'class="active"':'';?>>
							<a href="<?= m_appurl();?>">
								<i class="icon-dashboard"></i>
								<span class="menu-text">Dashboard</span>
							</a>
						</li>

						<li <?php echo (strpos($request->requestedUrl, 'cportal/ticket') !== FALSE) ? 'class="active"':'';?>>
							<a href="<?= m_appurl('cportal/ticket');?>">
								<i class="icon-ticket"></i>
								<span class="menu-text">Tickets</span>
							</a>
						</li>
						<li <?php echo (strpos($request->requestedUrl, 'cpemp') !== FALSE) ? 'class="active"':'';?>>
							<a href="<?= m_appurl('emp');?>">
								<i class="icon-user"></i>
								<span class="menu-text">Employees</span>
							</a>
						</li>
<!--
						<li <?php echo (strpos($request->requestedUrl, 'sandr') !== FALSE) ? 'class="active"':'';?>>
							<a href="<?= m_appurl('sandr');?>">
								<i class="icon-truck"></i>
								<span class="menu-text">S/R</span>
							</a>
						</li>
-->
<!--
						<li <?php echo (strpos($request->requestedUrl, 'reports') !== FALSE) ? 'class="active"':'';?>>
							<a href="<?= m_appurl('reports');?>">
								<i class="icon-signal"></i>
								<span class="menu-text">Reporting</span>
							</a>
						</li>
-->

					</ul><!-- /.sidebar-menu -->

                </section>
                <!-- /.sidebar -->
            </aside>

            <!-- Right side column. Contains the navbar and content of the page -->
            <aside class="right-side">
                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <h1>
						<?php echo _get('page.header');?>
                        <small><?php echo _get('page.subheader');?></small>
                    </h1>
<!--
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                        <li class="active">Dashboard</li>
                    </ol>
-->
                </section>

                <!-- Main content -->
                <section class="content">
						<div class="row">
							<div class="col-xs-12">
								<!-- PAGE CONTENT BEGINS -->
<? /* Metrofw_Template::parseSection('sparkmsg'); */?>

<?= Metrofw_Template::parseSection('main');?>

								<!-- PAGE CONTENT ENDS -->
							</div><!-- /.col -->
						</div><!-- /.row -->

                </section><!-- /.content -->
            </aside><!-- /.right-side -->
        </div><!-- ./wrapper -->


        <!-- build:js js/built/app.js -->
        <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
        <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js" type="text/javascript"></script>
        <script src="//code.jquery.com/ui/1.11.1/jquery-ui.min.js" type="text/javascript"></script>
        <!-- endbuild -->

		<?= Metrofw_Template::parseSection('pagejs');?>

        <!-- AdminLTE App -->
        <script src="<?php echo m_turl();?>js/AdminLTE/app.js" type="text/javascript"></script>
    </body>
</html>
