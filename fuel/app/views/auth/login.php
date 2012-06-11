<?php echo Form::open(array()); ?>

	<?php if (isset($_GET['destination'])): ?>
		<?php echo Form::hidden('destination',$_GET['destination']); ?>
	<?php endif; ?>

	<?php if (isset($login_error)): ?>
		<div class="error"><?php echo $login_error; ?></div>
	<?php endif; ?>

	<div class="row">
		<div class="span12">
            <label for="email">Email or Username:</label>
            <div class="input"><?php echo Form::input('email', Input::post('email')); ?></div>

            <?php if ($val->error('email')): ?>
                <div class="error"><?php echo $val->error('email')->get_message('You must provide a username or email'); ?></div>
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
            <label class="checkbox">
                <input type="checkbox"> Remember Me
            </label>
        </div>
    </div>




    <div class="form-actions">
		<?php echo Form::submit(array('value'=>'Login', 'name'=>'submit', 'class'=>'btn btn-large btn-success')); ?>
        &nbsp;&nbsp;or&nbsp;&nbsp;
        <?php echo Html::anchor('auth/session/facebook','Login with Facebook', array('class'=>'btn btn-large btn-primary')); ?>
	</div>

<?php echo Form::close(); ?>
<div class="row">
    <div class="span12">
        <?php echo Html::anchor('auth/forgot_password','Forgot your password?'); ?> |
        <?php echo Html::anchor('auth/register','Register'); ?>
    </div>
</div>