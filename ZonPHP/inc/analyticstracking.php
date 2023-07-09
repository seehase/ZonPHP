<!-- Global site tag (gtag.js) - Google Analytics -->
<?php
global $params;
?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= trim($params['googleTrackingId']) ?>"></script>
<script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
        dataLayer.push(arguments);
    }

    gtag('js', new Date());

    gtag('config', '<?= trim($params['googleTrackingId']) ?>');
</script>
