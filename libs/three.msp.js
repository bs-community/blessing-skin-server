/*
 * @Author: earthiverse
 *
 * Cape & HD support by xfl03
 * Encapsulate by printempw
 */

var scene, camera, renderer;
var geometry, material, mesh, material3;

var rightLeg2Box, leftLeg2Box;

var capeLoaded = false;
var capeToggle = document.getElementById('capeToggle');
var cape;

var deltaMouseX = 0;
var deltaMouseY = 0;
var originMouseX = 0;
var originMouseY = 0;
var isMouseDown = false;
var displayCanvas;
var allowFullScreenDrag=true;
var onMouseMove = function(e) {
    if (isMouseDown) {
        mouseX = e.pageX - displayCanvas.offsetLeft;
        deltaMouseX = mouseX - originMouseX;
        originMouseX = mouseX;

        mouseY = e.pageY - displayCanvas.offsetTop;
        deltaMouseY = mouseY - originMouseY;
        originMouseY = mouseY;
    }
};

var speed=1;

var radius = 32;
var alpha1 = 0; //For camera y rotation
var alpha2 = 0; //For camera x rotation
var alpha3 = 0; //For arms and legs y rotation (Swing)

var sidebarWidth = 250;

camera = new THREE.PerspectiveCamera(75, (window.innerWidth - sidebarWidth) / window.innerHeight, 1, 10000);

scene = new THREE.Scene();

// Skin Part
canvas = document.createElement('canvas');;
canvas.width = 64;
canvas.height = 64;
var context = canvas.getContext("2d");

var skinTexture = new THREE.Texture(canvas);
skinTexture.magFilter = THREE.NearestFilter;
skinTexture.minFilter = THREE.NearestMipMapNearestFilter;

// Cape Part
var canvas2 = document.createElement('canvas');;
canvas2.width = 22;
canvas2.height = 17;
var context2 = canvas2.getContext("2d");
var capeTexture = new THREE.Texture(canvas2);
capeTexture.magFilter = THREE.NearestFilter;
capeTexture.minFilter = THREE.NearestMipMapNearestFilter;

// Get the texture for the skin
material = new THREE.MeshBasicMaterial({map: skinTexture, side: THREE.FrontSide});
material2 = new THREE.MeshBasicMaterial({map: skinTexture, transparent: true, opacity: 1, alphaTest: 0.5, side: THREE.DoubleSide});
// Get the texture for the cape
material3 = new THREE.MeshBasicMaterial({map: capeTexture});

var skin = new Image();
skin.crossOrigin = '';
var hasAnimate = false;
skin.onload = function() {
    console.log("Loaded Skin");

    // hange the size of canvas
    canvas.width = skin.width;
    canvas.height = skin.width;

    // Erase what was on the canvas before
    context.clearRect(0, 0, skin.width, skin.width);

    // Draw the image to the canvas
    context.drawImage(skin, 0, 0);

    // Convert the image if need be
    if(skin.height == skin.width/2) Convert6432To6464(context,skin.width/64);
    FixNonVisible(context,skin.width/64);
    FixOverlay(context,skin.width/64);

    skinTexture.needsUpdate = true;

    material.needsUpdate = true;
    material2.needsUpdate = true;

    if(!hasAnimate) {
        RenderSkin();
        hasAnimate = true;
        Animate();
    }
}
skin.onerror = function() {
    console.log("Failed loading " + skin.src);
}
var cape = new Image();
cape.crossOrigin = '';
cape.onload = function() {
    if (cape.width/cape.height == 2) {
        canvas2.width = cape.width/64*22;
        canvas2.height = cape.height/32*17;
    } else {
        canvas2.width = cape.width;
        canvas2.height = cape.width;
    }
    console.log("Loaded Cape");

    // Erase what was on the canvas before
    context2.clearRect(0, 0, canvas2.width, canvas2.height);

    // Draw the image to the canvas
    context2.drawImage(cape, 0, 0);

    capeTexture.needsUpdate = true;
    material3.needsUpdate = true;

    capeLoaded = true;
    if (!hasAnimate) {
        RenderSkin();
        hasAnimate = true;
        Animate();
    }
}
cape.onerror = function() {
    capeLoaded = false;
    console.log("Failed loading " + img.src);
}

