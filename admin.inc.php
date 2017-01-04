<?php

define('MNETQNLOCAL_GITTOKEN', 'feddc4a4ff0de9c211e58800c3d92c3be7baba00');
define('MNETQNLOCAL_GITAPI', 'https://api.github.com/repos/monrifnet/mnetqnlocal/git/refs/heads/master');
define('MNETQNLOCAL_GITPULLSSL', 'https://github.com/monrifnet/mnetqnlocal/archive/%s.zip');
define('MNETQNLOCAL_GITPULL', 'http://github.com/monrifnet/mnetqnlocal/archive/%s.zip');
define('MNETQNLOCAL_ZIP', rtrim(MNETQNLOCAL_DIR, '/') . '-%s.zip');
define('MNETQNLOCAL_GITVER', 'mnetqnlocal_current_git_commit');
define('MNETQNLOCAL_NEXTVER', 'mnetqnlocal_next_git_commit');
define('MNETQNLOCAL_GITCHECK', 'mnetqnlocal_latest_git_check');

define('MNETQNLOCAL_OPT_UPDATEFREQ', 'mnetqnlocal_update_frequency');
define('MNETQNLOCAL_OPT_AUTOUPDATE', 'mnetqnlocal_update_automatically');

add_action('admin_init', 'mnetqnlocal_admin_init');

function mnetqnlocal_admin_init() {
    if (!current_user_can('update_plugins')) return;
    add_action('all_admin_notices', 'mnetqnlocal_admin_notice');
}

add_action('admin_menu', 'mnetqnlocal_admin_menu');
function mnetqnlocal_admin_menu() {
    add_options_page(
            'Monrif Net Q.Net Local Plugin Settings',
            'QN Local',
            'update_plugins',
            'mnetqnlocal-settings-page',
            'mnetqnlocal_admin_page'
        );
}

function mnetqnlocal_admin_notice() {
    $m_class = 'notice is-dismissible notice-';
    $m_message = FALSE;
    try {
        switch (mnetqnlocal_admin_latest_commit()) {
            case 0:
                $m_class .= 'warning';
                $m_message = 'impossibile collegarsi a git per verificare la versione.';
                break;
            case 1:
                // no message: plugin is updated
                break;
            case 2:
                $m_class .= 'info';
                $m_message = 'una nuova versione è disponibile al download. Si prega di aggiornare il plugin.';
                break;
            case 3:
                $m_class .= 'error';
                $m_message = 'si è verificato un errore durante l\'aggiornamento automatico del plugin.';
                break;
            case 4:
                $m_class .= 'warning';
                $m_message = 'il plugin è stato aggiornato automaticamente.';
                break;
        }
    } catch(Exception $e) {
        $m_class .= 'error';
        $m_message = 'si è verificato un errore: ' . $e->getMessage();
    }
    if ($m_message) {
        printf('<div class="%s"><p>MonrifNet Q.Net Local: %s</p></div>', $m_class, $m_message);
    }
}

/*
 * mnetqnlocal_admin_latest_commit
 * checks for new commits on the git repo
 * and returns a status code or an Exception:
 * 0 - Error connecting to git api
 * 1 - Current version is the latest
 * 2 - New version available (no auto-update)
 * 3 - Couldn't auto-update from git
 * 4 - Audo-update from git successful!
 */
