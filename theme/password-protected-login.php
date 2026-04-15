<?php

/**
 * Based roughly on wp-login.php @revision 19414
 * http://core.trac.wordpress.org/browser/trunk/wp-login.php?rev=19414
 */

global $wp_version, $Password_Protected, $error, $is_iphone;

/**
 * WP Shake JS
 */
if ( ! function_exists( 'wp_shake_js' ) ) {
	function wp_shake_js() {
		global $is_iphone;
		if ( $is_iphone ) {
			return;
		}
		?>
		<script>
		addLoadEvent = function(func){if(typeof jQuery!="undefined")jQuery(document).ready(func);else if(typeof wpOnload!='function'){wpOnload=func;}else{var oldonload=wpOnload;wpOnload=function(){oldonload();func();}}};
		function s(id,pos){g(id).left=pos+'px';}
		function g(id){return document.getElementById(id).style;}
		function shake(id,a,d){c=a.shift();s(id,c);if(a.length>0){setTimeout(function(){shake(id,a,d);},d);}else{try{g(id).position='static';wp_attempt_focus();}catch(e){}}}
		addLoadEvent(function(){ var p=new Array(15,30,15,0,-15,-30,-15,0);p=p.concat(p.concat(p));var i=document.forms[0].id;g(i).position='relative';shake(i,p,20);});
		</script>
		<?php
	}
}

/**
 * @since 3.7.0
 */
if ( ! function_exists( 'wp_login_viewport_meta' ) ) {
	function wp_login_viewport_meta() {
		?>
		<meta name="viewport" content="width=device-width" />
		<?php
	}
}

nocache_headers();
header( 'Content-Type: ' . get_bloginfo( 'html_type' ) . '; charset=' . get_bloginfo( 'charset' ) );

// Set a cookie now to see if they are supported by the browser.
setcookie( TEST_COOKIE, 'WP Cookie check', 0, COOKIEPATH, COOKIE_DOMAIN );
if ( SITECOOKIEPATH != COOKIEPATH ) {
	setcookie( TEST_COOKIE, 'WP Cookie check', 0, SITECOOKIEPATH, COOKIE_DOMAIN );
}

// If cookies are disabled we can't log in even with a valid password.
if ( isset( $_POST['password_protected_cookie_test'] ) && empty( $_COOKIE[ TEST_COOKIE ] ) ) {
	$Password_Protected->errors->add( 'test_cookie', __( "<strong>ERROR</strong>: Cookies are blocked or not supported by your browser. You must <a href='http://www.google.com/cookies.html'>enable cookies</a> to use WordPress.", 'password-protected' ) );
}

// Shake it!
$shake_error_codes = array( 'empty_password', 'incorrect_password' );
if ( $Password_Protected->errors->get_error_code() && in_array( $Password_Protected->errors->get_error_code(), $shake_error_codes ) ) {
	add_action( 'password_protected_login_head', 'wp_shake_js', 12 );
}

// Obey privacy setting
if ( function_exists( 'wp_robots' ) && function_exists( 'wp_robots_no_robots' ) && function_exists( 'add_filter' ) ) {
	add_filter( 'wp_robots', 'wp_robots_no_robots' );
	add_action( 'password_protected_login_head', 'wp_robots', 1 );
} elseif ( function_exists( 'wp_no_robots' ) ) {
	add_action( 'password_protected_login_head', 'wp_no_robots' );
}

add_action( 'password_protected_login_head', 'wp_login_viewport_meta' );

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head>

<meta http-equiv="Content-Type" content="<?php bloginfo( 'html_type' ); ?>; charset=<?php bloginfo( 'charset' ); ?>" />
<title><?php echo apply_filters( 'password_protected_wp_title', get_bloginfo( 'name' ) ); ?></title>

<?php

if ( version_compare( $wp_version, '3.9-dev', '>=' ) ) {
	wp_admin_css( 'login', true );
} else {
	wp_admin_css( 'wp-admin', true );
	wp_admin_css( 'colors-fresh', true );
}

