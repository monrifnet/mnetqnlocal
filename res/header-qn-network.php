<?php
    $mql_fixedheader = get_site_option(MNETQNLOCAL_OPT_FIXEDHEADER, FALSE);
    $mql_headerpos = $mql_fixedheader ? 'data-position="fixed"' : '';
?>

<!-- the common header and menus -->
<div id="qn-external-menu" data-json-menu="https://s3-eu-west-1.amazonaws.com/static.quotidiano.net/qn-external-menu/menu.localmente.json" <?php echo $mql_headerpos ?>>
<noscript>
<div class="qn-external-menu-container"><div id="qn-desktop-menu"><div class="qn-wrapper-header"><div class="qn-header"><div class="qn-top-header"><div class="qn-logo-block"><div id="qn-logo"><a tabindex="0" href="https://www.quotidiano.net/">QuotidianoNet</a> <a tabindex="1" href="http://www.ilrestodelcarlino.it/">il Resto del Carlino</a> <a tabindex="2" href="http://www.lanazione.it/">La Nazione</a> <a tabindex="3" href="http://www.ilgiorno.it/">Il Giorno</a> <a tabindex="4" href="https://www.iltelegrafolivorno.it/">Il Telegrafo</a></div></div></div></div></div></div></div>
</noscript>
</div>

<script type="text/javascript">
  (function(){
    var header = document.getElementById('qn-external-menu');
    if (!header) return;
    try {
        // moves the header far up in the page
        document.body.insertBefore(header, document.body.firstChild);
    } catch (e) {
        // if something had gone wrong already, issue order 66
        header.parentNode.removeChild(header);
    }
<?php if ($mql_fixedheader): ?>
    // slides the header up if it's absolute
    if (typeof getComputedStyle == "function") {
        var headerH = 40;
        var bodyMargin = 0;
        try {
            bodyMargin = parseFloat(getComputedStyle(document.body).paddingTop);
        } catch (e) {}
        if (headerH > bodyMargin) {
            document.body.style.paddingTop = headerH + "px";
            bodyMargin = headerH;
        }
    }
<?php endif; ?>
  })();
</script>

<script src="https://s3-eu-west-1.amazonaws.com/static.quotidiano.net/qn-external-menu/bundle.75b202c.js" async></script>