function mnetqnlocal_admin_latest_commit() {
    $latest_check = (int)get_site_option(MNETQNLOCAL_GITCHECK, 0);
    $update_freq = max(60, (int)get_site_option(MNETQNLOCAL_OPT_UPDATEFREQ, 0));
    // needs 1 hour at least to check again the GIT version
    $code = 0;
    $latest_commit = FALSE;
    if ($latest_check < time() - $update_freq * 60) {
        $git = curl_init(MNETQNLOCAL_GITAPI);
        curl_setopt($git, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($git, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($git, CURLOPT_MAXREDIRS, 2);
        curl_setopt($git, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($git, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($git, CURLOPT_HTTPHEADER, array(
                                'Authorization: token ' . MNETQNLOCAL_GITTOKEN,
                                'User-Agent: Monrifnet-Qnet-Local',
                            ));
        $commit = curl_exec($git);
        $http_code = curl_getinfo($git, CURLINFO_HTTP_CODE);
        curl_close($git);
        if ($commit && $http_code == 200) {
            $json = json_decode($commit, TRUE);
            $latest_commit = @$json["object"]["sha"];
            if ($latest_commit) {
                update_site_option(MNETQNLOCAL_GITCHECK, time());
                update_site_option(MNETQNLOCAL_NEXTVER, $latest_commit);
            }
        }
    } else {
        $latest_commit = get_site_option(MNETQNLOCAL_NEXTVER, '');
    }
    if ($latest_commit) {
        $current_commit = get_site_option(MNETQNLOCAL_GITVER, '');
        if ($current_commit != $latest_commit) {
            if (get_site_option(MNETQNLOCAL_OPT_AUTOUPDATE, FALSE)) {
                $update_from_git = mnetqnlocal_admin_update_from_git();
                if ($update_from_git) {
                    // updated from git
                    $code = 4;
                } else {
                    // could not update from git
                    $code = 3;
                }
            } else {
                $code = 2;
            }
        } else {
            $code = 1;
        }
    }
    return $code;
}

function mnetqnlocal_rrmdir($dir) {
    if (is_dir($dir)) {
        array_map('mnetqnlocal_rrmdir', glob($dir . "/*"));
        rmdir($dir);
    } elseif (is_file($dir)) {
        unlink($dir);
    }
}

/*
 * mnetqnlocal_admin_update_from_git
 * tries to update the plugin via git
 */
function mnetqnlocal_admin_update_from_git() {
    if (!class_exists('ZipArchive')) return FALSE;
    $sha = get_site_option(MNETQNLOCAL_NEXTVER, '');
    if (!$sha) return FALSE;
    $updated = FALSE;
    $copied = FALSE;
    $git_url = sprintf(MNETQNLOCAL_GITPULLSSL, $sha);
    $git_zip = @fopen($git_url, 'r');
    if (!$git_zip) return FALSE;
    $zip_path = sprintf(MNETQNLOCAL_ZIP, $sha);
    $archive_zip = @fopen($zip_path, 'w+');
    if ($archive_zip) {
        $copied = stream_copy_to_stream($git_zip, $archive_zip);
        fclose($archive_zip);
    }
    fclose($git_zip);
    if ($copied) {
        $zip = new ZipArchive();
        if ($zip->open($zip_path) === TRUE) {
            try {
                $plugdir = rtrim(MNETQNLOCAL_DIR, '/');
                $subdir = dirname($plugdir) . "/mnetqnlocal-{$sha}";
                $zip->extractTo(dirname($plugdir));
                if (is_dir($plugdir)) mnetqnlocal_rrmdir($plugdir);
                $updated = rename($subdir, $plugdir);
            } catch(Exception $e) {
                // it's ok babe
            }
            $zip->close();
        }
    }
    @unlink($zip_path);
    if ($updated) {
        update_site_option(MNETQNLOCAL_GITVER, $sha);
    }
    return $updated;
}

function mnetqnlocal_admin_page() {
    if (!current_user_can('update_plugins')) {
        echo "<p>Eh no.</p>";
        return;
    }
    $autoupdate = get_site_option(MNETQNLOCAL_OPT_AUTOUPDATE, FALSE);
    $updatefreq = max(60, (int)get_site_option(MNETQNLOCAL_OPT_UPDATEFREQ, 0));
    if (isset($_POST['mql'])) {
        $autoupdate_p = @$_POST['mql']['autoupdate'] == "1";
        $updatefreq_p = max(60, (int)@$_POST['mql']['updatefreq']);
        $had_to_update = 0;
        $updated = 0;
        if ($autoupdate !== $autoupdate_p) {
            $had_to_update++;
            if (update_site_option(MNETQNLOCAL_OPT_AUTOUPDATE, $autoupdate_p)) {
                $updated++;
                $autoupdate = $autoupdate_p;
            }
        }
        if ($updatefreq !== $updatefreq_p) {
            $had_to_update++;
            if (update_site_option(MNETQNLOCAL_OPT_UPDATEFREQ, $updatefreq_p)) {
                $updated++;
                $updatefreq = $updatefreq_p;
            }
        }
        if ($had_to_update < 1) {
            // nessun aggiornamento
            echo '<div class="notice notice-info"><p>Nessuna modifica da salvare nelle impostazioni.</p></div>';
        } elseif ($updated >= $had_to_update) {
            // aggiornamento riuscito
            echo '<div class="notice notice-success"><p>Impostazioni aggiornate con successo.</p></div>';
        } else {
            // aggiornamento fallito
            echo '<div class="notice notice-error"><p>Si è verificato un errore nel salvataggio delle impostazioni.</p></div>';
            return;
        }
    }
    $latest_commit = get_site_option(MNETQNLOCAL_NEXTVER, '');
    $current_commit = get_site_option(MNETQNLOCAL_GITVER, '');
    $to_upgrade = $latest_commit && $current_commit != $latest_commit;
    $manually_upgrade = FALSE;
    $update_zip_url = sprintf(MNETQNLOCAL_GITPULLSSL, $latest_commit);
    if ($to_upgrade && @$_POST['mql_update'] == 'Yass.') {
        $manually_upgrade = TRUE;
        $update_successful = mnetqnlocal_admin_update_from_git();
    }
?>
    <div class="wrap">
        <div id="icon-tools" class="icon32"></div>
        <h1>Monrif Net Q.Net Local Plugin Settings</h1>
        <h2 class="title">Impostazioni aggiornamento plugin</h2>
<?php
        if (isset($update_successful)) {
            $m_class = $update_successful ? 'success' : 'error';
            $m_message = 'L\'aggiornamento del plugin ' . ($update_successful ? '' : 'non ') . 'ha avuto successo.';
            printf('<div class="notice is-dismissible notice-%s"><p>%s</p></div>', $m_class, $m_message);
        } elseif ($to_upgrade) {
?>
        <form method="post">
            <table class="form-table">
              <tbody>
                <tr>
                    <th scope="row"><label>Aggiornamento disponibile!</label></th>
                    <td>
                        <button id="mql_update" name="mql_update" class="button" type="submit" value="Yass.">
                            Aggiorna manualmente
                        </button>
                        <p class="description">
                            La versione aggiornata è disponibile al download al seguente indirizzo:
                            <br /><?php printf('<a href="%1$s">%1$s</a>', $update_zip_url); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </form>
<?php   } ?>
        <form method="post">
            <table class="form-table">
              <tbody>
                <tr>
                    <th scope="row"><label for="mql_autoupdate">Aggiorna automaticamente</label></th>
                    <td>
                        <input id="mql_autoupdate" name="mql[autoupdate]" type="checkbox" value="1" <?php checked($autoupdate, TRUE); ?>/>
                        <span class="description">
                            Abilitare questo campo per permettere al plugin di aggiornarsi autonomamente
                            nel caso venga rilevata una nuova versione su GitHub.
                        </span>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="mql_updatefreq">Frequenza di controllo</label></th>
                    <td>
                        <input id="mql_updatefreq" name="mql[updatefreq]" type="number" value="<?php echo $updatefreq; ?>" min="60" max="1440" />
                        <span class="description">
                            intervallo minimo (in minuti) fra una verifica e l'altra di possibili
                            aggiornamenti del plugin.
                        </span>
                    </td>
                </tr>
              </tbody>
            </table>
            <p class="submit">
                <input name="submit" id="submit" class="button button-primary" value="Salva le modifiche" type="submit" />
            </p>
        </form>
    </div>
<?php
}