?>

<style media="screen">
html {
	height: 100%;
	overflow: hidden;
}
body.login.login-password-protected {
	position: relative;
	display: grid;
	place-items: center;
	min-height: 100vh;
	min-height: 100svh;
	margin: 0;
	padding: 20px;
	overflow: hidden;
	background: #f8fbfa;
}
#login_error, .login .message, #loginform { margin-bottom: 20px; }
.password-protected-text-below { display: inline-block; text-align: center; margin-top: 30px;}
.password-protected-text-above { text-align: center; margin-bottom: 10px;}
body.login.login-password-protected #login {
	position: relative;
	z-index: 2;
	width: 100%;
	max-width: 420px;
	margin: 0 auto;
	padding: 32px;
	border: 1px solid rgba(10, 61, 44, 0.08);
	border-radius: 24px;
	background: rgba(255, 255, 255, 0.9);
	box-shadow: 0 24px 60px rgba(10, 61, 44, 0.12);
	backdrop-filter: blur(12px);
}
body.login.login-password-protected #password-protected-logo {
	margin: 0 0 28px;
}
body.login.login-password-protected #password-protected-logo a {
	background-image: none;
	width: 100%;
	height: auto;
	min-height: 0;
	margin: 0 auto;
	padding: 0;
	text-indent: 0;
}
body.login.login-password-protected #password-protected-logo a img {
	display: block;
	width: 100%;
	max-width: 320px;
	height: auto;
	margin: 0 auto;
}
body.login.login-password-protected .password-protected-hero-background {
	position: fixed;
	inset: 0;
	z-index: 0;
	overflow: hidden;
	pointer-events: none;
	background: linear-gradient(135deg, rgba(17, 87, 64, 0.02) 0%, rgba(26, 128, 95, 0.06) 100%);
}
body.login.login-password-protected .password-protected-hero-shape {
	position: absolute;
	clip-path: polygon(50% 0%, 0% 100%, 100% 100%);
	transform-origin: center;
}
body.login.login-password-protected .password-protected-hero-shape.shape-1 {
	top: -200px;
	left: -100px;
	width: 400px;
	height: 400px;
	background: linear-gradient(135deg, rgba(17, 87, 64, 0.04), rgba(26, 128, 95, 0.06));
	transform: rotate(15deg);
	animation: passwordProtectedFloatTriangle1 20s ease-in-out infinite;
}
body.login.login-password-protected .password-protected-hero-shape.shape-2 {
	right: -50px;
	bottom: -150px;
	width: 300px;
	height: 300px;
	background: linear-gradient(135deg, rgba(26, 128, 95, 0.03), rgba(17, 87, 64, 0.05));
	transform: rotate(195deg);
	animation: passwordProtectedFloatTriangle2 25s ease-in-out infinite reverse;
}
body.login.login-password-protected .password-protected-hero-shape.shape-3 {
	top: 40%;
	right: 15%;
	width: 200px;
	height: 200px;
	background: linear-gradient(135deg, rgba(17, 87, 64, 0.02), rgba(26, 128, 95, 0.04));
	transform: rotate(45deg);
	animation: passwordProtectedFloatTriangle3 30s ease-in-out infinite;
}
body.login.login-password-protected .password-protected-hero-glow {
	position: absolute;
	inset: auto auto -120px -80px;
	width: 420px;
	height: 420px;
	border-radius: 50%;
	background: radial-gradient(circle, rgba(26, 128, 95, 0.12) 0%, rgba(26, 128, 95, 0) 72%);
	filter: blur(8px);
}
body.login.login-password-protected #login_error,
body.login.login-password-protected .message {
	border-radius: 16px;
}
body.login.login-password-protected #loginform {
	margin-bottom: 0;
}
body.login.login-password-protected .button.button-primary {
	border-color: #004e38;
	background: #004e38;
	box-shadow: none;
}
body.login.login-password-protected .button.button-primary:hover,
body.login.login-password-protected .button.button-primary:focus {
	border-color: #0a3d2c;
	background: #0a3d2c;
}
body.login.login-password-protected .wp-pwd .button.wp-hide-pw,
body.login.login-password-protected .wp-pwd .button.wp-hide-pw:hover,
body.login.login-password-protected .wp-pwd .button.wp-hide-pw:focus {
	border-color: #004e38;
	background: #004e38;
	color: #ffffff;
	box-shadow: none;
}
body.login.login-password-protected .wp-pwd .button.wp-hide-pw:hover,
body.login.login-password-protected .wp-pwd .button.wp-hide-pw:focus {
	border-color: #0a3d2c;
	background: #0a3d2c;
}
body.login.login-password-protected #password_protected_rememberme {
	accent-color: #004e38;
}
@keyframes passwordProtectedFloatTriangle1 {
	0%, 100% { transform: rotate(15deg) translate3d(0, 0, 0); }
	50% { transform: rotate(21deg) translate3d(18px, 18px, 0); }
}
@keyframes passwordProtectedFloatTriangle2 {
	0%, 100% { transform: rotate(195deg) translate3d(0, 0, 0); }
	50% { transform: rotate(188deg) translate3d(-22px, -18px, 0); }
}
@keyframes passwordProtectedFloatTriangle3 {
	0%, 100% { transform: rotate(45deg) translate3d(0, 0, 0); }
	50% { transform: rotate(52deg) translate3d(0, -22px, 0); }
}
@media (max-width: 600px) {
	body.login.login-password-protected {
		padding: 16px;
	}
	body.login.login-password-protected #login {
		padding: 24px 20px;
		border-radius: 20px;
	}
	body.login.login-password-protected .password-protected-hero-shape.shape-1 {
		width: 280px;
		height: 280px;
		top: -140px;
		left: -90px;
	}
	body.login.login-password-protected .password-protected-hero-shape.shape-2 {
		width: 220px;
		height: 220px;
		right: -70px;
		bottom: -120px;
	}
	body.login.login-password-protected .password-protected-hero-shape.shape-3 {
		top: 58%;
		right: -20px;
		width: 150px;
		height: 150px;
	}
}
@media (prefers-reduced-motion: reduce) {
	body.login.login-password-protected .password-protected-hero-shape {
		animation: none;
	}
}
</style>

