<?php

define('MNETQNLOCAL_OPT_FIXEDHEADER', 'mnetqnlocal_fixed_header');
define('MNETQNLOCAL_OPT_FULLURLEDIT', 'mnetqnlocal_fullurl_edit');
define('MNETQNLOCAL_OPT_OVERRIDE_WTK', 'mnetqnlocal_override_webtrekk');

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

/*
 * mnetqnlocal_get_curl
 * created by Paul long ago
 * performs a CURL w/ headers
 * and returns status + response
 */
function mnetqnlocal_get_curl($url, $return = TRUE, $custom_opts = array()) {
    try {
        $get = curl_init($url);
        curl_setopt($get, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($get, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($get, CURLOPT_MAXREDIRS, 2);
        curl_setopt($get, CURLOPT_SSL_VERIFYPEER, FALSE);
        foreach($custom_opts as $opt => $val) {
            curl_setopt($get, $opt, $val);
        }
        if ($return) {
            curl_setopt($get, CURLOPT_RETURNTRANSFER, TRUE);
        }
        $payload = curl_exec($get);
        $http_code = curl_getinfo($get, CURLINFO_HTTP_CODE);
        curl_close($get);
        if ($return) {
            return array(
                    "payload" => $payload,
                    "status" => $http_code,
                );
        }
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function mnetqnlocal_admin_page() {
    if (!current_user_can('update_plugins')) {
        echo "<p>Eh no.</p>";
        return;
    }
    $fixedheader = get_site_option(MNETQNLOCAL_OPT_FIXEDHEADER, FALSE);
    $fullurledit = get_site_option(MNETQNLOCAL_OPT_FULLURLEDIT, TRUE);
    $override_wtk = get_site_option(MNETQNLOCAL_OPT_OVERRIDE_WTK, '');
    if (isset($_POST['mql'])) {
        $fixedheader_p = @$_POST['mql']['fixedheader'] == '1';
        $fullurledit_p = @$_POST['mql']['fullurledit'] == '1';
        $override_wtk_p = trim(@$_POST['mql']['override_wtk']);
        $had_to_update = array();
        $updated = array();
        $toupdate = array(
            "fixedheader" => MNETQNLOCAL_OPT_FIXEDHEADER,
            "fullurledit" => MNETQNLOCAL_OPT_FULLURLEDIT,
            "override_wtk" => MNETQNLOCAL_OPT_OVERRIDE_WTK,
        );
        foreach ($toupdate as $name => $option) {
            if ($$name !== ${$name."_p"}) {
                $had_to_update[] = "$name";
                if ($$name == ${$name."_p"}) {
                    delete_site_option($option);
                }
                $value = ${$name."_p"} === FALSE ? 0 : ${$name."_p"};
                if (update_site_option($option, $value)) {
                    $updated[] = "$name";
                    $$name = ${$name."_p"};
                }
            }
        }
        if (count($had_to_update) < 1) {
            // nessun aggiornamento
            echo '<div class="notice notice-info"><p>Nessuna modifica da salvare nelle impostazioni.</p></div>';
        } elseif (count($updated) >= count($had_to_update)) {
            // aggiornamento riuscito
            echo '<div class="notice notice-success"><p>Impostazioni aggiornate con successo.</p></div>';
        } else {
            // aggiornamento fallito
            echo '<div class="notice notice-error"><p>
                Si è verificato un errore nel salvataggio delle impostazioni.<br />
                Opzioni aggiornate: <strong>' . implode(', ', $updated) . '</strong><br />
                Opzioni problematiche: <strong>' . implode(', ', array_diff($had_to_update, $updated)) . '</strong><br />
                <em><a href="">Ricarica la pagina per riprovare</a></em>
                </p></div>';
            return;
        }
    }
?>
    <div class="wrap">
        <div id="icon-tools" class="icon32"></div>
        <h1>Monrif Net Q.Net Local Plugin Settings</h1>
        <form method="post">
            <h2 class="title">Impostazioni network Q.Net</h2>
            <table class="form-table">
              <tbody>
                <tr>
                    <th scope="row"><label for="mql_fixedheader">Mini-header fisso</label></th>
                    <td>
                        <input id="mql_fixedheader" name="mql[fixedheader]" type="checkbox" value="1" <?php checked($fixedheader, TRUE); ?>/>
                        <span class="description">
                            Selezionando questa opzione, il mini-header del network QN
                            assumerà posizione fixed e sarà sempre visibile in cima alla pagina.
                        </span>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="mql_fullurledit">Impedire modifica dell'URL</label></th>
                    <td>
                        <input id="mql_fullurledit" name="mql[fullurledit]" type="checkbox" value="1" <?php checked($fullurledit, TRUE); ?>/>
                        <span class="description">
                            Selezionando questa opzione, l'URL generato per un articolo non
                            potrà essere cambiato finché tale articolo rimarrà pubblico.
                        </span>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="mql_override_wtk">Parametri Webtrekk analytics</label></th>
                    <td>
                        <span class="description">
                            <strong>SOLO PER ESPERTI:</strong> modificare questo campo per
                            configurare i parametri di tracciamento Webtrekk. Lasciare vuoto
                            perché vengano utilizzati valori di default.
                        </span>
                        <pre><?php
                            echo "// proprietà modificabili:" . PHP_EOL;
                            foreach (mql_wtk_default_properties() as $wtk_type => $wtk_values) {
                                foreach ($wtk_values as $wtk_k => $wtk_v) {
                                    printf('%s%s: %s' . PHP_EOL, $wtk_type, str_pad($wtk_k, 2, "0", STR_PAD_LEFT), $wtk_v);
                                }
                            }
                        ?></pre>
                        <textarea id="mql_fullurledit" name="mql[override_wtk]" cols="50" rows="4"><?php echo trim($override_wtk); ?></textarea>
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
