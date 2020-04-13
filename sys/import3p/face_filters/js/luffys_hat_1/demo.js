"use strict";

// SETTINGS of this demo :
var SETTINGS = {
    rotationOffsetX: 0, // negative -> look upper. in radians
    cameraFOV: 40,      // in degrees, 3D camera FOV
    pivotOffsetYZ: [0.0, 0.2], // XYZ of the distance between the center of the cube and the pivot
    detectionThreshold: 0.75, // sensibility, between 0 and 1. Less -> more sensitive
    detectionHysteresis: 0.05,
    scale: 1 // scale of the 3D cube
};


// some globalz :
var THREEVIDEOTEXTURE;
var THREERENDERER;
var THREEFACEOBJ3D;
var THREEFACEOBJ3DPIVOTED;
var THREESCENE;
var THREECAMERA;
var ISDETECTED = false;



// callback : launched if a face is detected or lost. TODO : add a cool particle effect WoW !
function detect_callback(isDetected) {
    if (isDetected) {
        //console.log('INFO in detect_callback() : DETECTED');
    } else {
        //console.log('INFO in detect_callback() : LOST');
    }
}


// build the 3D. called once when Jeeliz Face Filter is OK
function init_threeScene(spec) {
    // INIT THE THREE.JS context
    THREERENDERER = new THREE.WebGLRenderer({
        context: spec.GL,
        canvas: spec.canvasElement
    });



    // COMPOSITE OBJECT WHICH WILL FOLLOW THE HEAD
    // in fact we create 2 objects to be able to shift the pivot point
    THREEFACEOBJ3D = new THREE.Object3D();
    THREEFACEOBJ3D.frustumCulled = false;
    THREEFACEOBJ3DPIVOTED = new THREE.Object3D();
    THREEFACEOBJ3DPIVOTED.frustumCulled = false;
    THREEFACEOBJ3DPIVOTED.position.set(0, -SETTINGS.pivotOffsetYZ[0], -SETTINGS.pivotOffsetYZ[1]);
    THREEFACEOBJ3DPIVOTED.scale.set(SETTINGS.scale, SETTINGS.scale, SETTINGS.scale);
    THREEFACEOBJ3D.add(THREEFACEOBJ3DPIVOTED);

    // CREATE A CUBE
    const cubeGeometry = new THREE.BoxGeometry(1, 1, 1);
    const cubeMaterial = new THREE.MeshNormalMaterial();
    const threeCube = new THREE.Mesh(cubeGeometry, cubeMaterial);
    threeCube.frustumCulled = false;
    // THREEFACEOBJ3DPIVOTED.add(threeCube);


    // Create the JSONLoader for our hat
    const loader = new THREE.BufferGeometryLoader();
    // Load our cool hat
    loader.load(
        './sys/import3p/face_filters/js/luffys_hat_1/models/luffys_hat.json',
        function (geometry, materials) {
            // we create our Hat mesh
            const mat = new THREE.MeshBasicMaterial({
                map: new THREE.TextureLoader().load("./sys/import3p/face_filters/js/luffys_hat_1/models/Texture.jpg")
            });
            const hatMesh = new THREE.Mesh(geometry, mat);

            hatMesh.scale.multiplyScalar(1.2);
            hatMesh.rotation.set(0, -40, 0);
            hatMesh.position.set(0.0, 0.6, 0.0);
            hatMesh.frustumCulled = false;
            hatMesh.side = THREE.DoubleSide;

            THREEFACEOBJ3DPIVOTED.add(hatMesh);
        }
    )

   

    // CREATE THE SCENE
    THREESCENE = new THREE.Scene();
    THREESCENE.add(THREEFACEOBJ3D);

    // CREATE LIGHT
    const ambientLight = new THREE.AmbientLight(0XFFFFFF, 0.8);
    THREESCENE.add(ambientLight);


    //init video texture with red
    THREEVIDEOTEXTURE = new THREE.DataTexture(new Uint8Array([255,0,0]), 1, 1, THREE.RGBFormat);
    THREEVIDEOTEXTURE.needsUpdate = true;


    //CREATE THE VIDEO BACKGROUND
    const videoMaterial = new THREE.RawShaderMaterial({
        depthWrite: false,
        depthTest: false,
        vertexShader: "attribute vec2 position;\n\
            varying vec2 vUV;\n\
            void main(void){\n\
                gl_Position=vec4(position, 0., 1.);\n\
                vUV=0.5+0.5*position;\n\
            }",
        fragmentShader: "precision lowp float;\n\
            uniform sampler2D samplerVideo;\n\
            varying vec2 vUV;\n\
            void main(void){\n\
                gl_FragColor=texture2D(samplerVideo, vUV);\n\
            }",
         uniforms: {
            samplerVideo: { value: THREEVIDEOTEXTURE }
         }
    });
    const videoGeometry = new THREE.BufferGeometry();
    const videoScreenCorners = new Float32Array([-1,-1,   1,-1,   1,1,   -1,1]);
    videoGeometry.addAttribute('position', new THREE.BufferAttribute( videoScreenCorners, 2));
    videoGeometry.setIndex(new THREE.BufferAttribute(new Uint16Array([0,1,2, 0,2,3]), 1));
    const videoMesh = new THREE.Mesh(videoGeometry, videoMaterial);
    videoMesh.onAfterRender = function () {
        // replace THREEVIDEOTEXTURE.__webglTexture by the real video texture
        THREERENDERER.properties.update(THREEVIDEOTEXTURE, '__webglTexture', spec.videoTexture);
        THREEVIDEOTEXTURE.magFilter = THREE.LinearFilter;
        THREEVIDEOTEXTURE.minFilter = THREE.LinearFilter;
        delete(videoMesh.onAfterRender);
    };
    videoMesh.renderOrder = -1000; // render first
    videoMesh.frustumCulled = false;
    THREESCENE.add(videoMesh);




    // CREATE THE CAMERA
    const aspecRatio = spec.canvasElement.width / spec.canvasElement.height;
    THREECAMERA = new THREE.PerspectiveCamera(SETTINGS.cameraFOV, aspecRatio, 0.1, 100);
} // end init_threeScene()

