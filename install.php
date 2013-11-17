<?php
require_once dirname(__FILE__) . '/app/app.php';

// This is an helper function returning an html table row to avoid code duplication
function installStatus($str, $msg, $result) {
    $class = ($result) ? 'ok' : 'fail';
    return '<tr><td>' . $str . '</td><td class="' . $class . '">' . $msg . '</td></tr>';
}

function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    return $protocol . $_SERVER['HTTP_HOST'] . str_replace('/install.php', '', $_SERVER['REQUEST_URI']);
}

// If the password and config files exist, moonmoon is already installed
if (file_exists(dirname(__FILE__) . '/custom/config.yml')
    && file_exists(dirname(__FILE__) . '/admin/inc/pwd.inc.php')) {
    $status = 'installed';
} elseif (isset($_REQUEST['action'])) {
    $save = array();
    //Save config file
    $config = array(
        'url'           => getBaseUrl(),
        'name'          => filter_var($_REQUEST['title'], FILTER_SANITIZE_SPECIAL_CHARS),
        'locale'        => filter_var($_REQUEST['locale'], FILTER_SANITIZE_SPECIAL_CHARS),
        'items'         => 10,
        'refresh'       => 240,
        'cache'         => 10,
        'cachedir'      => './cache',
        'categories'    => '',
        'storage'       => 'sqlite'
    );

    $CreatePlanetConfig = new PlanetConfig($config);
    $save['config'] = file_put_contents(dirname(__FILE__).'/custom/config.yml', $CreatePlanetConfig->toYaml());

    //Save password
    $save['password'] = file_put_contents(dirname(__FILE__).'/admin/inc/pwd.inc.php', '<?php $login="admin"; $password="'.md5($_REQUEST['password']).'"; ?>');

    if (0 != ($save['config'] + $save['password'])) {
        $status = 'installed';
    }
} else {

    // We start by malking sure we have PHP5 as a base requirement
    if(phpversion() >= 5) {
        $strInstall = installStatus('Server is running PHP5', 'OK',true);
        $strRecommendation = '';
    } else {
        $strInstall = installStatus('Server is running PHP5', 'FAIL',false);
        $strRecommendation = '<li>Check your server documentation to activate PHP5</li>';
    }

    // Writable file requirements
    $tests = array(
        '/custom',
        '/custom/people.opml',
        '/admin/inc/pwd.inc.php',
        '/cache',
    );

    // We now test that all required files are writable
    foreach ($tests as $v) {
        if(is_writable(dirname(__FILE__) . $v)) {
            $strInstall .= installStatus("<code>$v</code> is writable", 'OK', true);
        } else {
            $strInstall .= installStatus("<code>$v</code> is writable", 'FAIL',false);
            $strRecommendation .= "<li>Make <code>$v</code> writable with CHMOD</li>";
        }
    }

    // We can now decide if we install moonmoon or not
    $status = ($strRecommendation != '') ? 'error' : 'install';

}
?>
<!DOCTYPE html>
<html lang="en">
<meta charset="utf-8">
<head>
    <title><?=_g('moonmoon installation')?></title>
    <style>
    body {
        font: normal 1em sans-serif;
        width: 500px;
        margin: 0 auto;
    }

    /* Error */
    td.ok {
        color: #090;
    }

    td.fail {
        color: #900;
        font-weight: bold;
    }
    th {
        text-align: left;
    }

    /* Install */
    .field label {
        display: block;
    }

    .submit {
        font-size: 2em;
    }

    </style>
</head>

<body>
    <h1><?=_g('moonmoon installation')?></h1>

    <?php if ($status == 'error') : ?>
    <div id="compatibility">
        <h2>Sorry, your server is not compatible with moonmoon.</h2>

        <h3>Your server does not fulfill the requirements</h3>
        <table>
            <thead>
                <tr>
                    <th>Test</th>
                    <th>Result</th>
                </tr>
            </thead>
            <tbody>
                <?php echo $strInstall ?>
            </tbody>
        </table>

        <h3>Troubleshooting</h3>
        <p>To install moonmoon, try the following changes:</p>
        <ul>
            <?php echo $strRecommendation; ?>
        </ul>
    </div>

    <?php elseif ($status == 'install') : ?>
    <div>
        <form method="post" action="">
            <fieldset>
                <p class="field">
                    <label for="title">Title:</label>
                    <input type="text" id="title" name="title" value="My website"/>
                </p>
                <!--
                <p class="field">
                    <label>Administrator login:</label> <code>admin</code>
                </p>
                -->
                <p class="field">
                    <label for="password">Administrator password:</label>
                    <input type="text" id="password" name="password" class="text password" value="admin" />
                </p>
                <p class="field">
                    <label for="locale">Language:</label>
                    <select name="locale" id="locale">
                        <option selected="selected" value="en">English</option>
                        <option value="fr">Français</option>
                    </select>
                </p>
                <p>
                    <input type="submit" name="action" class="submit" value="Install"/>
                </p>
            </fieldset>
        </form>
    </div>

    <?php elseif ($status =='installed'): ?>

    <p><?=_g('Congratulations! Your moonmoon is ready.')?></p>
    <h3><?=_g("What's next?")?></h3>
    <ol>
        <li>
            <?=_g('<strong>Delete</strong> <code>install.php</code> with your FTP software.')?>
        </li>
        <li>
            <?=_g('Use your password to go to the <a href="./admin/">administration panel</a>')?>
        </li>
    </ol>
    <?php endif; ?>
</body>
</html>
