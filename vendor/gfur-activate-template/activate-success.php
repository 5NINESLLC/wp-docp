<?php

global $gw_activate_template;

/**
 * @var $user_id
 * @var $blog_id
 * @var $password
 */
extract( $gw_activate_template->result );

$url = is_multisite() ? get_blogaddress_by_id( (int) $blog_id ) : home_url('', 'http');
$user = new WP_User( (int) $user_id );

if ( $gw_activate_template->result['password_hash'] ) {
	$password = esc_html__( 'Set at registration.', 'gravityformsuserregistration' );
} elseif ( ! empty( $user->user_activation_key ) ) {
	$password = esc_html__( 'Check your email for the set password link.', 'gravityformsuserregistration' );
} else {
	$password = sprintf( '<a href="%s">%s</a>', esc_url( gf_user_registration()->get_set_password_url( $user ) ), esc_html__( 'Set your password.', 'gravityformsuserregistration' ) );
}

$link = add_query_arg(['activate-success' => $user->user_login], home_url());

if ( ! empty( $user->user_activation_key ) ) {
    $passwordLink = gf_user_registration()->get_set_password_url( $user );
    $link = $passwordLink;
}

?>
<script>window.location.replace("<?php echo $link; ?>");</script>
<div style="padding: 30px;">
<table>
    <tr>
        <td><div id="loading-icon"></div></td>
        <td style="width: 100%;"><a href="<?php echo $link; ?>">...redirecting...</a></td>
    </tr>
</table>

</div>