//launched by body.onload() :
function main(){
    JeelizResizer.size_canvas({
        canvasId: 'jeeFaceFilterCanvas',
        callback: function(isError, bestVideoSettings){
            init_faceFilter(bestVideoSettings);
        }
    })
} //end main()

function init_faceFilter(videoSettings){
    JEEFACEFILTERAPI.init({
        canvasId: 'jeeFaceFilterCanvas',
        NNCpath: './sys/import3p/face_filters/js/includes/', // root of NNC.json file
        videoSettings: videoSettings,
        callbackReady: function (errCode, spec) {
            if (errCode) {
                console.log('AN ERROR HAPPENED. SORRY BRO :( . ERR =', errCode);
                return;
            }

            console.log('INFO : JEEFACEFILTERAPI IS READY');
            init_threeScene(spec);
        }, // end callbackReady()

        // called at each render iteration (drawing loop)
        callbackTrack: function(detectState) {
            if (ISDETECTED && detectState.detected < SETTINGS.detectionThreshold-SETTINGS.detectionHysteresis) {
                // DETECTION LOST
                detect_callback(false);
                ISDETECTED = false;
            } else if (!ISDETECTED && detectState.detected > SETTINGS.detectionThreshold+SETTINGS.detectionHysteresis) {
                // FACE DETECTED
                detect_callback(true);
                ISDETECTED = true;
            }

            if (ISDETECTED) {
                // move the cube in order to fit the head
                const tanFOV = Math.tan(THREECAMERA.aspect * THREECAMERA.fov * Math.PI / 360); // tan(FOV/2), in radians
                const W = detectState.s;  // relative width of the detection window (1-> whole width of the detection window)
                const D = 1 / (2 * W * tanFOV); // distance between the front face of the cube and the camera
                
                // coords in 2D of the center of the detection window in the viewport :
                const xv = detectState.x;
                const yv = detectState.y;
                
                // coords in 3D of the center of the cube (in the view coordinates system)
                const z = -D-0.5;   // minus because view coordinate system Z goes backward. -0.5 because z is the coord of the center of the cube (not the front face)
                const x = xv*D*tanFOV;
                const y = (yv*D*tanFOV/THREECAMERA.aspect);

                // move and rotate the cube
                THREEFACEOBJ3D.position.set(x,y+SETTINGS.pivotOffsetYZ[0],z+SETTINGS.pivotOffsetYZ[1]);
                THREEFACEOBJ3D.rotation.set(detectState.rx+SETTINGS.rotationOffsetX, detectState.ry, detectState.rz, "XYZ");
            }

            // reinitialize the state of THREE.JS because JEEFACEFILTER have changed stuffs
            THREERENDERER.state.reset();

            // trigger the render of the THREE.JS SCENE
            THREERENDERER.render(THREESCENE, THREECAMERA);
        } // end callbackTrack()
    }); // end JEEFACEFILTERAPI.init call
} // end main()

