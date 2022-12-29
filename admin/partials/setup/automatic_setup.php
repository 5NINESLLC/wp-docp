<div id="as-container">
    <div id="as-instructions">
        <h1>Import Settings</h1>
        <p>
            Click the Start button to import all settings.
        </p>
    </div>
    <div id="as-header-container">
        <a id="as-header-start" href="#">Start</a>
        <a id="as-header-continue" href="<?php echo add_query_arg(['page' => 'docc-support'], admin_url('admin.php')); ?>">Continue</a>
    </div>
    <br>
    <div id="as-setup-progress"></div>
    <table class="as-config-table">
        <thead>
            <th>Setup</th>
            <th>Status</th>
        </thead>
        <tbody>
            <tr>
                <td>User Roles</td>
                <td>
                    <div id="as-roles-status">Done</div>
                </td>
            </tr>
            <tr>
                <td>Pages</td>
                <td>
                    <div id="as-pages-status">Done</div>
                </td>
            </tr>
            <tr>
                <td>Divi Theme Options</td>
                <td>
                    <div id="as-options-status">Done</div>
                </td>
            </tr>
            <tr>
                <td>Divi Theme Builder Templates</td>
                <td>
                    <div id="as-templates-status">Done</div>
                </td>
            </tr>
            <tr>
                <td>Divi Theme Customizations</td>
                <td>
                    <div id="as-customizations-status">Done</div>
                </td>
            </tr>
            <tr>
                <td>Forms</td>
                <td>
                    <div id="as-forms-status">Done</div>
                </td>
            </tr>
            <tr>
                <td>Custom form feeds</td>
                <td>
                    <div id="as-feeds-status">Done</div>
                </td>
            </tr>
            <tr>
                <td>Misc. Settings</td>
                <td>
                    <div id="as-settings-status">Done</div>
                </td>
            </tr>
        </tbody>
    </table>
</div>