/* global skinview3d */

// TODO: Help wanted. This file needs to be tested.

$.msp = {};
$.msp.handles = {};
$.msp.control = null;
$.msp.config = {
    domElement: document.getElementById('preview-3d-container'),
    slim: false,
    width: $('#preview-3d-container').width(),
    height: $('#preview-3d-container').height(),
    skinUrl: '',
    capeUrl: ''
};

function initSkinViewer(cameraPositionZ = 70) {
    disposeSkinViewer();

    $.msp.viewer = new skinview3d.SkinViewer($.msp.config);
    $.msp.viewer.camera.position.z = cameraPositionZ;

    // Disable auto model detection
    $.msp.viewer.detectModel = false;
    $.msp.viewer.playerObject.skin.slim = $.msp.config.slim;
    $.msp.viewer.animation = new skinview3d.CompositeAnimation();

    // Init all available animations and pause them
    $.msp.handles.walk   = $.msp.viewer.animation.add(skinview3d.WalkingAnimation);
    $.msp.handles.run    = $.msp.viewer.animation.add(skinview3d.RunningAnimation);
    $.msp.handles.rotate = $.msp.viewer.animation.add(skinview3d.RotatingAnimation);
    $.msp.handles.run.paused = true;

    $.msp.control = skinview3d.createOrbitControls($.msp.viewer);
}

function applySkinViewerConfig(config) {
    config = config || $.msp.config;

    for (const key in config) {
        $.msp.viewer[key] = config[key];
    }
}

function disposeSkinViewer() {
    if ($.msp.viewer instanceof skinview3d.SkinViewer) {
        $.msp.viewer.dispose();
        $.msp.handles = {};
        $.msp.control = undefined;
    }
}

function registerAnimationController() {
    $('.fa-pause').click(function () {
        $.msp.viewer.animationPaused = !$.msp.viewer.animationPaused;
        $(this).toggleClass('fa-pause').toggleClass('fa-play');
    });

    $('.fa-forward').click(function () {
        $.msp.handles.run.paused  = !$.msp.handles.run.paused;
        $.msp.handles.walk.paused = !$.msp.handles.run.paused;
    });

    $('.fa-repeat').click(() => ($.msp.handles.rotate.paused = !$.msp.handles.rotate.paused));
    $('.fa-stop').click(() => {
        initSkinViewer();

        // Pause all animations respectively
        for (const key in $.msp.handles) {
            $.msp.handles[key].paused = true;
        }
    });
}

function registerWindowResizeHandler() {
    $(window).resize(function () {
        $.msp.viewer.width  = $('#preview-3d-container').width();
        $.msp.viewer.height = $('#preview-3d-container').height();
    });
}

if (process.env.NODE_ENV === 'test') {
    module.exports = { initSkinViewer, applySkinViewerConfig, registerAnimationController, registerWindowResizeHandler };
}
