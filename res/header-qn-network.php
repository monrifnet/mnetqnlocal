<?php
    // check if we support this, e.g. on MOTORI
    // needs follow up in critical CSS
    $mql_fixedheader = get_site_option(MNETQNLOCAL_OPT_FIXEDHEADER, FALSE);
    $mql_headerpos = $mql_fixedheader ? 'data-position="fixed"' : '';
?>
<div id="qn-react-menu" class="qn-react-menu" data-layout="small" data-env="www" data-show-login="false"></div>
<script type="text/javascript">
  (function(){
    var header = document.getElementById('qn-react-menu');
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
<!-- this goes at end of body-->
<script src="https://cdn-static.quotidiano.net/partner-menu/menu.js"></script>
