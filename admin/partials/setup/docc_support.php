<div id="asc-container">
    <div id="asc-instructions">
        <h1>Support</h1>
        <p>
            Installation and setup is complete! See below <u>if you encountered any errors</u> or problems during this process; otherwise, click 'Continue' to finish.
        </p>
    </div>
    <div id='asc-buttons-container'>
        <a id='asc-continue' href="<?php echo add_query_arg(['page' => 'test-email'], admin_url('admin.php')); ?>">Continue</a>
    </div>
    <br/>
    <br/>
    <br/>
    <a id="gs-toggle-support-info" href="javascript:void(0)"><i>Did something go wrong during the installation?</i></a>
    <br /><br />
    <div class="asc-support-info">
        <h2>Option 1: Send an email for Tech Support.</h2>
        <p>If you have encountered any errors or problems during this process then add your email and a description of the issue below and click 'Get support'
            to let our team know you are having trouble.</p>
        <div id='asc-error-log'>
            <h4>Errors:</h4>
            <?php if (empty($error_log)) { ?>
                <p>We did not detect any errors during setup.</p>
                <?php } else {
                foreach ($error_log as $error => $message) { ?>
                    <p><?php echo $error ?> : <?php echo $message ?></p>
            <?php }
            } ?>
        </div>
        <h4>Email</h4>
        <input id='asc-email' type='email' name='email' placeholder="your@email.address"></input>
        <p id='te-error-message'>Please enter a valid email address and try again.</p>
        <h4>Additional Information</h4>
        <textarea id='asc-msg' name='msg' rows="4" cols="50"></textarea>
        <br/><br/>
        <a id='asc-support-email' href="#">Get support</a>
        
    </div>
    <br/><br/>
    <div class="asc-support-info">
        <h2>Option 2: Try rerunning the setup.</h2>
        <p>If you have encountered any errors or problems during this process then use the button below to delete all data and restart the setup.</p>
        <br>
        <a id='asc-rerun' href="#">Rerun Setup</a>
        <br/><br/>
        <i>CAUTION: This will delete all data. Do not press this button if you have active users.</i>
    </div>
</div>