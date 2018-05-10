<script type="text/javascript" src="<?php echo MNETQNLOCAL_RES_URL; ?>webtrekk_v3.min.js"></script>
<script type="text/javascript" id="advtesterjs" src="<?php echo MNETQNLOCAL_RES_URL; ?>advert.js"></script>
<script type="text/javascript">
NAB_has_script = false;
NAB_is_blocked = function() {
    // first check if the test JS advers.js
    // has been included into the page
    if(NAB_has_script === false) {
        NAB_has_script = 0;
        var script = document.getElementById("advtesterjs");
        if(script) NAB_has_script = 1;
        else {
            var scripts = document.getElementsByTagName("script");
            if(scripts && scripts.length) {
                for(var i=0; i<scripts.length; i++) {
                    if (scripts[i].src.match(/advert\.js(?:\?|#|$)/)) {
                        NAB_has_script = 1;
                    }
                }
            }
        }
    }
    if(NAB_has_script !== 1) return false;
    // then, check on a DOM element inserted by that JS's document.write
    // do not use triple === because we need less precision to make it work
    return document.getElementById("advtester") == undefined;
}
</script>
<script type="text/javascript">
(function(){
    if (!webtrekkV3) {
        console.log('No webtrekk library found');
        return;
    }
    var getContentIdByURL = function() {
        var url = document.location.href;
        if(url && url !== null) {
          return url.split("?")[0].toLowerCase();
        }
        return "no_content";
    }
<?php
    $webtrekk_properties = mql_wtk_properties(TRUE);
    $wtk_types = array(
        "cg" => "contentGroup",
        "cp" => "customParameter",
    );
    $wtk_override = array(
        "cg" => array(),
        "cp" => array(
            1 => "document.title",
            9 => "NAB_is_blocked() ? 'YES' : 'NO'",
           14 => "typeof window.isConsentGiven == 'function' && window.isConsentGiven() ? 'YES' : 'NO'",
        ),
    );
    foreach ($wtk_types as $type => $jsvar) {
        echo "\tvar $jsvar = {";
        foreach ($webtrekk_properties[$type] as $wtk_k => $wtk_v) {
            if ($wtk_k > 1) echo ",";
            echo PHP_EOL . "\t\t{$wtk_k} : ";
            if (!empty($wtk_override[$type][$wtk_k])) {
                echo $wtk_override[$type][$wtk_k];
            } else {
                echo "'{$wtk_v}'";
            }
        }
        echo PHP_EOL . "\t}" . PHP_EOL;
    }
?>
    var wtk_config = {
       linkTrack : "link",
       heatmap : "",
       form : "",
       contentId : getContentIdByURL()
    }
    var tracker = new webtrekkV3(wtk_config);
    console.log('webtrekk contentGroup is', contentGroup);
    console.log('webtrekk customParameter is', customParameter);
    tracker.contentGroup = contentGroup;
    tracker.customParameter = customParameter;
    tracker.ignorePrerendering = 'true';
    tracker.sendinfo();
    console.log('Sent webtrekk info');
})();
</script>