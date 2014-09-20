<div id="login-area" class="container">

<form action="login.php" method="post" class="form-signin" role="form">
<h2 class="form-signin-heading">Login</h2>

<input type="text" id="username" class="form-control" placeholder="Username" name="username" required autofocus>
<input type="password" id="password" class="form-control" placeholder="Password" name="password" required>
<a href="#" id="forget-password-link" class="pull-right">Forgot Password?</a>
<label class="checkbox">
  <input type="checkbox" value="1" name="remember" checked> Remember me
</label>
<button class="btn btn-lg btn-primary btn-block" type="submit" name="action" value="Login">Sign in</button><br />
</form>

<form action="login.php" method="post" id="forget-password-form" role="form">
<h2 class="form-signin-heading">Forgot Password</h2>

<input type="text" id="search" class="form-control" placeholder="Username/Email" name="search" required>
<button class="btn btn-lg btn-primary btn-block" type="submit" name="action" value="Send Password">Send Password</button>
</form>

</div>
