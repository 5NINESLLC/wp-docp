<?php

global $gw_activate_template;

$result = $gw_activate_template->result;

$signup = $result->get_error_data();

$link = add_query_arg($signup->user_email === 0 ? 
    ['activate-no-key' => 'true'] 
    : ['activate-error' => $signup->user_email], home_url());

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