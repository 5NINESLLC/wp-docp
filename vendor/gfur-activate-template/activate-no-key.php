<?php
$link = add_query_arg(['activate-no-key' => true], home_url());
?>
<script>window.location.replace("<?php echo $link; ?>");</script>
<div style="padding: 30px;">
<table>
    <tr>
        <td><div id="loading-icon"></div></td>
        <td style="width: 100%;"><a href="<?php echo $link; ?>">...redirecting...</a></td>
    </tr>
</table>