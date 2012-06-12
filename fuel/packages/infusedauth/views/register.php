<?php echo $fieldset; ?>

<button type="submit" value="Register" name="btnSubmit" title="Login using email address and password" class="btn btn-primary">Register</button>
| <a href="/auth/login">Login</a>
| <a href="/auth/reset">Forgot your password?</a>


</form>

<h2>Or login with</h2>
<?php echo Html::anchor('auth/session/github','Github',array('class'=>'btn btn-large')) ?>&nbsp;
<?php echo Html::anchor('auth/session/facebook','Facebook',array('class'=>'btn btn-large')) ?>&nbsp;
<?php echo Html::anchor('auth/session/twitter','Twitter',array('class'=>'btn btn-large')) ?>&nbsp;
<?php echo Html::anchor('auth/session/google','Google+',array('class'=>'btn btn-large')) ?>