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
<?php
    $webtrekk_page_type = "home";
    $webtrekk_keywords = "";
    $webtrekk_category = "";
    if (is_singular()) {
        $webtrekk_page_type = "articolo";
        $the_tags = array();
        foreach (get_the_tags() as $tag) {
            $the_tags []= $tag->name;
        }
        $webtrekk_keywords = implode(",", $the_tags);
        $webtrekk_category = @get_the_category();
        if ($webtrekk_category and count($webtrekk_category)) {
            $webtrekk_category = $webtrekk_category[0]->name;
        } else {
            $webtrekk_category = "";
        }
    } elseif (is_archive()) {
        $webtrekk_page_type = "sezione";
    }
?>
<script type="text/javascript">
(function(){
    if (!webtrekkV3) {
        console.log('No webtrekk library found');
        return;
    }
    var getFromURL = function(what) {
        if (typeof what != 'number' && what > 0) return false;
        var url = location.host || location.hostname || location.href || '';
        var regexp = /^(?:http:\/\/)?([^.]+)\.([^.]+)\.(net|it|com|org)(?:\/|$)/i;
        var urlparse = url.match(regexp);
        var site = 'qn';
        if (urlparse && urlparse.length > what) {
            var level = urlparse.length - what;
            return urlparse[level];
        } 
        return false;
    }
    var getTestataFromURL = function() {
        var testata = getFromURL(2);
        switch (testata) {
            case 'ilrestodelcarlino':
                return 'ilresto';
            case 'quotidiano':
                return 'qn';
            default:
                break;
        }
        return testata;
    }
    var getEditionFromURL = function() {
        var testata = getTestataFromURL();
        if (testata in ['qn', 'ilresto', 'lanazione', 'ilgiorno']) {
            var edizione = getFromURL(3);
            if (edizione) return testata + " - " + edizione;
        }
        return false;
    }
    var getFromSchemaOrg = function(key) {
        if (key) {
            var schema = window.schemaOrgJs || false;
            if (!schema) {
                var scripts = document.head.getElementsByTagName('script');
                for (var i = 0; i < scripts.length; i++) {
                    if (scripts[i].type != 'application/ld+json') continue;
                    var script = {};
                    try {
                        script = JSON.parse(scripts[i].innerText);
                    } catch(e) {
                        // console.log("didn't parse!", e);
                    }
                    if ('@context' in script && script['@context'].match(/\bschema\.org\b/)) {
                        schema = script;
                        break;
                    }
                }
            }
            if (schema) {
                window.schemaOrgJs = schema;
                if (key in schema) return schema[key];
            }
        }
        return false;
    }
    var getExistsParameterByName = function(name) {
        var match = RegExp('[?&]'+name).exec(window.location.search);
        if (match) return name;
        return 'no-refresh';
    }
    var getContentIdByURL = function() {
        var url = document.location.href;
        if(url && url !== null) {
          return url.split("?")[0].toLowerCase();
        }
        return "no_content";
    }
    var na = 'na';
    var typeFromSchema = "home-sezione";
    switch (getFromSchemaOrg('@type')) {
        case "NewsArticle":
            typeFromSchema = "articolo";
            break;
        case "ImageGallery":
            typeFromSchema = "foto";
            break;
        case "VideoObject":
            typeFromSchema = "video";
            break;
    }
    var config = {
        1: 'Aggregato Monrif',
        2: 'qn-local',
        3: getTestataFromURL(),
        4: getEditionFromURL() || na,
        5: typeFromSchema || na,
        6: '<?php echo $webtrekk_category; ?>' || na
    }
    if ("wtk_cg" in window) {
        for (var k in wtk_cg) {
            config[k] = wtk_cg[k];
        }
    }
    var contentGroup = {}
    for (var i = 1; i < 10; i++) {
        if (!(i in config)) break;
        contentGroup[i] = config[i];
    }
    var keywords = '<?php echo $webtrekk_keywords; ?>';
    //if (typeof keywords == "string") keywords = keywords.split(/\s*,\s*/);
    //if (keywords && keywords.length) keywords = keywords.join(',');
    //else keywords = na;
    var customParameter = {
        1  : document.title,
        2  : getExistsParameterByName('refresh_ce'),
        3  : contentGroup[6] || na,
        4  : 'external.site.leccenews24', // or 'internal.site.qn'
        //5  : 'internal.page.qn.motori',
        7  : contentGroup[5] || na,
        9  : NAB_is_blocked() ? 'YES' : 'NO',
        13 : keywords,
        14 : typeof window.isConsentGiven == 'function' && window.isConsentGiven() ? "YES" : "NO",
        15 : '<?php the_author(); ?>' || na,
        16 : '<?php the_author_meta("ID"); ?>' || na
    }
    if ("wtk_cp" in window) {
        for (var k in wtk_cp) {
            customParameter[k] = wtk_cp[k];
        }
    }
    for (var i = 1; i <= 25; i++) if (!customParameter[i]) customParameter[i] = na;
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