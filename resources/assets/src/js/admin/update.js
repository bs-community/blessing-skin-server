'use strict';

function downloadUpdates() {
    var fileSize = 0;
    var progress = 0;

    console.log('Prepare to download');

    fetch({
        url: url('admin/update/download?action=prepare-download'),
        type: 'GET',
        dataType: 'json',
        beforeSend: function() {
            $('#update-button').html(
                '<i class="fa fa-spinner fa-spin"></i> ' + trans('admin.preparing')
            ).prop('disabled', 'disabled');
        }
    }).then(json => {
        console.log(json);

        fileSize = json.file_size;

        $('#file-size').html(fileSize);

        $('#modal-start-download').modal({
            'backdrop': 'static',
            'keyboard': false
        });

        console.log('Start downloading');

        fetch({
            url: url('admin/update/download?action=start-download'),
            type: 'POST',
            dataType: 'json'
        }).then(json => {
            // Set progress to 100 when got the response
            progress = 100;

            console.log('Downloading finished');
            console.log(json);
        }).catch(showAjaxError);

        // Downloading progress polling
        let interval_id = window.setInterval(() => {
            $('#imported-progress').html(progress);
            $('.progress-bar').css('width', progress+'%').attr('aria-valuenow', progress);

            if (progress == 100) {
                clearInterval(interval_id);

                $('.modal-title').html('<i class="fa fa-spinner fa-spin"></i> ' + trans('admin.extracting'));
                $('.modal-body').append(`<p>${ trans('admin.downloadCompleted') }</p>`);

                console.log('Start extracting');

                fetch({
                    url: url('admin/update/download?action=extract'),
                    type: 'POST',
                    dataType: 'json'
                }).then(json => {
                    console.log('Package extracted and files are covered');
                    $('#modal-start-download').modal('toggle');

                    swal({
                        type: 'success',
                        html: json.msg
                    }).then(function() {
                        window.location = url('/');
                    }, function() {
                        window.location = url('/');
                    });
                }).catch(showAjaxError);

            } else {
                fetch({
                    url: url('admin/update/download?action=get-file-size'),
                    type: 'GET'
                }).then(json => {
                    progress = (json.size / fileSize * 100).toFixed(2);

                    console.log('Progress: ' + progress);
                }).catch(showAjaxError);
            }

        }, 300);
    }).catch(showAjaxError);

}

if (typeof require !== 'undefined' && typeof module !== 'undefined') {
    module.exports = downloadUpdates;
}
