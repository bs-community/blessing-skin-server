/* global MSP */

class TexturePreview {

    /**
     * 
     * @param {'steve'|'alex'|'cape'} type 
     * @param {number} tid 
     * @param {'default'|'slim'} preference 
     */
    constructor(type, tid, preference) {
        this.tid               = tid;
        this.type              = type;
        /**
         * @type {JQuery<HTMLImageElement>}
         */
        this.selector          = $(`#${type}`);
        /**
         * @type {'default'|'slim'}
         */
        this.preference        = (type == 'steve') ? 'default' : 'slim';
        this.playerPreference  = preference;
    }

    change2dPreview() {
        this.selector
            .attr('src', url(`preview/200/${this.tid}.png`))
            .show()
            .parent().attr('href', url(`skinlib/show/${this.tid}`))
            .next().hide();

        return this;
    }

    async change3dPreview() {
        if (this.playerPreference == this.preference || this.type == 'cape') {
            try {
                const { hash } = await fetch({
                    type: 'GET',
                    url: url(`skinlib/info/${this.tid}`),
                    dataType: 'json'
                });

                let textureUrl = url(`textures/${hash}`);

                if (this.type == 'cape') {
                    MSP.changeCape(textureUrl);
                } else {
                    MSP.changeSkin(textureUrl);
                }
            } catch (error) {
                showAjaxError(error);
            }
        }

        return this;
    }

    showNotUploaded() {
        this.selector.hide().parent().next().show();

        // clear 3D preview of cape
        if (this.type == 'cape') {
            MSP.changeCape('');
        }

        return this;
    }

    static init3dPreview() {
        if (TexturePreview.previewType == '2D') return;
    
        $('#preview-2d').hide();
    
        let canvas = null;
    
        if ($(window).width() < 800) {
            canvas = MSP.get3dSkinCanvas($('#skinpreview').width(), $('#skinpreview').width());
            $('#skinpreview').append($(canvas).prop('id', 'canvas3d'));
        } else {
            canvas = MSP.get3dSkinCanvas(350, 350);
            $('#skinpreview').append($(canvas).prop('id', 'canvas3d'));
        }
    }

    static show3dPreview() {
        TexturePreview.previewType = '3D';
    
        TexturePreview.init3dPreview();
        $('#preview-2d').hide();
        $('.operations').show();
        $('#preview-switch').html(trans('user.switch2dPreview'));
    }

    static show2dPreview() {
        TexturePreview.previewType = '2D';
    
        $('#canvas3d').remove();
        $('.operations').hide();
        $('#preview-2d').show();
        $('#preview-switch').html(trans('user.switch3dPreview')).attr('onclick', 'show3dPreview();');
    }
}

TexturePreview.previewType = '3D';

// change 3D preview status
$('.fa-pause').click(function () {
    MSP.setStatus('rotation',  ! MSP.getStatus('rotation'));
    MSP.setStatus('movements', ! MSP.getStatus('movements'));

    $(this).toggleClass('fa-pause').toggleClass('fa-play');
});

$('.fa-forward').click(() => MSP.setStatus('running',  ! MSP.getStatus('running')));
$('.fa-repeat' ).click(() => MSP.setStatus('rotation', ! MSP.getStatus('rotation')));

if (typeof require !== 'undefined' && typeof module !== 'undefined') {
    module.exports = TexturePreview;
}
