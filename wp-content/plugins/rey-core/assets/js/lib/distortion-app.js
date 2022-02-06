"use strict";var ReyDistortionApp=function(t){this.sliderImages=[],this.commonAngle=Math.PI/4,this.options=jQuery.extend({$el:{},$images:{},imgWidth:1e3,imgHeight:1e3,effect:0,intensity1:.8,intensity2:.8,vertical:!1,nextImage:0},t),this.init=function(){var t=this;this.getAngle(),this.renderer=new THREE.WebGLRenderer({antialias:!1}),this.renderer.setClearColor(16777215,0),this.renderer.setSize(this.options.imgWidth,this.options.imgHeight),this.loader=new THREE.TextureLoader,this.loader.crossOrigin="anonymous",jQuery.each(this.options.$images,(function(e,i){var s=t.loader.load(i.url[0]+"?v="+Date.now(),(function(i){e===t.options.$images.length-1&&t.options.$el.trigger("rey:distortion-app:init")}));s.magFilter=s.minFilter=THREE.LinearFilter,s.anisotropy=t.renderer.capabilities.getMaxAnisotropy(),t.sliderImages.push(s)})),this.scene=new THREE.Scene,this.scene.background=new THREE.Color(2303786),this.camera=new THREE.OrthographicCamera(this.options.imgWidth/-2,this.options.imgWidth/2,this.options.imgHeight/2,this.options.imgHeight/-2,1,1e3),this.camera.position.z=1,this.disp=this.loader.load(this.options.effect,this.renderer.render(this.scene,this.camera)),this.disp.magFilter=this.disp.minFilter=THREE.LinearFilter,this.disp.anisotropy=this.renderer.capabilities.getMaxAnisotropy(),""===this.options.effect&&(this.disp=this.sliderImages[1]),this.mat=new THREE.ShaderMaterial({uniforms:{dispFactor:{type:"f",value:0},currentImage:{type:"t",value:this.sliderImages[0]},nextImage:{type:"t",value:this.sliderImages[this.options.nextImage]},intensity1:{type:"f",value:this.options.intensity1},intensity2:{type:"f",value:this.options.intensity2},angle1:{type:"f",value:this.angle1},angle2:{type:"f",value:this.angle2},disp:{type:"t",value:this.disp},res:{type:"vec4",value:new THREE.Vector4(this.options.imgWidth,this.options.imgHeight,1,1)},dpr:{type:"f",value:window.devicePixelRatio}},vertexShader:this.vertex(),fragmentShader:this.fragment(),transparent:!0,opacity:1}),this.geometry=new THREE.PlaneBufferGeometry(this.options.imgWidth,this.options.imgHeight,1),this.object=new THREE.Mesh(this.geometry,this.mat),this.object.position.set(0,0,0),this.scene.add(this.object);return function e(){requestAnimationFrame(e),t.renderer.render(t.scene,t.camera)}(),this},this.animationStart=function(t,e){this.mat.uniforms.nextImage.value=this.sliderImages[t],this.mat.uniforms.nextImage.needsUpdate=!0,this.mat.uniforms.intensity1.value=this.options.intensity1*(e||1),this.mat.uniforms.intensity2.value=this.options.intensity2*(e||1),""===this.options.effect&&(this.mat.uniforms.disp.value=this.sliderImages[t])},this.animationEnd=function(t){this.mat.uniforms.currentImage.value=this.sliderImages[t],this.mat.uniforms.currentImage.needsUpdate=!0,this.mat.uniforms.dispFactor.value=0},this.getAngle=function(){this.angle1=this.commonAngle,this.angle2=3*-this.commonAngle,this.options.vertical&&(this.angle1=1*-this.commonAngle,this.angle2=3*this.commonAngle)},this.vertex=function(){return console.log("%c Hover effect by Robin Delaporte: https://github.com/robin-dela/hover-effect ","color: #bada55; font-size: 0.8rem"),"varying vec2 vUv;void main() {  vUv = uv;  gl_Position = projectionMatrix * modelViewMatrix * vec4( position, 1.0 );}"},this.fragment=function(){return"varying vec2 vUv; uniform sampler2D currentImage; uniform sampler2D nextImage; uniform sampler2D disp; uniform float dpr; uniform float dispFactor; uniform float angle1; uniform float angle2; uniform float intensity1; uniform float intensity2; uniform vec4 res; mat2 getRotM(float angle) { \tfloat s = sin(angle); \tfloat c = cos(angle); \treturn mat2(c, -s, s, c); } void main() { \tvec4 disp = texture2D(disp, vUv); \tvec2 dispVec = vec2(disp.r, disp.g); \tvec2 uv = 1.0 * gl_FragCoord.xy / (res.xy) ; \tvec2 myUV = (uv - vec2(0.5)) * res.zw + vec2(0.5); \tvec2 distortedPosition1 = myUV + getRotM(angle1) * dispVec * intensity1 * dispFactor; \tvec2 distortedPosition2 = myUV + getRotM(angle2) * dispVec * intensity2 * (1.0 - dispFactor); \tvec4 _currentImage = texture2D(currentImage, distortedPosition1); \tvec4 _nextImage = texture2D(nextImage, distortedPosition2); \tgl_FragColor = mix(_currentImage, _nextImage, dispFactor); } "},this.init()};