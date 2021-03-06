<?php include_once("index.html"); ?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<title>Document</title>
	<style>
		* {
			padding: 0;
			margin: 0;
			box-sizing: border-box;
		}

		.container {
			margin-top: 20px;
			margin-left: auto;
			margin-right: auto;
			max-width: 1170px;
			padding: 0 15px;
		}

		center {
			display: flex;
			flex-direction: column;
			align-items: center;
		}

		#number {
			margin: 15px 0;
		}

		#video {
			display: block;
			width: 100%;
			margin: 15px auto;
			color: #fff;
		}

		.video-wrap {
			position: relative;
			max-width: 550px;
			margin: 0 auto;
		}

		.video-wrap::before {
			content: '';
			position: absolute;
			z-index: 2;
			top: 0;
			left: 0;
			right: 0;
			height: 40%;
			width: 100%;
			background-color: rgba(0, 0, 0, 0.295);
		}

		.video-wrap::after {
			content: '';
			position: absolute;
			z-index: 2;
			bottom: 0;
			left: 0;
			right: 0;
			height: 40%;
			width: 100%;
			background-color: rgba(255, 0, 0, 0.24);
		}
	</style>
</head>

<body>


	<div class="container">
		<center>
			<button onclick="start()" style="height:70px; width:200px;">Start!</button>
			<input id="number" type="text" style="height:70px; width:200px;  font-size:20px;">
			<input id="series" type="text" style="height:70px; width:200px;  font-size:20px;">
		</center>



		<div class="video-wrap" style="visibility:hidden;">
			<video id="video" width="100%" autoplay></video>
		</div>

		<canvas id="preview"></canvas>

	</div>

	<script>
		"use strict"
		var canvas = document.createElement("canvas")
		var video = document.getElementById('video')
		var videoWrap = document.querySelector('.video-wrap')
		var preview = document.querySelector("#preview")
		var div = document.querySelector('#text')
		var loading = false
		var snapping = false
		var localStream

		var start = () => {


			navigator.mediaDevices.getUserMedia = navigator.mediaDevices.getUserMedia ||
				navigator.mediaDevices.webkitGetUserMedia ||
				navigator.mediaDevices.mozGetUserMedia;

			navigator.mediaDevices.getUserMedia({
				audio: false,
				video: {
					width: {
						min: 320,
						ideal: 1024
					},
					height: {
						min: 320,
						ideal: 768
					}
				}
			}).then(stream => {
				localStream = stream;
				snapping = true;
				video.srcObject = stream;
				videoWrap.style.visibility = "visible";
			}).catch(log);

		}




	
		var snap = () => {
        if (snapping) {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            canvas.getContext('2d').drawImage(video, 0, 0, video.videoWidth, video.videoHeight);
            preview.getContext('2d').drawImage(canvas, 0, video.videoHeight / 5, video.videoWidth, video.videoHeight - video.videoHeight / 2, 0, video.videoHeight / 4, video.videoWidth, video.videoHeight - video.videoHeight / 2);

            let data = preview.toDataURL().split(',')[1];

            const dataToSend = JSON.stringify({ "image": data });
            let dataReceived = "";
            if (!loading) {
                loading = true;

                fetch("https://mrz-proxy.herokuapp.com/process", {
                    method: "post",
                    headers: { "Content-Type": "application/json" },
                    body: dataToSend,
                    rejectUnauthorized: false,
                    requestCert: true,
                    agent: false
                }).then(resp => {
                    if (resp.status === 200) {
                        return resp.json()
                    } else {
                        console.log("Status: " + resp.status)
                        return Promise.reject("server")
                    }
                }).then(dataJson => {
                    if (!dataJson['error']) {
                        //if (dataJson['valid_score'] > 5) 
                        //document.getElementById("text").innerHTML = JSON.stringify(dataJson);
                        snapping = false;
                        video.style.visibility = "hidden";
                        document.getElementById("number").value = dataJson['number'];
                        document.getElementById("series").value = dataJson['personal_number'];
                        localStream.getTracks().forEach((track) => {
                            track.stop();
                        });
                    }
                    loading = false;
                })
                    .catch(err => {
                        loading = false;
                        if (err === "server") return
                        console.log(err)
                    })
            }
        }
    }

    setInterval(snap, 1000);

    var log = msg => div.innerHTML += "<br>" + msg;

	</script>






	<!-- <canvas id="canvas" width="640" height="480">
		<p>Ваш браузер не поддерживает рисование.</p>
	</canvas>
	
	<input id="photo" type="file" name="image" accept="image/*" capture>
	
	<script type="text/javascript" src="index.js"></script> -->


</body>

</html>