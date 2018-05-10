<?php

# Plugging of the Events Manager plugin
# using its own hooks, mainly to add captcha

define('MNETQNLOCAL_OPT_EM_CAPTCHA', 'mnetqnlocal_em_captcha');

function mnetqnlocal_em_admin() {
    if (!function_exists('em_options_radio_binary')) return;
    global $save_button;
    $postbox_title = 'MonrifNet QN Local';
    $postbox_desc = 'Opzioni del plugin Events Manager aggiunte tramite plugin QN Local di Monrif Net';
    $captcha_title = 'Abilitare captcha?';
    $captcha_desc = 'Sul modulo pubblico di segnalazione eventi, all\'utente anonimo verrà chiesta una conferma via captcha.';
    ?>
	<div  class="postbox" id="em-opt-mnetqnlocal" >
	<div class="handlediv" title="<?php __('Click to toggle', 'events-manager'); ?>"><br /></div>
    <h3><span><?php echo $postbox_title; ?></span></h3>
	<div class="inside">
        <table class="form-table">
            <tr><td colspan="2" class="em-boxheader">
            	<?php echo $postbox_desc; ?>
			</td></tr>
			<?php
				em_options_radio_binary( $captcha_title, MNETQNLOCAL_OPT_EM_CAPTCHA, $captcha_desc );
                echo $save_button;
            ?>
		</table>
	</div> <!-- . inside -->
	</div> <!-- .postbox -->
    <?php
}
add_action( 'em_options_page_footer', 'mnetqnlocal_em_admin' );

function mnetqnlocal_em_admin_save() {
    if (isset($_POST[MNETQNLOCAL_OPT_EM_CAPTCHA])) {
        $captcha = get_site_option(MNETQNLOCAL_OPT_EM_CAPTCHA, FALSE);
        $captcha_p = @(int)$_POST[MNETQNLOCAL_OPT_EM_CAPTCHA] === 1;
        if ($captcha !== $captcha_p) {
            if ($captcha_p == $captcha_p) {
                delete_site_option(MNETQNLOCAL_OPT_EM_CAPTCHA);
            }
            update_site_option(MNETQNLOCAL_OPT_EM_CAPTCHA, $captcha_p);
        }
    }
}
add_action( 'em_options_save', 'mnetqnlocal_em_admin_save' );

function mnetqnlocal_em_init() {
    if (get_site_option(MNETQNLOCAL_OPT_EM_CAPTCHA, FALSE)) {
        add_action( 'em_front_event_form_guest', 'mnetqnlocal_em_form_captcha' );
        add_action( 'em_front_event_form_footer', 'mnetqnlocal_em_form_footer' );
        add_filter( 'em_event_validate', 'mnetqnlocal_em_captcha_validate', 10, 2 );
    }
}
add_action( 'init', 'mnetqnlocal_em_init' );

function mnetqnlocal_em_form_captcha() {
    echo '<div class="g-recaptcha" data-sitekey="'
         . MNETQNLOCAL_RECAPTCHA_KEY
         . '" data-callback="onReCaptchaSubmit"></div>';
}

function mnetqnlocal_em_form_footer() {
    echo <<< HereMnet
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script type="text/javascript">
        function onReCaptchaSubmit(token) {
            console.log("Captcha submitted", token);
        }
    </script>
HereMnet;
}

function mnetqnlocal_em_captcha_validate($validating, $em_obj) {
    $response = @$_POST[MNETQNLOCAL_RECAPTCHA_FIELD];
    $captcha_passed = mnetqnlocal_recaptcha_verify($response);
    if (!$captcha_passed) {
        $em_obj->add_error( sprintf(__("%s is required.", 'events-manager'), __('Captcha')) );
    }
    return $validating && $captcha_passed;
}