function RenderSkin() {
    // Head Parts
    var headTop = [
        new THREE.Vector2(0.125, 0.875),
        new THREE.Vector2(0.25, 0.875),
        new THREE.Vector2(0.25, 1),
        new THREE.Vector2(0.125, 1)
    ];
    var headBottom = [
        new THREE.Vector2(0.25, 0.875),
        new THREE.Vector2(0.375, 0.875),
        new THREE.Vector2(0.375, 1),
        new THREE.Vector2(0.25, 1)
    ];
    var headLeft = [
        new THREE.Vector2(0, 0.75),
        new THREE.Vector2(0.125, 0.75),
        new THREE.Vector2(0.125, 0.875),
        new THREE.Vector2(0, 0.875)
    ];
    var headFront = [
        new THREE.Vector2(0.125, 0.75),
        new THREE.Vector2(0.25, 0.75),
        new THREE.Vector2(0.25 ,0.875),
        new THREE.Vector2(0.125 ,0.875)
    ];
    var headRight = [
        new THREE.Vector2(0.25, 0.75),
        new THREE.Vector2(0.375, 0.75),
        new THREE.Vector2(0.375, 0.875),
        new THREE.Vector2(0.25, 0.875)
    ];
    var headBack = [
        new THREE.Vector2(0.375, 0.75),
        new THREE.Vector2(0.5, 0.75),
        new THREE.Vector2(0.5, 0.875),
        new THREE.Vector2(0.375, 0.875)
    ];
    headBox = new THREE.BoxGeometry(8, 8, 8, 0, 0, 0);
    headBox.faceVertexUvs[0] = [];
    headBox.faceVertexUvs[0][0] = [headRight[3], headRight[0], headRight[2]];
    headBox.faceVertexUvs[0][1] = [headRight[0], headRight[1], headRight[2]];
    headBox.faceVertexUvs[0][2] = [headLeft[3], headLeft[0], headLeft[2]];
    headBox.faceVertexUvs[0][3] = [headLeft[0], headLeft[1], headLeft[2]];
    headBox.faceVertexUvs[0][4] = [headTop[3], headTop[0], headTop[2]];
    headBox.faceVertexUvs[0][5] = [headTop[0], headTop[1], headTop[2]];
    headBox.faceVertexUvs[0][6] = [headBottom[0], headBottom[3], headBottom[1]];
    headBox.faceVertexUvs[0][7] = [headBottom[3], headBottom[2], headBottom[1]];
    headBox.faceVertexUvs[0][8] = [headFront[3], headFront[0], headFront[2]];
    headBox.faceVertexUvs[0][9] = [headFront[0], headFront[1], headFront[2]];
    headBox.faceVertexUvs[0][10] = [headBack[3], headBack[0], headBack[2]];
    headBox.faceVertexUvs[0][11] = [headBack[0], headBack[1], headBack[2]];
    headMesh = new THREE.Mesh(headBox, material);
    headMesh.name = "head";
    scene.add(headMesh);

    // Body Parts
    var bodyTop = [
        new THREE.Vector2(0.3125, 0.6875),
        new THREE.Vector2(0.4375, 0.6875),
        new THREE.Vector2(0.4375, 0.75),
        new THREE.Vector2(0.3125, 0.75)
    ];
    var bodyBottom = [
        new THREE.Vector2(0.4375, 0.6875),
        new THREE.Vector2(0.5625, 0.6875),
        new THREE.Vector2(0.5625, 0.75),
        new THREE.Vector2(0.4375, 0.75)
    ];
    var bodyLeft = [
        new THREE.Vector2(0.25, 0.5),
        new THREE.Vector2(0.3125, 0.5),
        new THREE.Vector2(0.3125, 0.6875),
        new THREE.Vector2(0.25, 0.6875)
    ];
    var bodyFront = [
        new THREE.Vector2(0.3125, 0.5),
        new THREE.Vector2(0.4375, 0.5),
        new THREE.Vector2(0.4375, 0.6875),
        new THREE.Vector2(0.3125, 0.6875)
    ];
    var bodyRight = [
        new THREE.Vector2(0.4375, 0.5),
        new THREE.Vector2(0.5, 0.5),
        new THREE.Vector2(0.5, 0.6875),
        new THREE.Vector2(0.4375, 0.6875)
    ];
    var bodyBack = [
        new THREE.Vector2(0.5, 0.5),
        new THREE.Vector2(0.625, 0.5),
        new THREE.Vector2(0.625, 0.6875),
        new THREE.Vector2(0.5, 0.6875)
    ];
    bodyBox = new THREE.BoxGeometry(8, 12, 4, 0, 0, 0);
    bodyBox.faceVertexUvs[0] = [];
    bodyBox.faceVertexUvs[0][0] = [bodyRight[3], bodyRight[0], bodyRight[2]];
    bodyBox.faceVertexUvs[0][1] = [bodyRight[0], bodyRight[1], bodyRight[2]];
    bodyBox.faceVertexUvs[0][2] = [bodyLeft[3], bodyLeft[0], bodyLeft[2]];
    bodyBox.faceVertexUvs[0][3] = [bodyLeft[0], bodyLeft[1], bodyLeft[2]];
    bodyBox.faceVertexUvs[0][4] = [bodyTop[3], bodyTop[0], bodyTop[2]];
    bodyBox.faceVertexUvs[0][5] = [bodyTop[0], bodyTop[1], bodyTop[2]];
    bodyBox.faceVertexUvs[0][6] = [bodyBottom[0], bodyBottom[3], bodyBottom[1]];
    bodyBox.faceVertexUvs[0][7] = [bodyBottom[3], bodyBottom[2], bodyBottom[1]];
    bodyBox.faceVertexUvs[0][8] = [bodyFront[3], bodyFront[0], bodyFront[2]];
    bodyBox.faceVertexUvs[0][9] = [bodyFront[0], bodyFront[1], bodyFront[2]];
    bodyBox.faceVertexUvs[0][10] = [bodyBack[3], bodyBack[0], bodyBack[2]];
    bodyBox.faceVertexUvs[0][11] = [bodyBack[0], bodyBack[1], bodyBack[2]];
    bodyMesh = new THREE.Mesh(bodyBox, material);
    bodyMesh.name = "body";
    bodyMesh.position.y = -10;
    scene.add(bodyMesh);

    // Right Arm Parts
    var rightArmTop = [
        new THREE.Vector2(0.6875, 0.6875),
        new THREE.Vector2(0.75, 0.6875),
        new THREE.Vector2(0.75, 0.75),
        new THREE.Vector2(0.6875, 0.75),
    ];
    var rightArmBottom = [
        new THREE.Vector2(0.75, 0.6875),
        new THREE.Vector2(0.8125, 0.6875),
        new THREE.Vector2(0.8125, 0.75),
        new THREE.Vector2(0.75, 0.75)
    ];
    var rightArmLeft = [
        new THREE.Vector2(0.625, 0.5),
        new THREE.Vector2(0.6875, 0.5),
        new THREE.Vector2(0.6875, 0.6875),
        new THREE.Vector2(0.625, 0.6875)
    ];
    var rightArmFront = [
        new THREE.Vector2(0.6875, 0.5),
        new THREE.Vector2(0.75, 0.5),
        new THREE.Vector2(0.75, 0.6875),
        new THREE.Vector2(0.6875, 0.6875)
    ];
    var rightArmRight = [
        new THREE.Vector2(0.75, 0.5),
        new THREE.Vector2(0.8125, 0.5),
        new THREE.Vector2(0.8125, 0.6875),
        new THREE.Vector2(0.75, 0.6875)
    ];
    var rightArmBack = [
        new THREE.Vector2(0.8125, 0.5),
        new THREE.Vector2(0.875, 0.5),
        new THREE.Vector2(0.875, 0.6875),
        new THREE.Vector2(0.8125, 0.6875)
    ];
    rightArmBox = new THREE.BoxGeometry(4, 12, 4, 0, 0, 0);
    rightArmBox.faceVertexUvs[0] = [];
    rightArmBox.faceVertexUvs[0][0] = [rightArmRight[3], rightArmRight[0], rightArmRight[2]];
    rightArmBox.faceVertexUvs[0][1] = [rightArmRight[0], rightArmRight[1], rightArmRight[2]];
    rightArmBox.faceVertexUvs[0][2] = [rightArmLeft[3], rightArmLeft[0], rightArmLeft[2]];
    rightArmBox.faceVertexUvs[0][3] = [rightArmLeft[0], rightArmLeft[1], rightArmLeft[2]];
    rightArmBox.faceVertexUvs[0][4] = [rightArmTop[3], rightArmTop[0], rightArmTop[2]];
    rightArmBox.faceVertexUvs[0][5] = [rightArmTop[0], rightArmTop[1], rightArmTop[2]];
    rightArmBox.faceVertexUvs[0][6] = [rightArmBottom[0], rightArmBottom[3], rightArmBottom[1]];
    rightArmBox.faceVertexUvs[0][7] = [rightArmBottom[3], rightArmBottom[2], rightArmBottom[1]];
    rightArmBox.faceVertexUvs[0][8] = [rightArmFront[3], rightArmFront[0], rightArmFront[2]];
    rightArmBox.faceVertexUvs[0][9] = [rightArmFront[0], rightArmFront[1], rightArmFront[2]];
    rightArmBox.faceVertexUvs[0][10] = [rightArmBack[3], rightArmBack[0], rightArmBack[2]];
    rightArmBox.faceVertexUvs[0][11] = [rightArmBack[0], rightArmBack[1], rightArmBack[2]];
    rightArmMesh = new THREE.Mesh(rightArmBox, material);
    rightArmMesh.name = "rightArm";
    rightArmMesh.position.y = -10;
    rightArmMesh.position.x = -6;
    scene.add(rightArmMesh);

    // Left Arm Parts
    var leftArmTop = [
        new THREE.Vector2(0.5625, 0.1875),
        new THREE.Vector2(0.625, 0.1875),
        new THREE.Vector2(0.625, 0.25),
        new THREE.Vector2(0.5625, 0.25),
    ];
    var leftArmBottom = [
        new THREE.Vector2(0.625, 0.1875),
        new THREE.Vector2(0.6875, 0.1875),
        new THREE.Vector2(0.6875, 0.25),
        new THREE.Vector2(0.625, 0.25)
    ];
    var leftArmLeft = [
        new THREE.Vector2(0.5, 0),
        new THREE.Vector2(0.5625, 0),
        new THREE.Vector2(0.5625, 0.1875),
        new THREE.Vector2(0.5, 0.1875)
    ];
    var leftArmFront = [
        new THREE.Vector2(0.5625, 0),
        new THREE.Vector2(0.625, 0),
        new THREE.Vector2(0.625, 0.1875),
        new THREE.Vector2(0.5625, 0.1875)
    ];
    var leftArmRight = [
        new THREE.Vector2(0.625, 0),
        new THREE.Vector2(0.6875, 0),
        new THREE.Vector2(0.6875, 0.1875),
        new THREE.Vector2(0.625, 0.1875)
    ];
    var leftArmBack = [
        new THREE.Vector2(0.6875, 0),
        new THREE.Vector2(0.75, 0),
        new THREE.Vector2(0.75, 0.1875),
        new THREE.Vector2(0.6875, 0.1875)
    ];
    leftArmBox = new THREE.BoxGeometry(4, 12, 4, 0, 0, 0);
    leftArmBox.faceVertexUvs[0] = [];
    leftArmBox.faceVertexUvs[0][0] = [leftArmRight[3], leftArmRight[0], leftArmRight[2]];
    leftArmBox.faceVertexUvs[0][1] = [leftArmRight[0], leftArmRight[1], leftArmRight[2]];
    leftArmBox.faceVertexUvs[0][2] = [leftArmLeft[3], leftArmLeft[0], leftArmLeft[2]];
    leftArmBox.faceVertexUvs[0][3] = [leftArmLeft[0], leftArmLeft[1], leftArmLeft[2]];
    leftArmBox.faceVertexUvs[0][4] = [leftArmTop[3], leftArmTop[0], leftArmTop[2]];
    leftArmBox.faceVertexUvs[0][5] = [leftArmTop[0], leftArmTop[1], leftArmTop[2]];
    leftArmBox.faceVertexUvs[0][6] = [leftArmBottom[0], leftArmBottom[3], leftArmBottom[1]];
    leftArmBox.faceVertexUvs[0][7] = [leftArmBottom[3], leftArmBottom[2], leftArmBottom[1]];
    leftArmBox.faceVertexUvs[0][8] = [leftArmFront[3], leftArmFront[0], leftArmFront[2]];
    leftArmBox.faceVertexUvs[0][9] = [leftArmFront[0], leftArmFront[1], leftArmFront[2]];
    leftArmBox.faceVertexUvs[0][10] = [leftArmBack[3], leftArmBack[0], leftArmBack[2]];
    leftArmBox.faceVertexUvs[0][11] = [leftArmBack[0], leftArmBack[1], leftArmBack[2]];
    leftArmMesh = new THREE.Mesh(leftArmBox, material);
    leftArmMesh.name = "leftArm";
    leftArmMesh.position.y = -10;
    leftArmMesh.position.x = 6;
    scene.add(leftArmMesh);

    // Right Leg Parts
    var rightLegTop = [
        new THREE.Vector2(0.0625, 0.6875),
        new THREE.Vector2(0.125, 0.6875),
        new THREE.Vector2(0.125, 0.75),
        new THREE.Vector2(0.0625, 0.75),
    ];
    var rightLegBottom = [
        new THREE.Vector2(0.125, 0.6875),
        new THREE.Vector2(0.1875, 0.6875),
        new THREE.Vector2(0.1875, 0.75),
        new THREE.Vector2(0.125, 0.75)
    ];
    var rightLegLeft = [
        new THREE.Vector2(0, 0.5),
        new THREE.Vector2(0.0625, 0.5),
        new THREE.Vector2(0.0625, 0.6875),
        new THREE.Vector2(0, 0.6875)
    ];
    var rightLegFront = [
        new THREE.Vector2(0.0625, 0.5),
        new THREE.Vector2(0.125, 0.5),
        new THREE.Vector2(0.125, 0.6875),
        new THREE.Vector2(0.0625, 0.6875)
    ];
    var rightLegRight = [
        new THREE.Vector2(0.125, 0.5),
        new THREE.Vector2(0.1875, 0.5),
        new THREE.Vector2(0.1875, 0.6875),
        new THREE.Vector2(0.125, 0.6875)
    ];
    var rightLegBack = [
        new THREE.Vector2(0.1875, 0.5),
        new THREE.Vector2(0.25, 0.5),
        new THREE.Vector2(0.25, 0.6875),
        new THREE.Vector2(0.1875, 0.6875)
    ];
    rightLegBox = new THREE.BoxGeometry(4, 12, 4, 0, 0, 0);
    rightLegBox.faceVertexUvs[0] = [];
    rightLegBox.faceVertexUvs[0][0] = [rightLegRight[3], rightLegRight[0], rightLegRight[2]];
    rightLegBox.faceVertexUvs[0][1] = [rightLegRight[0], rightLegRight[1], rightLegRight[2]];
    rightLegBox.faceVertexUvs[0][2] = [rightLegLeft[3], rightLegLeft[0], rightLegLeft[2]];
    rightLegBox.faceVertexUvs[0][3] = [rightLegLeft[0], rightLegLeft[1], rightLegLeft[2]];
    rightLegBox.faceVertexUvs[0][4] = [rightLegTop[3], rightLegTop[0], rightLegTop[2]];
    rightLegBox.faceVertexUvs[0][5] = [rightLegTop[0], rightLegTop[1], rightLegTop[2]];
    rightLegBox.faceVertexUvs[0][6] = [rightLegBottom[0], rightLegBottom[3], rightLegBottom[1]];
    rightLegBox.faceVertexUvs[0][7] = [rightLegBottom[3], rightLegBottom[2], rightLegBottom[1]];
    rightLegBox.faceVertexUvs[0][8] = [rightLegFront[3], rightLegFront[0], rightLegFront[2]];
    rightLegBox.faceVertexUvs[0][9] = [rightLegFront[0], rightLegFront[1], rightLegFront[2]];
    rightLegBox.faceVertexUvs[0][10] = [rightLegBack[3], rightLegBack[0], rightLegBack[2]];
    rightLegBox.faceVertexUvs[0][11] = [rightLegBack[0], rightLegBack[1], rightLegBack[2]];
    rightLegMesh = new THREE.Mesh(rightLegBox, material);
    rightLegMesh.name = "rightLeg"
    rightLegMesh.position.y = -22;
    rightLegMesh.position.x = -2;
    scene.add(rightLegMesh);

    // Left Leg Parts
    var leftLegTop = [
        new THREE.Vector2(0.3125, 0.1875),
        new THREE.Vector2(0.375, 0.1875),
        new THREE.Vector2(0.375, 0.25),
        new THREE.Vector2(0.3125, 0.25),
    ];
    var leftLegBottom = [
        new THREE.Vector2(0.375, 0.1875),
        new THREE.Vector2(0.4375, 0.1875),
        new THREE.Vector2(0.4375, 0.25),
        new THREE.Vector2(0.375, 0.25)
    ];
    var leftLegLeft = [
        new THREE.Vector2(0.25, 0),
        new THREE.Vector2(0.3125, 0),
        new THREE.Vector2(0.3125, 0.1875),
        new THREE.Vector2(0.25, 0.1875)
    ];
    var leftLegFront = [
        new THREE.Vector2(0.3125, 0),
        new THREE.Vector2(0.375, 0),
        new THREE.Vector2(0.375, 0.1875),
        new THREE.Vector2(0.3125, 0.1875)
    ];
    var leftLegRight = [
        new THREE.Vector2(0.375, 0),
        new THREE.Vector2(0.4375, 0),
        new THREE.Vector2(0.4375, 0.1875),
        new THREE.Vector2(0.375, 0.1875)
    ];
    var leftLegBack = [
        new THREE.Vector2(0.4375, 0),
        new THREE.Vector2(0.5, 0),
        new THREE.Vector2(0.5, 0.1875),
        new THREE.Vector2(0.4375, 0.1875)
    ];
    leftLegBox = new THREE.BoxGeometry(4, 12, 4, 0, 0, 0);
    leftLegBox.faceVertexUvs[0] = [];
    leftLegBox.faceVertexUvs[0][0] = [leftLegRight[3], leftLegRight[0], leftLegRight[2]];
    leftLegBox.faceVertexUvs[0][1] = [leftLegRight[0], leftLegRight[1], leftLegRight[2]];
    leftLegBox.faceVertexUvs[0][2] = [leftLegLeft[3], leftLegLeft[0], leftLegLeft[2]];
    leftLegBox.faceVertexUvs[0][3] = [leftLegLeft[0], leftLegLeft[1], leftLegLeft[2]];
    leftLegBox.faceVertexUvs[0][4] = [leftLegTop[3], leftLegTop[0], leftLegTop[2]];
    leftLegBox.faceVertexUvs[0][5] = [leftLegTop[0], leftLegTop[1], leftLegTop[2]];
    leftLegBox.faceVertexUvs[0][6] = [leftLegBottom[0], leftLegBottom[3], leftLegBottom[1]];
    leftLegBox.faceVertexUvs[0][7] = [leftLegBottom[3], leftLegBottom[2], leftLegBottom[1]];
    leftLegBox.faceVertexUvs[0][8] = [leftLegFront[3], leftLegFront[0], leftLegFront[2]];
    leftLegBox.faceVertexUvs[0][9] = [leftLegFront[0], leftLegFront[1], leftLegFront[2]];
    leftLegBox.faceVertexUvs[0][10] = [leftLegBack[3], leftLegBack[0], leftLegBack[2]];
    leftLegBox.faceVertexUvs[0][11] = [leftLegBack[0], leftLegBack[1], leftLegBack[2]];
    leftLegMesh = new THREE.Mesh(leftLegBox, material);
    leftLegMesh.name = "leftLeg";
    leftLegMesh.position.y = -22;
    leftLegMesh.position.x = 2;
    scene.add(leftLegMesh);

    // Head Overlay Parts
    var head2Top = [
        new THREE.Vector2(0.625, 0.875),
        new THREE.Vector2(0.75, 0.875),
        new THREE.Vector2(0.75, 1),
        new THREE.Vector2(0.625, 1)
    ];
    var head2Bottom = [
        new THREE.Vector2(0.75, 0.875),
        new THREE.Vector2(0.875, 0.875),
        new THREE.Vector2(0.875, 1),
        new THREE.Vector2(0.75, 1)
    ];
    var head2Left = [
        new THREE.Vector2(0.5, 0.75),
        new THREE.Vector2(0.625, 0.75),
        new THREE.Vector2(0.625, 0.875),
        new THREE.Vector2(0.5, 0.875)
    ];
    var head2Front = [
        new THREE.Vector2(0.625, 0.75),
        new THREE.Vector2(0.75, 0.75),
        new THREE.Vector2(0.75, 0.875),
        new THREE.Vector2(0.625, 0.875)
    ];
    var head2Right = [
        new THREE.Vector2(0.75, 0.75),
        new THREE.Vector2(0.875, 0.75),
        new THREE.Vector2(0.875, 0.875),
        new THREE.Vector2(0.75, 0.875)
    ];
    var head2Back = [
        new THREE.Vector2(0.875, 0.75),
        new THREE.Vector2(1, 0.75),
        new THREE.Vector2(1, 0.875),
        new THREE.Vector2(0.875, 0.875)
    ];
    head2Box = new THREE.BoxGeometry(9, 9, 9, 0, 0, 0);
    head2Box.faceVertexUvs[0] = [];
    head2Box.faceVertexUvs[0][0] = [head2Right[3], head2Right[0], head2Right[2]];
    head2Box.faceVertexUvs[0][1] = [head2Right[0], head2Right[1], head2Right[2]];
    head2Box.faceVertexUvs[0][2] = [head2Left[3], head2Left[0], head2Left[2]];
    head2Box.faceVertexUvs[0][3] = [head2Left[0], head2Left[1], head2Left[2]];
    head2Box.faceVertexUvs[0][4] = [head2Top[3], head2Top[0], head2Top[2]];
    head2Box.faceVertexUvs[0][5] = [head2Top[0], head2Top[1], head2Top[2]];
    head2Box.faceVertexUvs[0][6] = [head2Bottom[0], head2Bottom[3], head2Bottom[1]];
    head2Box.faceVertexUvs[0][7] = [head2Bottom[3], head2Bottom[2], head2Bottom[1]];
    head2Box.faceVertexUvs[0][8] = [head2Front[3], head2Front[0], head2Front[2]];
    head2Box.faceVertexUvs[0][9] = [head2Front[0], head2Front[1], head2Front[2]];
    head2Box.faceVertexUvs[0][10] = [head2Back[3], head2Back[0], head2Back[2]];
    head2Box.faceVertexUvs[0][11] = [head2Back[0], head2Back[1], head2Back[2]];
    head2Mesh = new THREE.Mesh(head2Box, material2);
    head2Mesh.name = "head2"
    scene.add(head2Mesh);

    // Body Overlay Parts
    var body2Top = [
        new THREE.Vector2(0.3125, 0.4375),
        new THREE.Vector2(0.4375, 0.4375),
        new THREE.Vector2(0.4375, 0.5),
        new THREE.Vector2(0.3125, 0.5)
    ];
    var body2Bottom = [
        new THREE.Vector2(0.4375, 0.4375),
        new THREE.Vector2(0.5625, 0.4375),
        new THREE.Vector2(0.5625, 0.5),
        new THREE.Vector2(0.4375, 0.5)
    ];
    var body2Left = [
        new THREE.Vector2(0.25, 0.25),
        new THREE.Vector2(0.3125, 0.25),
        new THREE.Vector2(0.3125, 0.4375),
        new THREE.Vector2(0.25, 0.4375)
    ];
    var body2Front = [
        new THREE.Vector2(0.3125, 0.25),
        new THREE.Vector2(0.4375, 0.25),
        new THREE.Vector2(0.4375, 0.4375),
        new THREE.Vector2(0.3125, 0.4375)
    ];
    var body2Right = [
        new THREE.Vector2(0.4375, 0.25),
        new THREE.Vector2(0.5, 0.25),
        new THREE.Vector2(0.5, 0.4375),
        new THREE.Vector2(0.4375, 0.4375)
    ];
    var body2Back = [
        new THREE.Vector2(0.5, 0.25),
        new THREE.Vector2(0.625, 0.25),
        new THREE.Vector2(0.625, 0.4375),
        new THREE.Vector2(0.5, 0.4375)
    ];
    body2Box = new THREE.BoxGeometry(9, 13.5, 4.5, 0, 0, 0);
    body2Box.faceVertexUvs[0] = [];
    body2Box.faceVertexUvs[0][0] = [body2Right[3], body2Right[0], body2Right[2]];
    body2Box.faceVertexUvs[0][1] = [body2Right[0], body2Right[1], body2Right[2]];
    body2Box.faceVertexUvs[0][2] = [body2Left[3], body2Left[0], body2Left[2]];
    body2Box.faceVertexUvs[0][3] = [body2Left[0], body2Left[1], body2Left[2]];
    body2Box.faceVertexUvs[0][4] = [body2Top[3], body2Top[0], body2Top[2]];
    body2Box.faceVertexUvs[0][5] = [body2Top[0], body2Top[1], body2Top[2]];
    body2Box.faceVertexUvs[0][6] = [body2Bottom[0], body2Bottom[3], body2Bottom[1]];
    body2Box.faceVertexUvs[0][7] = [body2Bottom[3], body2Bottom[2], body2Bottom[1]];
    body2Box.faceVertexUvs[0][8] = [body2Front[3], body2Front[0], body2Front[2]];
    body2Box.faceVertexUvs[0][9] = [body2Front[0], body2Front[1], body2Front[2]];
    body2Box.faceVertexUvs[0][10] = [body2Back[3], body2Back[0], body2Back[2]];
    body2Box.faceVertexUvs[0][11] = [body2Back[0], body2Back[1], body2Back[2]];
    body2Mesh = new THREE.Mesh(body2Box, material2);
    body2Mesh.name = "body2";
    body2Mesh.position.y = -10;
    scene.add(body2Mesh);

    // Right Arm Overlay Parts
    var rightArm2Top = [
        new THREE.Vector2(0.6875, 0.4375),
        new THREE.Vector2(0.75, 0.4375),
        new THREE.Vector2(0.75, 0.5),
        new THREE.Vector2(0.6875, 0.5),
    ];
    var rightArm2Bottom = [
        new THREE.Vector2(0.75, 0.4375),
        new THREE.Vector2(0.8125, 0.4375),
        new THREE.Vector2(0.8125, 0.5),
        new THREE.Vector2(0.75, 0.5)
    ];
    var rightArm2Left = [
        new THREE.Vector2(0.625, 0.25),
        new THREE.Vector2(0.6875, 0.25),
        new THREE.Vector2(0.6875, 0.4375),
        new THREE.Vector2(0.625, 0.4375)
    ];
    var rightArm2Front = [
        new THREE.Vector2(0.6875, 0.25),
        new THREE.Vector2(0.75, 0.25),
        new THREE.Vector2(0.75, 0.4375),
        new THREE.Vector2(0.6875, 0.4375)
    ];
    var rightArm2Right = [
        new THREE.Vector2(0.75, 0.25),
        new THREE.Vector2(0.8125, 0.25),
        new THREE.Vector2(0.8125, 0.4375),
        new THREE.Vector2(0.75, 0.4375)
    ];
    var rightArm2Back = [
        new THREE.Vector2(0.8125, 0.25),
        new THREE.Vector2(0.875, 0.25),
        new THREE.Vector2(0.875, 0.4375),
        new THREE.Vector2(0.8125, 0.4375)
    ];
    rightArm2Box = new THREE.BoxGeometry(4.5, 13.5, 4.5, 0, 0, 0);
    rightArm2Box.faceVertexUvs[0] = [];
    rightArm2Box.faceVertexUvs[0][0] = [rightArm2Right[3], rightArm2Right[0], rightArm2Right[2]];
    rightArm2Box.faceVertexUvs[0][1] = [rightArm2Right[0], rightArm2Right[1], rightArm2Right[2]];
    rightArm2Box.faceVertexUvs[0][2] = [rightArm2Left[3], rightArm2Left[0], rightArm2Left[2]];
    rightArm2Box.faceVertexUvs[0][3] = [rightArm2Left[0], rightArm2Left[1], rightArm2Left[2]];
    rightArm2Box.faceVertexUvs[0][4] = [rightArm2Top[3], rightArm2Top[0], rightArm2Top[2]];
    rightArm2Box.faceVertexUvs[0][5] = [rightArm2Top[0], rightArm2Top[1], rightArm2Top[2]];
    rightArm2Box.faceVertexUvs[0][6] = [rightArm2Bottom[0], rightArm2Bottom[3], rightArm2Bottom[1]];
    rightArm2Box.faceVertexUvs[0][7] = [rightArm2Bottom[3], rightArm2Bottom[2], rightArm2Bottom[1]];
    rightArm2Box.faceVertexUvs[0][8] = [rightArm2Front[3], rightArm2Front[0], rightArm2Front[2]];
    rightArm2Box.faceVertexUvs[0][9] = [rightArm2Front[0], rightArm2Front[1], rightArm2Front[2]];
    rightArm2Box.faceVertexUvs[0][10] = [rightArm2Back[3], rightArm2Back[0], rightArm2Back[2]];
    rightArm2Box.faceVertexUvs[0][11] = [rightArm2Back[0], rightArm2Back[1], rightArm2Back[2]];
    rightArm2Mesh = new THREE.Mesh(rightArm2Box, material2);
    rightArm2Mesh.name = "rightArm2";
    rightArm2Mesh.position.y = -10;
    rightArm2Mesh.position.x = -6;
    scene.add(rightArm2Mesh);

    // Left Arm Overlay Parts
    var leftArm2Top = [
        new THREE.Vector2(0.8125, 0.1875),
        new THREE.Vector2(0.875, 0.1875),
        new THREE.Vector2(0.875, 0.25),
        new THREE.Vector2(0.8125, 0.25),
    ];
    var leftArm2Bottom = [
        new THREE.Vector2(0.875, 0.1875),
        new THREE.Vector2(0.9375, 0.1875),
        new THREE.Vector2(0.9375, 0.25),
        new THREE.Vector2(0.875, 0.25)
    ];
    var leftArm2Left = [
        new THREE.Vector2(0.75, 0),
        new THREE.Vector2(0.8125, 0),
        new THREE.Vector2(0.8125, 0.1875),
        new THREE.Vector2(0.75, 0.1875)
    ];
    var leftArm2Front = [
        new THREE.Vector2(0.8125, 0),
        new THREE.Vector2(0.875, 0),
        new THREE.Vector2(0.875, 0.1875),
        new THREE.Vector2(0.8125, 0.1875)
    ];
    var leftArm2Right = [
        new THREE.Vector2(0.875, 0),
        new THREE.Vector2(0.9375, 0),
        new THREE.Vector2(0.9375, 0.1875),
        new THREE.Vector2(0.875, 0.1875)
    ];
    var leftArm2Back = [
        new THREE.Vector2(0.9375, 0),
        new THREE.Vector2(1, 0),
        new THREE.Vector2(1, 0.1875),
        new THREE.Vector2(0.9375, 0.1875)
    ];
    leftArm2Box = new THREE.BoxGeometry(4.5, 13.5, 4.5, 0, 0, 0);
    leftArm2Box.faceVertexUvs[0] = [];
    leftArm2Box.faceVertexUvs[0][0] = [leftArm2Right[3], leftArm2Right[0], leftArm2Right[2]];
    leftArm2Box.faceVertexUvs[0][1] = [leftArm2Right[0], leftArm2Right[1], leftArm2Right[2]];
    leftArm2Box.faceVertexUvs[0][2] = [leftArm2Left[3], leftArm2Left[0], leftArm2Left[2]];
    leftArm2Box.faceVertexUvs[0][3] = [leftArm2Left[0], leftArm2Left[1], leftArm2Left[2]];
    leftArm2Box.faceVertexUvs[0][4] = [leftArm2Top[3], leftArm2Top[0], leftArm2Top[2]];
    leftArm2Box.faceVertexUvs[0][5] = [leftArm2Top[0], leftArm2Top[1], leftArm2Top[2]];
    leftArm2Box.faceVertexUvs[0][6] = [leftArm2Bottom[0], leftArm2Bottom[3], leftArm2Bottom[1]];
    leftArm2Box.faceVertexUvs[0][7] = [leftArm2Bottom[3], leftArm2Bottom[2], leftArm2Bottom[1]];
    leftArm2Box.faceVertexUvs[0][8] = [leftArm2Front[3], leftArm2Front[0], leftArm2Front[2]];
    leftArm2Box.faceVertexUvs[0][9] = [leftArm2Front[0], leftArm2Front[1], leftArm2Front[2]];
    leftArm2Box.faceVertexUvs[0][10] = [leftArm2Back[3], leftArm2Back[0], leftArm2Back[2]];
    leftArm2Box.faceVertexUvs[0][11] = [leftArm2Back[0], leftArm2Back[1], leftArm2Back[2]];
    leftArm2Mesh = new THREE.Mesh(leftArm2Box, material2);
    leftArm2Mesh.name = "leftArm2";
    leftArm2Mesh.position.y = -10;
    leftArm2Mesh.position.x = 6;
    scene.add(leftArm2Mesh);

    // Right Leg Overlay Parts
    var rightLeg2Top = [
        new THREE.Vector2(0.0625, 0.4375),
        new THREE.Vector2(0.125, 0.4375),
        new THREE.Vector2(0.125, 0.5),
        new THREE.Vector2(0.0625, 0.5),
    ];
    var rightLeg2Bottom = [
        new THREE.Vector2(0.125, 0.4375),
        new THREE.Vector2(0.1875, 0.4375),
        new THREE.Vector2(0.1875, 0.5),
        new THREE.Vector2(0.125, 0.5)
    ];
    var rightLeg2Left = [
        new THREE.Vector2(0, 0.25),
        new THREE.Vector2(0.0625, 0.25),
        new THREE.Vector2(0.0625, 0.4375),
        new THREE.Vector2(0, 0.4375)
    ];
    var rightLeg2Front = [
        new THREE.Vector2(0.0625, 0.25),
        new THREE.Vector2(0.125, 0.25),
        new THREE.Vector2(0.125, 0.4375),
        new THREE.Vector2(0.0625, 0.4375)
    ];
    var rightLeg2Right = [
        new THREE.Vector2(0.125, 0.25),
        new THREE.Vector2(0.1875, 0.25),
        new THREE.Vector2(0.1875, 0.4375),
        new THREE.Vector2(0.125, 0.4375)
    ];
    var rightLeg2Back = [
        new THREE.Vector2(0.1875, 0.25),
        new THREE.Vector2(0.25, 0.25),
        new THREE.Vector2(0.25, 0.4375),
        new THREE.Vector2(0.1875, 0.4375)
    ];
    rightLeg2Box = new THREE.BoxGeometry(4.5, 13.5, 4.5, 0, 0, 0);
    rightLeg2Box.faceVertexUvs[0] = [];
    rightLeg2Box.faceVertexUvs[0][0] = [rightLeg2Right[3], rightLeg2Right[0], rightLeg2Right[2]];
    rightLeg2Box.faceVertexUvs[0][1] = [rightLeg2Right[0], rightLeg2Right[1], rightLeg2Right[2]];
    rightLeg2Box.faceVertexUvs[0][2] = [rightLeg2Left[3], rightLeg2Left[0], rightLeg2Left[2]];
    rightLeg2Box.faceVertexUvs[0][3] = [rightLeg2Left[0], rightLeg2Left[1], rightLeg2Left[2]];
    rightLeg2Box.faceVertexUvs[0][4] = [rightLeg2Top[3], rightLeg2Top[0], rightLeg2Top[2]];
    rightLeg2Box.faceVertexUvs[0][5] = [rightLeg2Top[0], rightLeg2Top[1], rightLeg2Top[2]];
    rightLeg2Box.faceVertexUvs[0][6] = [rightLeg2Bottom[0], rightLeg2Bottom[3], rightLeg2Bottom[1]];
    rightLeg2Box.faceVertexUvs[0][7] = [rightLeg2Bottom[3], rightLeg2Bottom[2], rightLeg2Bottom[1]];
    rightLeg2Box.faceVertexUvs[0][8] = [rightLeg2Front[3], rightLeg2Front[0], rightLeg2Front[2]];
    rightLeg2Box.faceVertexUvs[0][9] = [rightLeg2Front[0], rightLeg2Front[1], rightLeg2Front[2]];
    rightLeg2Box.faceVertexUvs[0][10] = [rightLeg2Back[3], rightLeg2Back[0], rightLeg2Back[2]];
    rightLeg2Box.faceVertexUvs[0][11] = [rightLeg2Back[0], rightLeg2Back[1], rightLeg2Back[2]];
    rightLeg2Mesh = new THREE.Mesh(rightLeg2Box, material2);
    rightLeg2Mesh.name = "rightLeg2"
    rightLeg2Mesh.position.y = -22;
    rightLeg2Mesh.position.x = -2;
    scene.add(rightLeg2Mesh);

    // Left Leg Overlay Parts
    var leftLeg2Top = [
        new THREE.Vector2(0.0625, 0.1875),
        new THREE.Vector2(0.125, 0.1875),
        new THREE.Vector2(0.125, 0.25),
        new THREE.Vector2(0.0625, 0.25),
    ];
    var leftLeg2Bottom = [
        new THREE.Vector2(0.125, 0.1875),
        new THREE.Vector2(0.1875, 0.1875),
        new THREE.Vector2(0.1875, 0.25),
        new THREE.Vector2(0.125, 0.25)
    ];
    var leftLeg2Left = [
        new THREE.Vector2(0, 0),
        new THREE.Vector2(0.0625, 0),
        new THREE.Vector2(0.0625, 0.1875),
        new THREE.Vector2(0, 0.1875)
    ];
    var leftLeg2Front = [
        new THREE.Vector2(0.0625, 0),
        new THREE.Vector2(0.125, 0),
        new THREE.Vector2(0.125, 0.1875),
        new THREE.Vector2(0.0625, 0.1875)
    ];
    var leftLeg2Right = [
        new THREE.Vector2(0.125, 0),
        new THREE.Vector2(0.1875, 0),
        new THREE.Vector2(0.1875, 0.1875),
        new THREE.Vector2(0.125, 0.1875)
    ];
    var leftLeg2Back = [
        new THREE.Vector2(0.1875, 0),
        new THREE.Vector2(0.25, 0),
        new THREE.Vector2(0.25, 0.1875),
        new THREE.Vector2(0.1875, 0.1875)
    ];
    var leftLeg2Box = new THREE.BoxGeometry(4.5, 13.5, 4.5, 0, 0, 0);
    leftLeg2Box.faceVertexUvs[0] = [];
    leftLeg2Box.faceVertexUvs[0][0] = [leftLeg2Right[3], leftLeg2Right[0], leftLeg2Right[2]];
    leftLeg2Box.faceVertexUvs[0][1] = [leftLeg2Right[0], leftLeg2Right[1], leftLeg2Right[2]];
    leftLeg2Box.faceVertexUvs[0][2] = [leftLeg2Left[3], leftLeg2Left[0], leftLeg2Left[2]];
    leftLeg2Box.faceVertexUvs[0][3] = [leftLeg2Left[0], leftLeg2Left[1], leftLeg2Left[2]];
    leftLeg2Box.faceVertexUvs[0][4] = [leftLeg2Top[3], leftLeg2Top[0], leftLeg2Top[2]];
    leftLeg2Box.faceVertexUvs[0][5] = [leftLeg2Top[0], leftLeg2Top[1], leftLeg2Top[2]];
    leftLeg2Box.faceVertexUvs[0][6] = [leftLeg2Bottom[0], leftLeg2Bottom[3], leftLeg2Bottom[1]];
    leftLeg2Box.faceVertexUvs[0][7] = [leftLeg2Bottom[3], leftLeg2Bottom[2], leftLeg2Bottom[1]];
    leftLeg2Box.faceVertexUvs[0][8] = [leftLeg2Front[3], leftLeg2Front[0], leftLeg2Front[2]];
    leftLeg2Box.faceVertexUvs[0][9] = [leftLeg2Front[0], leftLeg2Front[1], leftLeg2Front[2]];
    leftLeg2Box.faceVertexUvs[0][10] = [leftLeg2Back[3], leftLeg2Back[0], leftLeg2Back[2]];
    leftLeg2Box.faceVertexUvs[0][11] = [leftLeg2Back[0], leftLeg2Back[1], leftLeg2Back[2]];
    leftLeg2Mesh = new THREE.Mesh(leftLeg2Box, material2);
    leftLeg2Mesh.name = "leftLeg2";
    leftLeg2Mesh.position.y = -22;
    leftLeg2Mesh.position.x = 2;
    scene.add(leftLeg2Mesh);

    // Cape Parts
    var capeTop = [
        new THREE.Vector2(1/22, 21/17),
        new THREE.Vector2(11/22, 21/17),
        new THREE.Vector2(11/22, 22/17),
        new THREE.Vector2(1/22, 22/17),
    ];
    var capeBottom = [
        new THREE.Vector2(11/22, 16/17),
        new THREE.Vector2(21/22, 16/17),
        new THREE.Vector2(21/22, 16/17),
        new THREE.Vector2(11/22, 16/17)
    ];
    var capeLeft = [
        new THREE.Vector2(11/22, 0/17),
        new THREE.Vector2(12/22, 0/17),
        new THREE.Vector2(12/22, 16/17),
        new THREE.Vector2(11/22, 16/17)
    ];
    var capeFront = [
        new THREE.Vector2(12/22, 0/17),
        new THREE.Vector2(1, 0/17),
        new THREE.Vector2(1, 16/17),
        new THREE.Vector2(12/22, 16/17)
    ];
    var capeRight = [
        new THREE.Vector2(0, 0/17),
        new THREE.Vector2(1/22, 0/17),
        new THREE.Vector2(1/22, 16/17),
        new THREE.Vector2(0, 16/17)
    ];
    var capeBack = [
        new THREE.Vector2(1/22, 0/17),
        new THREE.Vector2(11/22, 0/17),
        new THREE.Vector2(11/22, 16/17),
        new THREE.Vector2(1/22, 16/17)
    ];
    var capeBox = new THREE.BoxGeometry(10, 16, 1, 0, 0, 0);
    capeBox.faceVertexUvs[0] = [];
    capeBox.faceVertexUvs[0][0] = [capeRight[3], capeRight[0], capeRight[2]];
    capeBox.faceVertexUvs[0][1] = [capeRight[0], capeRight[1], capeRight[2]];
    capeBox.faceVertexUvs[0][2] = [capeLeft[3], capeLeft[0], capeLeft[2]];
    capeBox.faceVertexUvs[0][3] = [capeLeft[0], capeLeft[1], capeLeft[2]];
    capeBox.faceVertexUvs[0][4] = [capeTop[3], capeTop[0], capeTop[2]];
    capeBox.faceVertexUvs[0][5] = [capeTop[0], capeTop[1], capeTop[2]];
    capeBox.faceVertexUvs[0][6] = [capeBottom[0], capeBottom[3], capeBottom[1]];
    capeBox.faceVertexUvs[0][7] = [capeBottom[3], capeBottom[2], capeBottom[1]];
    capeBox.faceVertexUvs[0][8] = [capeFront[3], capeFront[0], capeFront[2]];
    capeBox.faceVertexUvs[0][9] = [capeFront[0], capeFront[1], capeFront[2]];
    capeBox.faceVertexUvs[0][10] = [capeBack[3], capeBack[0], capeBack[2]];
    capeBox.faceVertexUvs[0][11] = [capeBack[0], capeBack[1], capeBack[2]];
    capeMesh = new THREE.Mesh(capeBox, material3);
    capeMesh.name = "cape";
    capeMesh.position.x = 0;
    scene.add(capeMesh);

    // Add to page
    container = document.getElementById('skinpreview');

    renderer = new THREE.WebGLRenderer({alpha: true});
    renderer.setSize(600, 350);

    displayCanvas = renderer.domElement;
    displayCanvas.addEventListener('mousedown', function(e) {
        e.preventDefault();
        originMouseX = e.pageX - displayCanvas.offsetLeft;
        originMouseY = e.pageY - displayCanvas.offsetTop;
        isMouseDown = true;
    }, false);
    window.addEventListener('mouseup', function(e) {
        isMouseDown = false;
    }, false);
    window.addEventListener('mousemove', onMouseMove, false);
    displayCanvas.addEventListener('mouseout', function(e) {
        if(!allowFullScreenDrag)
            isMouseDown = false;
    }, false);

    var canvas3d = renderer.domElement;
    canvas3d.setAttribute('id', 'canvas3d');
    canvas3d.style = '';
    if (preview_type == "3d") container.appendChild(canvas3d);
}

