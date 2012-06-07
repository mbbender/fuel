<?php echo Form::open(array()); ?>

	<?php if (isset($_GET['destination'])): ?>
		<?php echo Form::hidden('destination',$_GET['destination']); ?>
	<?php endif; ?>

	<?php if (isset($login_error)): ?>
		<div class="error"><?php echo $login_error; ?></div>
	<?php endif; ?>

    <div class="row">
        <div class="span12">
            <label for="username">Username:</label>
            <div class="input"><?php echo Form::input('username', $user->username); ?></div>

            <?php if ($val->error('username')): ?>
            <div class="error"><?php echo $val->error('username')->get_message('You must provide a username'); ?></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
		<div class="span12">
            <label for="email">Email:</label>
            <div class="input"><?php echo Form::input('email', $user->email); ?></div>

            <?php if ($val->error('email')): ?>
                <div class="error"><?php echo $val->error('email')->get_message('You must provide an email'); ?></div>
            <?php endif; ?>
        </div>
	</div>

	<div class="row">
        <div class="span12">
            <label for="password">Password:</label>
            <div class="input"><?php echo Form::password('password'); ?></div>

            <?php if ($val->error('password')): ?>
                <div class="error"><?php echo $val->error('password')->get_message(':label cannot be blank'); ?></div>
            <?php endif; ?>
        </div>
	</div>

    <div class="row">
        <div class="span12">
            <label for="password2">Password Verification:</label>
            <div class="input"><?php echo Form::password('password2'); ?></div>

            <?php if ($val->error('password2')): ?>
            <div class="error"><?php echo $val->error('password2')->get_message(':label cannot be blank'); ?></div>
            <?php endif; ?>
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