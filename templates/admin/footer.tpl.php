<?php

$data['script'] = isset($data['script']) ? $data['script'] : "";

$data['script'] .= <<< 'EOT'
<script>
$(document).ready(function() {
    $.getJSON('update.php?action=check', function(data) {
        if (data.new_version_available == true)
            $('[href="update.php"]').append('<span class="label label-primary pull-right">v'+data.latest_version+'</span>');
    })
});
</script>
EOT;

View::show('footer', $data);
