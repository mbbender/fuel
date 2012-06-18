<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo \Config::get('app.name','My Site'); ?></title>
    <?php echo Asset::css('bootstrap.css'); ?>
    <?php echo Asset::css('bootstrap_overrides.css'); ?>
    <style>
        body {margin-top:90px}
    </style>

    <?php echo Asset::js(array(
        'http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js',
        'bootstrap.js'
    )); ?>
</head>
<body>

<!-- Nav bar -->
<div class="navbar navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container">
            <ul class="nav font15">
                <a class="pull-left" style="padding-top: 6px; padding-right: 30px;" href="/">
                    <?php echo Asset::img('logo.png',array('id'=>'logo')) ?>
                </a>
                <li class="active">
                    <li><a href="#">How it Works</a></li>
                </li>

                <li><a href="#">Browse</a></li>
                <li><a href="#">Pricing</a></li>
                <li><a href="#">About</a></li>
            </ul>
            <ul class="nav pull-right" style="font-size:13px">
                <li><a href="auth/register">Sign Up</a></li>
                <li><a href="auth/login">Log In</a></li>
            </ul>
        </div>
    </div>
</div>


<div class="container">
    <div class="row">
        <div class="span12">
            <?php if(isset($title)): ?><h1><?php echo $title; ?></h1><hr><?php endif ?>

            <?php if (Session::get_flash('success')): ?>
            <div class="alert alert-success">
                <button class="close" data-dismiss="alert">×</button>
                <?php echo implode('</p><p>', (array) Session::get_flash('success')); ?>
            </div>
            <?php endif; ?>
            <?php if (Session::get_flash('error')): ?>
            <div class="alert alert-error">
                <button class="close" data-dismiss="alert">×</button>
                <?php echo implode('</p><p>', (array) Session::get_flash('error')); ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="span12">
            <?php echo $content; ?>
        </div>
    </div>
    <hr/>
    <footer>
        <p class="pull-right">Page rendered in {exec_time}s using {mem_usage}mb of memory.</p>
        <p>
            <a href="http://fuelphp.com">FuelPHP</a> is released under the MIT license.<br>
            <small>Version: <?php echo e(Fuel::VERSION); ?></small>
        </p>
    </footer>
</div>

</body>
</html>