Element.prototype.setSize = function (w, h) {
    this.style.width = w + "px";
    this.style.height = h + "px";
    return this;
};

function Animate() {
    scene.getObjectByName("cape", false).visible = capeLoaded;

    requestAnimationFrame(Animate);

    if (!isMouseDown) {
        alpha1 += Math.PI / 360 * speed;

        if (alpha2 < 0) {
            alpha2 += Math.PI / 360;
            if (alpha2 > 0) alpha2=0;
        } else if (alpha2 > 0){
            alpha2 -= Math.PI / 360;
            if (alpha2<0) alpha2=0;
        }
    } else {
        alpha1 += (-deltaMouseX/(window.innerWidth - sidebarWidth)/(radius*2)*1000);
        deltaMouseX = 0;

        alpha2 += deltaMouseY / window.innerHeight * 5;
        deltaMouseY = 0;
        if (alpha2 > (Math.PI / 180*40)) {
            alpha2 = Math.PI / 180*40;
        }
        if (alpha2 < (-Math.PI / 180*30)) {
            alpha2 = -Math.PI / 180*30;
        }
    }

    alpha3 += Math.PI / 180*0.5*speed;

    camera.position.y = radius*Math.sin(alpha2);
    camera.position.z = radius*Math.cos(alpha1);
    camera.position.x = radius*Math.sin(alpha1);
    camera.position.setLength(30);
    camera.lookAt(new THREE.Vector3(0, -12, 0));

    //Leg Swing
    leftLeg2Mesh.rotation.x = leftLegMesh.rotation.x = Math.cos(alpha3*4);
    leftLeg2Mesh.position.z = leftLegMesh.position.z = 0 - 6*Math.sin(leftLegMesh.rotation.x);
    leftLeg2Mesh.position.y = leftLegMesh.position.y = -16 - 6*Math.abs(Math.cos(leftLegMesh.rotation.x));
    rightLeg2Mesh.rotation.x = rightLegMesh.rotation.x = Math.cos(alpha3*4 + (Math.PI));
    rightLeg2Mesh.position.z = rightLegMesh.position.z = 0 - 6*Math.sin(rightLegMesh.rotation.x);
    rightLeg2Mesh.position.y = rightLegMesh.position.y = -16 - 6*Math.abs(Math.cos(rightLegMesh.rotation.x));

    //Arm Swing
    leftArm2Mesh.rotation.x = leftArmMesh.rotation.x = Math.cos(alpha3*4 + (Math.PI));
    leftArm2Mesh.position.z = leftArmMesh.position.z = 0 - 6*Math.sin(leftArmMesh.rotation.x);
    leftArm2Mesh.position.y = leftArmMesh.position.y = -4 - 6*Math.abs(Math.cos(leftArmMesh.rotation.x));
    rightArm2Mesh.rotation.x = rightArmMesh.rotation.x = Math.cos(alpha3*4);
    rightArm2Mesh.position.z = rightArmMesh.position.z = 0 - 6*Math.sin(rightArmMesh.rotation.x);
    rightArm2Mesh.position.y = rightArmMesh.position.y = -4 - 6*Math.abs(Math.cos(rightArmMesh.rotation.x));

    //Cape Swing
    if(capeLoaded){
        capeMesh.rotation.x = Math.abs(Math.cos(alpha3*2+(Math.PI/4))/4)+Math.PI / 180*10;
        capeMesh.position.z = -2.2 - 8*Math.abs(Math.sin(capeMesh.rotation.x));
        capeMesh.position.y = -4 - 8*Math.abs(Math.cos(capeMesh.rotation.x));
    }

    renderer.render(scene, camera);
}
