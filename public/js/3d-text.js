var height,
  width,
  container,
  scene,
  camera,
  renderer,
  particles = [],
  mouseVector = new THREE.Vector3(0, 0, 0),
  mousePos = new THREE.Vector3(0, 0, 0),
  cameraLookAt = new THREE.Vector3(0, 0, 0),
  cameraTarget = new THREE.Vector3(0, 0, 800),
  textCanvas,
  textCtx,
  textPixels = [],
  input;
var colors = ["#db4e98", "#24fdc3", "#0ee1e7", "#ef235c", "#0ee1e7"];

function initStage() {
  width = screen.width - 24;
  height = 420;
  container = document.getElementById("stage");
  // window.addEventListener('resize', resize);
  container.addEventListener("mousemove", mousemove);
}

function initScene() {
  scene = new THREE.Scene();
  renderer = new THREE.WebGLRenderer({
    alpha: true,
    antialias: true,
  });
  renderer.setPixelRatio(window.devicePixelRatio);
  renderer.setSize(width, height);
  container.appendChild(renderer.domElement);
}

function randomPos(vector) {
  var radius = width * 3;
  var centerX = 0;
  var centerY = 0;

  // ensure that p(r) ~ r instead of p(r) ~ constant
  var r = width + radius * Math.random();
  var angle = Math.random() * Math.PI * 2;

  // compute desired coordinates
  vector.x = centerX + r * Math.cos(angle);
  vector.y = centerY + r * Math.sin(angle);
}

function initCamera() {
  fieldOfView = 70;
  aspectRatio = width / height;
  nearPlane = 1;
  farPlane = 3000;
  camera = new THREE.PerspectiveCamera(
    fieldOfView,
    aspectRatio,
    nearPlane,
    farPlane
  );
  camera.position.z = 800;
  console.log(camera.position);
  console.log(cameraTarget);
}

function createLights() {
  shadowLight = new THREE.DirectionalLight(0xffffff, 2);
  shadowLight.position.set(20, 0, 10);
  shadowLight.castShadow = true;
  shadowLight.shadowDarkness = 0.01;
  scene.add(shadowLight);

  light = new THREE.DirectionalLight(0xffffff, 0.5);
  light.position.set(-20, 0, 20);
  scene.add(light);

  backLight = new THREE.DirectionalLight(0xffffff, 0.8);
  backLight.position.set(0, 0, -20);
  scene.add(backLight);
}

function Particle() {
  this.vx = Math.random() * 0.07;
  this.vy = Math.random() * 0.07;
}

Particle.prototype.init = function (i) {
  var particle = new THREE.Object3D();
  var geometryCore = new THREE.BoxGeometry(20, 20, 20);
  var materialCore = new THREE.MeshLambertMaterial({
    color: colors[i % colors.length],
    shading: THREE.FlatShading,
  });
  var box = new THREE.Mesh(geometryCore, materialCore);
  box.geometry.__dirtyVertices = true;
  box.geometry.dynamic = true;
  particle.targetPosition = new THREE.Vector3(
    (textPixels[i].x - width / 2) * 4,
    textPixels[i].y * 5,
    -10 * Math.random() + 20
  );
  particle.position.set(width * 0.5, height * 0.5, -10 * Math.random() + 20);
  randomPos(particle.position);

  for (var i = 0; i < box.geometry.vertices.length; i++) {
    box.geometry.vertices[i].x += -10 + Math.random() * 20;
    box.geometry.vertices[i].y += -10 + Math.random() * 20;
    box.geometry.vertices[i].z += -10 + Math.random() * 20;
  }

  particle.add(box);
  this.particle = particle;
};

Particle.prototype.updateRotation = function () {
  this.particle.rotation.x += this.vx;
  this.particle.rotation.y += this.vy;
};

Particle.prototype.updatePosition = function () {
  this.particle.position.lerp(this.particle.targetPosition, 0.02);
};

function render() {
  renderer.render(scene, camera);
}

function updateParticles() {
  for (var i = 0, l = particles.length; i < l; i++) {
    particles[i].updateRotation();
    particles[i].updatePosition();
  }
}

function setParticles() {
  for (var i = 0; i < textPixels.length; i++) {
    if (particles[i]) {
      particles[i].particle.targetPosition.x =
        (textPixels[i].x - width / 2) * 4;
      particles[i].particle.targetPosition.y = textPixels[i].y * 5;
      particles[i].particle.targetPosition.z = -10 * Math.random() + 20;
    } else {
      var p = new Particle();
      p.init(i);
      scene.add(p.particle);
      particles[i] = p;
    }
  }

  for (var i = textPixels.length; i < particles.length; i++) {
    randomPos(particles[i].particle.targetPosition);
  }
}

function initCanvas() {
  textCanvas = document.getElementById("text");
  stageCanvas = document.getElementById("stage");
  wavesTextCanvas = document.getElementById("waves-text");



  if(screen.width < 768){
    textCanvas.style.display = 'none'
    stageCanvas.style.display = 'none'

  }else{
    wavesTextCanvas.style.display = 'none'
    textCanvas.style.width = width + "px";
    textCanvas.style.height = height + "px";
    textCanvas.width = width;
    textCanvas.height = height;
    textCtx = textCanvas.getContext("2d");
    textCtx.font = "700 100px ShantellSans";
    textCtx.fillStyle = "#555";
  }
  
}

function initInput() {
  input = document.getElementById("input");
  input.addEventListener("keyup", updateText);
  input.value = "WAVES'23";
}

function updateText() {
  var fontSize = width / (input.value.length * 1.3);
  if (fontSize > 120) fontSize = 120;
  textCtx.font = "700 " + fontSize + "px ShantellSans";
  textCtx.clearRect(0, 0, width, 200);
  textCtx.textAlign = "center";
  textCtx.textBaseline = "middle";
  textCtx.fillText(input.value.toUpperCase(), width / 2, 50);

  var pix = textCtx.getImageData(0, 0, width, 200).data;
  textPixels = [];
  for (var i = pix.length; i >= 0; i -= 4) {
    if (pix[i] != 0) {
      var x = (i / 4) % width;
      var y = Math.floor(Math.floor(i / width) / 4);

      if (x && x % 6 == 0 && y && y % 6 == 0)
        textPixels.push({
          x: x,
          y: 200 - y + -120,
        });
    }
  }
  setParticles();
}

function animate() {
  requestAnimationFrame(animate);
  updateParticles();
  camera.position.lerp(cameraTarget, 0.2);
  camera.lookAt(cameraLookAt);
  render();
}

// function resize() {
//     width = 800;
//     height = 200;
//     camera.aspect = width / height;
//     camera.updateProjectionMatrix();
//     renderer.setSize(width, height);

//     textCanvas.style.width = width + 'px';
//     textCanvas.style.height = height + 'px';
//     textCanvas.width = width;
//     textCanvas.height = height;
//     updateText();
// }

function mousemove(e) {
  var x = e.pageX - width / 2;
  var y = e.pageY - height / 2;
  cameraTarget.x = x * -1;
  cameraTarget.y = y;
}

initStage();
initScene();
initCanvas();
initCamera();
createLights();
initInput();
animate();
updateText();
// setTimeout(function () {
//     updateText();
// }, 40);
