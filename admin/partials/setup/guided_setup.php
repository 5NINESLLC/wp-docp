<div id="gs-container">
    <div id="gs-instructions">
        <h1>DOCC Setup</h1>
        <?php if ($subdirectory) : ?>
            <div id="query-message" class="alert alert-dismissible collapse alert-danger" role="alert" style="display: block;">
                <button id="query-message-dismiss" type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
                <strong id="query-message-title">Subdirectory Installation Detected: </strong>
                <span id="query-message-body">
                    It appears that your Wordpress installation is not running in the root folder of your web server.
                    This can cause trouble for the DOCC app because the imported layouts use relative links.
                    Please consider correcting this issue before continuing.
                </span>
                <br/><br/>
                <i>Debug info:</i>
                <br/>
                <code><?php
                echo "siteurl: ",get_option( 'siteurl' ); 
                echo "<br/>","&nbsp;home: &nbsp;&nbsp;&nbsp;",get_option( 'home' );
                ?></code>
            </div>
        <?php endif; ?>
        <p>
            Click the Start button below to install the dependencies for the DOCC app.
        </p>
    </div>
    <div id="gs-header-container">
        <a id="gs-header-start" href="#">Start</a>
        <a id="gs-header-continue" href="<?php echo add_query_arg(['page' => 'auto-setup'], admin_url('admin.php')); ?>">Continue</a>
    </div>
    <br />
    <i id="gs-do-not-leave-page" style="display: none;">Do not leave this page while installation is in progress.</i>
    <i id="gs-header-instructions">Installation complete! Click 'Continue' to set up the app...</i>
    <div id="gs-setup-progress"></div>

    <table class="gs-themes">
        <caption>Theme</caption>
        <thead>
            <tr>
                <th class="always-show" scope="col">Theme</th>
                <th scope="col">Version</th>
                <th scope="col">Latest Version</th>
                <th scope="col">Required</th>
                <th scope="col">Installed</th>
                <th class="always-show" scope="col">Active</th>
                <th scope="col"></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="always-show" data-label="Theme"><?php echo $themes[0]['theme']; ?></td>
                <td data-label="Version"><?php echo $themes[0]['version']; ?></td>
                <td data-label="Latest Version"><?php echo $themes[0]['latest_version']; ?></td>
                <td data-label="Required"><?php echo ($themes[0]['required'] === true ? '&check;' : '&cross;') ?></td>
                <td data-label="Installed"><?php echo ($themes[0]['installed'] === true ? '&check;' : '&cross;') ?></td>
                <td class="always-show" data-label="Active"><?php echo ($themes[0]['active'] === true ? '&check;' : '&cross;') ?></td>
                <?php if ($themes[0]['installed'] !== true) { ?>
                    <td data-label="Install Button"><a id="gs-theme-install" href="#">Install</a></td>
                <?php } else if ($themes[0]['active'] !== true) { ?>
                    <td data-label="Activate Button"><a id="gs-theme-activate" href="#">Activate</a></td>
                <?php } else { ?>
                    <td data-label="No Action">Active</td>
                <?php } ?>
            </tr>
        </tbody>
    </table>
    <table class="gs-plugins">
        <caption>Plugins</caption>
        <thead>
            <tr>
                <th class="always-show" scope="col">Plugin</th>
                <th scope="col">Version</th>
                <th scope="col">Latest Version</th>
                <th scope="col">Required</th>
                <th scope="col">Installed</th>
                <th class="always-show" scope="col">Active</th>
                <th scope="col"></th>
            </tr>
        </thead>
        <tbody> <?php

                foreach ($plugins as $plugin_ob) { ?>
                <tr>
                    <td class="always-show" data-label="Plugin"><?php echo $plugin_ob['plugin'] ?></td>
                    <td data-label="Version"><?php echo $plugin_ob['version'] ?></td>
                    <td data-label="Latest Version"><?php echo $plugin_ob['latest_version'] ?></td>
                    <td data-label="Required"><?php echo ($plugin_ob['required'] === true ? '&check;' : '&cross;') ?></td>
                    <td data-label="Installed"><?php echo ($plugin_ob['installed'] === true ? '&check;' : '&cross;') ?></td>
                    <td class="always-show" data-label="Active"><?php echo ($plugin_ob['active'] === true ? '&check;' : '&cross;') ?></td>
                    <?php if ($plugin_ob['installed'] !== true) { ?>
                        <td data-label="Install Button"><a class="gs-plugin-install" id="<?php echo $plugin_ob['slug'] ?>" href="#">Install</a></td>
                    <?php } else if ($plugin_ob['active'] !== true) { ?>
                        <td data-label="Activate Button"><a class="gs-plugin-activate" id="<?php echo $plugin_ob['slug'] ?>" href="#">Activate</a></td>
                    <?php } else { ?>
                        <td data-label="No Action">Active</td>
                    <?php } ?>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    <a id="gs-toggle-advanced-info" href="javascript:void(0)"><i>toggle advanced info</i></a>
    <br /><br />
    <div id="gs-footer-container">
        <div>Click <a href="<?php echo admin_url(); ?>">here</a> to skip the guided setup.</div>
    </div>
</div>