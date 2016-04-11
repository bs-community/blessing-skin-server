/*
* @Author: printempw
* @Date:   2016-04-03 13:44:36
* @Last Modified by:   printempw
* @Last Modified time: 2016-04-11 17:16:30
*/

'use strict';

$(document).ready(function() {
    $('pre').each(function(i, block) {
        hljs.highlightBlock(block);
    });
});

$('#mod-select').change(function() {
    $('#version-select').children().each(function() { $(this).remove(); });

    if ($(this).val() == "csl") {
        $('#version-select').append('<option value="13_1-upper">13.1 版及以上（推荐）</option>');
        $('#version-select').append('<option value="13_1-lower">13.1 版以下</option>');
    } else if ($(this).val() == "usm") {
        $('#version-select').append('<option value="1_4-upper">1.4 版及以上（推荐）</option>');
        $('#version-select').append('<option value="1_2-1_3">1.2 及 1.3 版</option>');
        $('#version-select').append('<option value="1_2-lower">1.2 版以下</option>');
    }

    showConfig();
});

function showConfig() {
    $('#config-13_1-upper').hide();
    $('#config-13_1-lower').hide();
    $('#config-1_4-upper').hide();
    $('#config-1_2-1_3').hide();
    $('#config-1_2-lower').hide();
    $('#config-'+$('#version-select').val()).show();
}

$('#version-select').change(showConfig);