<?php

if ( $is_iphone ) {
	?>
	<meta name="viewport" content="width=320; initial-scale=0.9; maximum-scale=1.0; user-scalable=0;" />
	<style media="screen">
	.login form, .login .message, #login_error { margin-left: 0px; }
	.login #nav, .login #backtoblog { margin-left: 8px; }
	.login h1 a { width: auto; }
	#login { padding: 20px 0; }
	</style>
	<?php
}

do_action( 'login_enqueue_scripts' );
if ( class_exists( 'Login_Designer' ) ) {
	do_action( 'password_protected_enqueue_scripts' );
}
do_action( 'password_protected_login_head' );

?>

</head>
<body class="login login-password-protected login-action-password-protected-login wp-core-ui">

<div class="password-protected-hero-background" aria-hidden="true">
	<div class="password-protected-hero-glow"></div>
	<div class="password-protected-hero-shape shape-1"></div>
	<div class="password-protected-hero-shape shape-2"></div>
	<div class="password-protected-hero-shape shape-3"></div>
</div>

<div id="login">
	<?php
	$logo_file = PASSWORD_PROTECTED_DIR . 'assets/images/logodark.png';
	$logo_url  = '';

	if ( file_exists( $logo_file ) ) {
		$logo_url = apply_filters( 'password_protected_login_logo_url', PASSWORD_PROTECTED_URL . 'assets/images/logodark.png' );
	}
	?>
	<h1 class="wp-login-logo" id="password-protected-logo"><a href="<?php echo esc_url( apply_filters( 'password_protected_login_headerurl', home_url( '/' ) ) ); ?>" title="<?php echo esc_attr( apply_filters( 'password_protected_login_headertitle', get_bloginfo( 'name' ) ) ); ?>"><?php if ( ! empty( $logo_url ) ) : ?><img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" /><?php else : ?><?php bloginfo( 'name' ); ?><?php endif; ?></a></h1>
	<?php do_action( 'password_protected_login_messages' ); ?>

	<?php do_action( 'password_protected_before_login_form' ); ?>

	<form name="loginform" id="loginform" action="<?php echo esc_url( $Password_Protected->login_url() ); ?>" method="post">

        <p>
            <?php do_action( 'password_protected_above_password_field' ); ?>
        </p>

        <!--
		We are removing this field PP-245
             <p>
                <label for="password_protected_pass"><?php echo esc_attr( apply_filters( 'password_protected_login_password_title', __( 'Password', 'password-protected' ) ) ); ?></label>
                <input type="password" name="password_protected_pwd" id="password_protected_pass" class="input" value="" size="20" tabindex="20" autocomplete="false" />
            </p>
        -->

        <div class="user-pass-wrap">
            <label for="password_protected_pass"><?php echo esc_attr( apply_filters( 'password_protected_login_password_title', __( 'Password', 'password-protected' ) ) ); ?></label>
            <div class="wp-pwd">
                <input id="password_protected_pass" class="input password-input" type="password" name="password_protected_pwd" value="" size="20" autocomplete="false" spellcheck="false" required>
                <button id="pp-hide-show-password" class="button button-secondary hide-if-no-js wp-hide-pw" type="button" data-toggle="0" aria-label="Show password">
                    <span class="dashicons dashicons-visibility" aria-hidden="true"></span>
                </button>
            </div>
        </div>

		<?php do_action('password_protected_after_password_field'); ?>
		<?php if ( $Password_Protected->allow_remember_me() ) : ?>
			<p class="forgetmenot">
				<label for="password_protected_rememberme"><input name="password_protected_rememberme" type="checkbox" id="password_protected_rememberme" value="1" tabindex="90" /> <?php esc_attr_e( 'Remember Me' ); ?></label>
			</p>
		<?php endif; ?>
		
		<p class="submit">
			<input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="<?php esc_attr_e( 'Log In' ); ?>" tabindex="100" />
			<input type="hidden" name="password_protected_cookie_test" value="1" />
			<input type="hidden" name="password-protected" value="login" />
			<input type="hidden" name="redirect_to" value="<?php echo esc_attr( ! empty( $_REQUEST['redirect_to'] ) ? esc_url( $_REQUEST['redirect_to'] ) : '' ); ?>" />
		</p>

        <div style="display: table;clear: both;"></div>

        <p>
		    <?php do_action( 'password_protected_below_password_field' ); ?>
        </p>

	</form>

	<?php do_action( 'password_protected_after_login_form' ); ?>

</div>

<script>
try{document.getElementById('password_protected_pass').focus();}catch(e){}
if(typeof wpOnload=='function')wpOnload();
try{let s=document.getElementById("pp-hide-show-password");s.addEventListener("click",function(e){e.preventDefault();let t=document.getElementById("password_protected_pass");"password"===t.type?(t.type="text",s.innerHTML='<span class="dashicons dashicons-hidden" aria-hidden="true"></span>'):(t.type="password",s.innerHTML='<span class="dashicons dashicons-visibility" aria-hidden="true"></span>')})}catch(e){}
</script>

<?php do_action( 'login_footer' ); ?>

<div class="clear"></div>

<?php if ( class_exists( 'Login_Designer' ) ) : ?>
	<div id="password-protected-background" style="position:absolute; inset: 0;width: 100%;height: 100%;z-index: -1;transition: opacity 300ms cubic-bezier(0.694, 0, 0.335, 1) 0s"></div>
<?php endif; ?>

</body>
</html>
