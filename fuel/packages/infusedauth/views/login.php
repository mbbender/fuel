

    <?php echo $fieldset->build(); ?>


        <button type="submit" value="Login" name="btnSubmit" title="Login using email address and password" class="btn btn-primary">Login</button>
        | <a href="/auth/register">Register</a>
        | <a href="/auth/reset">Forgot your password?</a>


</form>

<h2>Or login with</h2>
<?php echo Html::anchor('auth/session/github','Github',array('class'=>'btn btn-large')) ?>&nbsp;
<?php echo Html::anchor('auth/session/facebook','Facebook',array('class'=>'btn btn-large')) ?>&nbsp;
<?php echo Html::anchor('auth/session/twitter','Twitter',array('class'=>'btn btn-large')) ?>&nbsp;
<?php echo Html::anchor('auth/session/google','Google+',array('class'=>'btn btn-large')) ?>
