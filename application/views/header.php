	<!DOCTYPE html>
	<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
		<link rel="stylesheet" type="text/css" href="/css/style.min.css">
		<title><?php echo isset($title) ? $title : ''; ?></title>
	</head>
	<body>
		<nav class="navbar navbar-default">
	        <div class="container">
	            <div class="navbar-header">

	                <!-- Collapsed Hamburger -->
	                <button data-target="#app-navbar-collapse" data-toggle="collapse" class="navbar-toggle collapsed" type="button">
	                    <span class="sr-only">Toggle Navigation</span>
	                    <span class="icon-bar"></span>
	                    <span class="icon-bar"></span>
	                    <span class="icon-bar"></span>
	                </button>

	                <!-- Branding Image -->
	                <a href="/" class="navbar-brand">
	                    <?php echo getenv("SCV_TITLE"); ?>
	                </a>
	            </div>

	            <div id="app-navbar-collapse" class="collapse navbar-collapse">
	                <!-- Left Side Of Navbar -->
	                <?php if ( $auth->check() ) { ?>
		                <ul class="nav navbar-nav">
		                    <li><a href="/">Overview</a></li>
		                </ul>
                    <?php } ?>

	                <!-- Right Side Of Navbar -->
	                <ul class="nav navbar-nav navbar-right">
	                    <!-- Authentication Links -->
	                    <?php if ( $auth->check() ) { ?>
                        	<li><a href="/auth/logout">Logout</a></li>
                        <?php } ?>
                        <?php if ( $auth->check() ) { ?>
                        	<li><a href="/auth/register">Register</a></li>
                        <?php } ?>
                    </ul>
	            </div>
	        </div>
	    </nav>
		<div class="container-fluid">
			<div class="row">