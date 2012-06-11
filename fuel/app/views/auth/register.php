<?php echo Form::open(array()); ?>

	<?php if (isset($_GET['destination'])): ?>
		<?php echo Form::hidden('destination',$_GET['destination']); ?>
	<?php endif; ?>

	<?php if (isset($login_error)): ?>
<div class="error" xmlns="http://www.w3.org/1999/html"><?php echo $login_error; ?></div>
	<?php endif; ?>

    <div class="row">
        <div class="span3 <?php if (isset($val) and method_exists($val, 'error') and $val->error('username')) echo ' control-group error'; ?>">
            <label for="username">Username:</label>
            <?php if (isset($val) and method_exists($val, 'error') and $val->error('username')): ?>
                <p><div class="label label-important"><?php echo $val->error('username')->get_message(':label is required'); ?></div></p>
            <?php endif; ?>
            <div class="input"><?php echo Form::input('username', isset($user->username) ? $user->username : ''); ?></div>
        </div>
    </div>

    <div class="row">
		<div class="span3 <?php if (isset($val) and method_exists($val, 'error') and $val->error('email')) echo ' control-group error'; ?>">
            <label for="email">Email:</label>
            <?php if (isset($val) and method_exists($val, 'error') and $val->error('email')): ?>
                <p><div class="label label-important"><?php echo $val->error('email')->get_message(':label is required'); ?></div></p>
            <?php endif; ?>
            <div class="input"><?php echo Form::input('email', isset($user->email) ? $user->email : ''); ?></div>
        </div>
	</div>

	<div class="row">
        <div class="span3 <?php if (isset($val) and method_exists($val, 'error') and $val->error('password')) echo ' control-group error'; ?>">
            <label for="password">Password:</label>
            <?php if (isset($val) and method_exists($val, 'error') and $val->error('password')): ?>
                <p><div class="label label-important"><?php echo $val->error('password')->get_message(':label is required'); ?></div></p>
            <?php endif; ?>
            <div class="input"><?php echo Form::password('password'); ?></div>
        </div>
	</div>

    <div class="row">
        <div class="span3 <?php if (isset($val) and method_exists($val, 'error') and $val->error('password2')) echo ' control-group error'; ?>">
            <label for="password2">Password Verification:</label>
            <?php if (isset($val) and method_exists($val, 'error') and $val->error('password2')): ?>
                <p><div class="label label-important"><?php echo $val->error('password2')->get_message(':label did not match'); ?></div></p>
            <?php endif; ?>
            <div class="input"><?php echo Form::password('password2'); ?></div>
        </div>
    </div>

    <div class="form-actions">
		<?php echo Form::submit(array('value'=>'Register', 'name'=>'submit', 'class'=>'btn btn-large btn-success')); ?>
        &nbsp;&nbsp;or&nbsp;&nbsp;
        <?php echo Html::anchor('auth/session/facebook','Sign-up with Facebook', array('class'=>'btn btn-large btn-primary')); ?>
	</div>

<?php echo Form::close(); ?>
<div class="row">
    <div class="span12">
        <?php echo Html::anchor('admin/login','Already a member?'); ?>
    </div>
</div>