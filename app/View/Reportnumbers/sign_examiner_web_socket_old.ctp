
        <p id="status">Not connected</p>
        <br/>
				    <input id="SignBtn" name="SignBtn" type="button" value="Sign" onclick="getSignature();" style="background-color: #FF6600; width: 120; height: 40; color: white; font: bold 8pt verdana"/>
        <br/>
                    <p style=" font-weight:bold;">Pad Type:</p>
                    <p id="PadType_0"></p>
                    <p style=" font-weight:bold;">Serial Number:</p>
                    <p id="SerialNumber_0"></p>
                    <p style=" font-weight:bold;">Firmware Version:</p>
                    <p id="FirmwareVersion_0"></p>
        <ul id="log"></ul></p>
                    <canvas id="sigCanvas" ></canvas> 
                    <img id="Signature_0" alt="Signature 0" src="signo/White.png"/>
<textarea id="SignData_0" rows="3" cols="40" readonly="readonly" style="border-style:hidden; resize:none;"></textarea>
        <script type="text/javascript">

//			close_pad();
//			getSignature();
            

			var wsUri = "wss://local.signotecwebsocket.de:49494";

            var state = "#status";
            var log = "#log";
            var sigcanvas = document.getElementById("sigCanvas");
            var sigcanva = "#sigCanvas";

			$("SignData_0").val("");

            if (window.WebSocket === undefined) {
				$(state).text("sockets not supported");
				$(state).addClass("fail");
            }
			
            else {
                if (typeof String.prototype.startsWith != "function") {
                    String.prototype.startsWith = function (str) {
                        return this.indexOf(str) == 0;
                    };
                }
                websocket = new WebSocket(wsUri);
                websocket.onopen = function (evt) { onOpen(evt) };
                websocket.onclose = function (evt) { onClose(evt) };
                websocket.onerror = function (evt) { onError(evt) };
                websocket.onmessage = function (evt) { onMessage(evt) };
            }

            function onOpen(evt) {
//                state.className = "success";
//                state.innerHTML = "Connected to server " + wsUri;
 				$(state).text("Connected to server " + wsUri);
				$(state).addClass("success");
           }

            function onClose(evt) {
//                state.className = "fail";
//                state.innerHTML = "Not connected";
 				$(state).text("Not connected");
				$(state).addClass("fail");
            }

            function onError(evt) {
//                state.className = "fail";
//                state.innerHTML = "Communication error";
 				$(state).text("Communication error");
				$(state).addClass("fail");
            }

            function logMessage(msg) {
//                log.innerHTML = "<li>" + msg + "</li>";
// 				$(log).html("<li>" + msg + "</li>");
            }

			var websocketModes = {
				Default: 0,
				API:1
			};
			
			var websocketMode = websocketModes.Default;

			var padTypes = {
			    sigmaUSB: 1,
			    sigmaSerial: 2,
			    omegaUSB: 11,
			    omegaSerial: 12,
			    gammaUSB: 15,
			    gammaSerial: 16,
			    deltaUSB: 21,
			    deltaSerial: 22,
			    deltaIP: 23,
			    alphaUSB: 31,
			    alphaSerial: 32,
			    alphaIP: 33
			}
			var padType = 0;

			var searchStates = {
				setPadType: 0,
				search: 1,
				getInfo: 2,
				getVersion: 3
			};
			var searchState = searchStates.setPadType;
			
			var openStates = {
				openPad: 0,
				getDisplayWidth: 1,
				getDisplayHeight: 2,
				getResolution: 3
			};
			var openState = openStates.openPad;

			var preparationStates = {
				setBackgroundTarget: 0,
				setBackgroundImage: 1,
				setCancelButton: 2,
				setRetryButton: 3,
				setConfirmButton: 4,
				setSignRect: 5,
				setFieldName: 6,
				setCustomText: 7,
				setForegroundTarget: 8,
				switchBuffers: 9,
				startSignature:10
			};
			var preparationState = preparationStates.setBackgroundTarget;
			
			var cancelButton = -1;
			var retryButton = -1;
			var confirmButton = -1;
			var buttonDiff = 0;
			var buttonLeft = 0;
			var buttonTop = 0;
			var buttonSize = 0;
			var backgroundImage;
			var scaleFactorX = 1.0;
			var scaleFactorY = 1.0;
			
            function onMessage(evt) {
                var message = evt.data;
                logMessage(message);
				
                var obj = JSON.parse(message);
                if (obj.TOKEN_TYPE == "TOKEN_TYPE_SEND") {
                    if (obj.TOKEN_CMD == "TOKEN_CMD_SIGNATURE_CONFIRM") {
						// confirm signing process
                        signature_confirm();
                    }
                    else if (obj.TOKEN_CMD == "TOKEN_CMD_SIGNATURE_RETRY") {
						// restart signing process
                        signature_retry();
                    }
                    else if (obj.TOKEN_CMD == "TOKEN_CMD_SIGNATURE_CANCEL") {
						// cancel signing process
                        signature_cancel();
                    }
                    else if (obj.TOKEN_CMD == "TOKEN_CMD_SIGNATURE_POINT") {

                        var ctx = sigcanvas.getContext("2d");
//						var ctx = $(sigcanva).getContext('2d');

                        ctx.fillStyle = "#001eff";
                        ctx.strokeStyle = "#001eff";
                        ctx.lineWidth = 1.5;
                        ctx.lineCap = "round";

                        if (obj.TOKEN_PARAM_POINT.p == 0) {
                            drawStrokeStartPoint(ctx, obj.TOKEN_PARAM_POINT.x * scaleFactorX, obj.TOKEN_PARAM_POINT.y * scaleFactorY);
                        }
                        else {
                            drawStrokePoint(ctx, obj.TOKEN_PARAM_POINT.x * scaleFactorX, obj.TOKEN_PARAM_POINT.y * scaleFactorY);
                        }

                    }
                    else if (obj.TOKEN_CMD == "TOKEN_CMD_DISCONNECT") {
                        //the opened pad has been disconnected
                        disconnect();
                    }
					else if (obj.TOKEN_CMD == "TOKEN_CMD_API_SENSOR_HOT_SPOT_PRESSED") {
                        var button = obj.TOKEN_PARAM_HOTSPOT_ID;
						switch (button) {
							case cancelButton:
								// cancel signing process
								signature_cancel();
								break;
							case retryButton:
								// restart signing process
								signature_retry();
								break;
							case confirmButton:
								// confirm signing process
								signature_confirm();
								break;
							default:
								alert("unknown button id: " + button);
						}
                    }
                    else {
                        // do nothing
                    }
                }
                else if (obj.TOKEN_TYPE == "TOKEN_TYPE_RESPONSE") {
                    if ((obj.TOKEN_CMD_ORIGIN == "TOKEN_CMD_SIGNATURE_CONFIRM") || (obj.TOKEN_CMD_ORIGIN == "TOKEN_CMD_API_SIGNATURE_CONFIRM")) {
                        //check the return code
                        var ret = obj.TOKEN_PARAM_RETURN_CODE;
                        if (ret < 0) {
                            alert("Failed to confirm the signature. Reason: " + obj.TOKEN_PARAM_ERROR_DESCRIPTION);
                            close_pad();
                            return;
                        }

                        signature_sign_data();
                    }
                    else if (obj.TOKEN_CMD_ORIGIN == "TOKEN_CMD_SIGNATURE_SIGN_DATA") {

                        //check the return code
                        var ret = obj.TOKEN_PARAM_RETURN_CODE;
                        if (ret < 0) {
                            alert("Failed to get signature SignData. Reason: " + obj.TOKEN_PARAM_ERROR_DESCRIPTION);
                            close_pad();
                            return;
                        }

                        document.getElementById("SignData_0").value = obj.TOKEN_PARAM_SIGNATURE_SIGN_DATA;

                        signature_image();

                    }
                    else if (obj.TOKEN_CMD_ORIGIN == "TOKEN_CMD_SIGNATURE_IMAGE") {

                        //check the return code
                        var ret = obj.TOKEN_PARAM_RETURN_CODE;
                        if (ret < 0) {
                            alert("Failed to get signature image. Reason: " + obj.TOKEN_PARAM_ERROR_DESCRIPTION);
                            close_pad();
                            return;
                        }

                        document.getElementById("Signature_0").src = "data:image/png;base64," + obj.TOKEN_PARAM_FILE;

                        close_pad();

                    }
                    else if ((obj.TOKEN_CMD_ORIGIN == "TOKEN_CMD_SIGNATURE_RETRY") || (obj.TOKEN_CMD_ORIGIN == "TOKEN_CMD_API_SIGNATURE_RETRY")) {

                        //check the return code
                        var ret = obj.TOKEN_PARAM_RETURN_CODE;
                        if (ret < 0) {
                            alert("Failed to restart signature process. Reason: " + obj.TOKEN_PARAM_ERROR_DESCRIPTION);
                            close_pad();
                            return;
                        }

                        var ctx = sigcanvas.getContext("2d");
//						var ctx = $(sigcanva).getContext('2d');
                        ctx.clearRect(0, 0, sigcanvas.width, sigcanvas.height);

                    }
                    else if ((obj.TOKEN_CMD_ORIGIN == "TOKEN_CMD_SIGNATURE_CANCEL") || (obj.TOKEN_CMD_ORIGIN == "TOKEN_CMD_API_SIGNATURE_CANCEL")) {

                        //check the return code
                        var ret = obj.TOKEN_PARAM_RETURN_CODE;
                        if (ret < 0) {
                            alert("Failed to cancel signature process. Reason: " + obj.TOKEN_PARAM_ERROR_DESCRIPTION);
                            close_pad();
                            return;
                        }

                        var ctx = sigcanvas.getContext("2d");
                        ctx.clearRect(0, 0, sigcanvas.width, sigcanvas.height);

                        close_pad();

                    }
                    else if (obj.TOKEN_CMD_ORIGIN == "TOKEN_CMD_SEARCH_FOR_PADS") {
                        //check the return code
                        var ret = obj.TOKEN_PARAM_RETURN_CODE;
                        if (ret < 0) {
                            alert("The search for pads failed. Reason: " + obj.TOKEN_PARAM_ERROR_DESCRIPTION);
                            searchState = searchStates.setPadType;
                            openState = openStates.openPad;
                            preparationState = preparationStates.setBackgroundTarget;
                            return;
                        }

                        //check for connected pads
                        if (obj.TOKEN_PARAM_CONNECTED_PADS == null) {
                            alert("No connected pads have been found.");
                            searchState = searchStates.setPadType;
                            openState = openStates.openPad;
                            preparationState = preparationStates.setBackgroundTarget;
                            return;
                        }

                        //show the pads properties
                        document.getElementById("PadType_0").innerHTML = obj.TOKEN_PARAM_CONNECTED_PADS[0].TOKEN_PARAM_PAD_TYPE;
                        document.getElementById("SerialNumber_0").innerHTML = obj.TOKEN_PARAM_CONNECTED_PADS[0].TOKEN_PARAM_PAD_SERIAL_NUMBER;
                        document.getElementById("FirmwareVersion_0").innerHTML = obj.TOKEN_PARAM_CONNECTED_PADS[0].TOKEN_PARAM_PAD_FIRMWARE_VERSION;
                        
                        //try to open the connected pad
                        open_pad();
                    }
                    else if (obj.TOKEN_CMD_ORIGIN == "TOKEN_CMD_OPEN_PAD") {
                        //check the return code
                        var ret = obj.TOKEN_PARAM_RETURN_CODE;
                        if (ret < 0) {
                            alert("Failed to open pad. Reason: " + obj.TOKEN_PARAM_ERROR_DESCRIPTION);
                            searchState = searchStates.setPadType;
                            openState = openStates.openPad;
                            preparationState = preparationStates.setBackgroundTarget;
                            return;
                        }

						//set canvas size
                        sigcanvas.width = obj.TOKEN_PARAM_PAD_DISPLAY_WIDTH;
                        sigcanvas.height = obj.TOKEN_PARAM_PAD_DISPLAY_HEIGHT;

						//get scale factor from siganture resolution to canvas
						scaleFactorX = obj.TOKEN_PARAM_PAD_DISPLAY_WIDTH / obj.TOKEN_PARAM_PAD_X_RESOLUTION;
						scaleFactorY = obj.TOKEN_PARAM_PAD_DISPLAY_HEIGHT / obj.TOKEN_PARAM_PAD_Y_RESOLUTION;

                        //start the signature process
                        signature_start();
                    }
                    else if (obj.TOKEN_CMD_ORIGIN == "TOKEN_CMD_SIGNATURE_START") {

                        //check the return code
                        var ret = obj.TOKEN_PARAM_RETURN_CODE;
                        if (ret < 0) {
                            alert("Failed to start signature process. Reason: " + obj.TOKEN_PARAM_ERROR_DESCRIPTION);
                            close_pad();
                            return;
                        }

                    }
                    else if ((obj.TOKEN_CMD_ORIGIN == "TOKEN_CMD_CLOSE_PAD") || (obj.TOKEN_CMD_ORIGIN == "TOKEN_CMD_API_DEVICE_CLOSE")) {
                        searchState = searchStates.setPadType;
                        openState = openStates.openPad;
                        preparationState = preparationStates.setBackgroundTarget;
                        //check the return code
                        var ret = obj.TOKEN_PARAM_RETURN_CODE;
                        if (ret < 0) {
                            alert("Failed to close pad. Reason: " + obj.TOKEN_PARAM_ERROR_DESCRIPTION);
                            return;
                        }
                    }
                    else if (obj.TOKEN_CMD_ORIGIN == "TOKEN_CMD_API_DEVICE_SET_COM_PORT") {
                        //check the return code
                        var ret = obj.TOKEN_PARAM_RETURN_CODE;
                        if (ret < 0) {
                            alert("Failed to set pad type. Reason: " + obj.TOKEN_PARAM_ERROR_DESCRIPTION);
                            searchState = searchStates.setPadType;
                            openState = openStates.openPad;
                            preparationState = preparationStates.setBackgroundTarget;
                            return;
                        }
						
						//search for pads
						searchState = searchStates.search;
						search_for_pads();
					}
                    else if (obj.TOKEN_CMD_ORIGIN == "TOKEN_CMD_API_DEVICE_GET_COUNT") {
                        //check the return code
                        var ret = obj.TOKEN_PARAM_RETURN_CODE;
                        if (ret < 0) {
                            alert("The search for pads failed. Reason: " + obj.TOKEN_PARAM_ERROR_DESCRIPTION);
							//search finished, reset search state
                            searchState = searchStates.setPadType;
                            openState = openStates.openPad;
                            preparationState = preparationStates.setBackgroundTarget;
                            return;
                        }

                        //check for connected pads
                        if (ret == 0) {
                            alert("No connected pads have been found.");
							//search finished, reset search state
                            searchState = searchStates.setPadType;
                            openState = openStates.openPad;
                            preparationState = preparationStates.setBackgroundTarget;
                            return;
                        }
						
						//get device info
						searchState = searchStates.getInfo;						
						search_for_pads();
                    }
                    else if (obj.TOKEN_CMD_ORIGIN == "TOKEN_CMD_API_DEVICE_GET_INFO") {
                        //check the return code
                        var ret = obj.TOKEN_PARAM_RETURN_CODE;
                        if (ret < 0) {
                            alert("Failed to get device info. Reason: " + obj.TOKEN_PARAM_ERROR_DESCRIPTION);
                            searchState = searchStates.setPadType;
                            openState = openStates.openPad;
                            preparationState = preparationStates.setBackgroundTarget;
                            return;
                        }

						//remember pad type, get image and get button size
						padType = parseInt(obj.TOKEN_PARAM_TYPE);
						switch (padType) {
							case padTypes.sigmaUSB:
							case padTypes.sigmaSerial:
								getBackgroundImage("signo/Sigma.png");
								buttonSize = 36;
								buttonTop = 2;
								break;
							case padTypes.omegaUSB:
							case padTypes.omegaSerial:
								getBackgroundImage("signo/Omega.png");
								buttonSize = 48;
								buttonTop = 4;
								break;
							case padTypes.gammaUSB:
							case padTypes.gammaSerial:
								getBackgroundImage("signo/Gamma.png");
								buttonSize = 48;
								buttonTop = 4;
								break;
							case padTypes.deltaUSB:
							case padTypes.deltaSerial:
							case padTypes.deltaIP:
								getBackgroundImage("signo/Delta.png");
								buttonSize = 48;
								buttonTop = 4;
								break;
							case padTypes.alphaUSB:
							case padTypes.alphaSerial:
							case padTypes.alphaIP:
								getBackgroundImage("signo/Alpha.png");
								buttonSize = 80;
								buttonTop = 10;
								break;
						}
						
						//print device info
                        document.getElementById("PadType_0").innerHTML = obj.TOKEN_PARAM_TYPE;
                        document.getElementById("SerialNumber_0").innerHTML = obj.TOKEN_PARAM_SERIAL;
					
						//get firmware version
						searchState = searchStates.getVersion;						
						search_for_pads();
                    }
                    else if (obj.TOKEN_CMD_ORIGIN == "TOKEN_CMD_API_DEVICE_GET_VERSION") {
                        //check the return code
                        var ret = obj.TOKEN_PARAM_RETURN_CODE;
                        if (ret < 0) {
                            alert("Failed to get device version. Reason: " + obj.TOKEN_PARAM_ERROR_DESCRIPTION);
                            searchState = searchStates.setPadType;
                            openState = openStates.openPad;
                            preparationState = preparationStates.setBackgroundTarget;
                            return;
                        }

                        //print firmware version
                        document.getElementById("FirmwareVersion_0").innerHTML = obj.TOKEN_PARAM_VERSION;
						
						//search finished, reset search state
						searchState = searchStates.setPadType;						

                        //try to open the connected pad
                        open_pad();
                    }
                    else if (obj.TOKEN_CMD_ORIGIN == "TOKEN_CMD_API_DEVICE_OPEN") {
                        //check the return code
                        var ret = obj.TOKEN_PARAM_RETURN_CODE;
                        if (ret < 0) {
                            alert("Failed to open pad. Reason: " + obj.TOKEN_PARAM_ERROR_DESCRIPTION);
                            searchState = searchStates.setPadType;
                            openState = openStates.openPad;
                            preparationState = preparationStates.setBackgroundTarget;
                            return;
                        }
						
						//get display width
						openState = openStates.getDisplayWidth;
						open_pad();
                    }
                    else if (obj.TOKEN_CMD_ORIGIN == "TOKEN_CMD_API_DISPLAY_GET_WIDTH") {
                        //check the return code
                        var ret = obj.TOKEN_PARAM_RETURN_CODE;
                        if (ret < 0) {
                            alert("Failed to get display width. Reason: " + obj.TOKEN_PARAM_ERROR_DESCRIPTION);
                            searchState = searchStates.setPadType;
                            openState = openStates.openPad;
                            preparationState = preparationStates.setBackgroundTarget;
                            return;
                        }

						//remember width
                        displayWidth = ret;
						
						//get display height
						openState = openStates.getDisplayHeight;
						open_pad();
                    }
                    else if (obj.TOKEN_CMD_ORIGIN == "TOKEN_CMD_API_DISPLAY_GET_HEIGHT") {
                        //check the return code
                        var ret = obj.TOKEN_PARAM_RETURN_CODE;
                        if (ret < 0) {
                            alert("Failed to get display height. Reason: " + obj.TOKEN_PARAM_ERROR_DESCRIPTION);
                            searchState = searchStates.setPadType;
                            openState = openStates.openPad;
                            preparationState = preparationStates.setBackgroundTarget;
                            return;
                        }

						//remember height
                        displayHeight = ret;
						
						//get signature point resolution
						openState = openStates.getResolution;
						open_pad();
                    }
                    else if (obj.TOKEN_CMD_ORIGIN == "TOKEN_CMD_API_SIGNATURE_GET_RESOLUTION") {
                        //check the return code
                        var ret = obj.TOKEN_PARAM_RETURN_CODE;
                        if (ret < 0) {
                            alert("Failed to get signature resolution. Reason: " + obj.TOKEN_PARAM_ERROR_DESCRIPTION);
                            searchState = searchStates.setPadType;
                            openState = openStates.openPad;
                            preparationState = preparationStates.setBackgroundTarget;
                            return;
                        }

						//set canvas size
                        sigcanvas.width = displayWidth;
                        sigcanvas.height = displayHeight;

						//get scale factor from siganture resolution to canvas
						scaleFactorX = displayWidth / obj.TOKEN_PARAM_PAD_X_RESOLUTION;
						scaleFactorY = displayHeight / obj.TOKEN_PARAM_PAD_Y_RESOLUTION;
						
						//reset open state
						openState = openStates.openPad;

                        //start the signature process
                        signature_start();
                    }
                    else if (obj.TOKEN_CMD_ORIGIN == "TOKEN_CMD_API_DISPLAY_SET_TARGET") {
                        //check the return code
                        var ret = obj.TOKEN_PARAM_RETURN_CODE;
                        if (ret < 0) {
                            alert("Failed to set display target. Reason: " + obj.TOKEN_PARAM_ERROR_DESCRIPTION);
							close_pad();
                            return;
                        }

						switch (preparationState) {
							case preparationStates.setBackgroundTarget:
								//set background image
								preparationState = preparationStates.setBackgroundImage;
								break;
							case preparationStates.setForegroundTarget:
								//switch buffers to display dialog
								preparationState = preparationStates.switchBuffers;
								break;
							default:
							    preparationState = preparationStates.setBackgroundTarget;
								alert("invalid preparationState");
								return;
						}
                        signature_start();
                    }
                    else if (obj.TOKEN_CMD_ORIGIN == "TOKEN_CMD_API_DISPLAY_SET_IMAGE") {
                        //check the return code
                        var ret = obj.TOKEN_PARAM_RETURN_CODE;
                        if (ret < 0) {
                            alert("Failed to set background image. Reason: " + obj.TOKEN_PARAM_ERROR_DESCRIPTION);
							close_pad();
                            return;
                        }

						//set cancel button
						preparationState = preparationStates.setCancelButton;
                        signature_start();
                    }
                    else if (obj.TOKEN_CMD_ORIGIN == "TOKEN_CMD_API_SENSOR_ADD_HOT_SPOT") {
                        //check the return code
                        var ret = obj.TOKEN_PARAM_RETURN_CODE;
                        if (ret < 0) {
                            alert("Failed to add button. Reason: " + obj.TOKEN_PARAM_ERROR_DESCRIPTION);
							close_pad();
                            return;
                        }

						switch (preparationState) {
							case preparationStates.setCancelButton:
								cancelButton = ret;
								// set retry button
								preparationState = preparationStates.setRetryButton;
								break;
							case preparationStates.setRetryButton:
								retryButton = ret;
								// set confirm button
								preparationState = preparationStates.setConfirmButton;
								break;
							case preparationStates.setConfirmButton:
								confirmButton = ret;
								// set signature rectangle
								preparationState = preparationStates.setSignRect;
								break;
							default:
								preparationState = preparationStates.setBackgroundTarget;
								alert("invalid preparationState");
								return;
						}
                        signature_start();
                    }
                    else if (obj.TOKEN_CMD_ORIGIN == "TOKEN_CMD_API_SENSOR_SET_SIGN_RECT") {
                        //check the return code
                        var ret = obj.TOKEN_PARAM_RETURN_CODE;
                        if (ret < 0) {
                            alert("Failed to set signature rectangle. Reason: " + obj.TOKEN_PARAM_ERROR_DESCRIPTION);
							close_pad();
                            return;
                        }

						// set field name
						preparationState = preparationStates.setFieldName;
                        signature_start();
                    }
                    else if (obj.TOKEN_CMD_ORIGIN == "TOKEN_CMD_API_DISPLAY_SET_TEXT_IN_RECT") {
                        //check the return code
                        var ret = obj.TOKEN_PARAM_RETURN_CODE;
                        if (ret < 0) {
                            alert("Failed to set text. Reason: " + obj.TOKEN_PARAM_ERROR_DESCRIPTION);
							close_pad();
                            return;
                        }

						switch (preparationState) {
							case preparationStates.setFieldName:
								// set custom text
								preparationState = preparationStates.setCustomText;
								break;
							case preparationStates.setCustomText:
								// set foreground target
								preparationState = preparationStates.setForegroundTarget;
								break;
							default:
								preparationState = preparationStates.setBackgroundTarget;
								alert("invalid preparationState");
								return;
						}
                        signature_start();
                    }
                    else if (obj.TOKEN_CMD_ORIGIN == "TOKEN_CMD_API_DISPLAY_SET_IMAGE_FROM_STORE") {
                        //check the return code
                        var ret = obj.TOKEN_PARAM_RETURN_CODE;
                        if (ret < 0) {
                            alert("Failed to switch buffers. Reason: " + obj.TOKEN_PARAM_ERROR_DESCRIPTION);
							close_pad();
                            return;
                        }

						//start signing process
						preparationState = preparationStates.startSignature;
                        signature_start();
                    }
                    else if (obj.TOKEN_CMD_ORIGIN == "TOKEN_CMD_API_SIGNATURE_START") {
                        //check the return code
                        var ret = obj.TOKEN_PARAM_RETURN_CODE;
                        if (ret < 0) {
                            alert("Failed to start signing process. Reason: " + obj.TOKEN_PARAM_ERROR_DESCRIPTION);
							close_pad();
                            return;
                        }

						// reset preparationState
						preparationState = preparationStates.setBackgroundTarget;
                    }
                    else if (obj.TOKEN_CMD_ORIGIN == "TOKEN_CMD_API_SIGNATURE_GET_SIGN_DATA") {
                        //check the return code
                        var ret = obj.TOKEN_PARAM_RETURN_CODE;
                        if (ret < 0) {
                            alert("Failed to get signature SignData. Reason: " + obj.TOKEN_PARAM_ERROR_DESCRIPTION);
                            close_pad();
                            return;
                        }

                        document.getElementById("SignData_0").value = obj.TOKEN_PARAM_SIGN_DATA;

                        signature_image();
                    }
                   else if (obj.TOKEN_CMD_ORIGIN == "TOKEN_CMD_API_SIGNATURE_SAVE_AS_STREAM_EX") {
                        //check the return code
                        var ret = obj.TOKEN_PARAM_RETURN_CODE;
                        if (ret < 0) {
                            alert("Failed to get signature image. Reason: " + obj.TOKEN_PARAM_ERROR_DESCRIPTION);
                            close_pad();
                            return;
                        }

                        document.getElementById("Signature_0").src = "data:image/png;base64," + obj.TOKEN_PARAM_IMAGE;

                        close_pad();
                    }
                    else {
                        // do nothing
                    }
                }
                else {
                    // do nothing
                }
            }

            /**
            * Draws a stroke start point into the canvas.
            */
            function drawStrokeStartPoint(canvasContext, softCoordX, softCoordY) {
                // open new stroke's path
                canvasContext.beginPath();
                canvasContext.arc(softCoordX, softCoordY, 0.1, 0, 2 * Math.PI, true);
                canvasContext.fill();
                canvasContext.stroke();
                canvasContext.moveTo(softCoordX, softCoordY);
            }

            /**
            * Draws a stroke point into the canvas.
            */
            function drawStrokePoint(canvasContext, softCoordX, softCoordY) {
                // continue after start or not start point
                canvasContext.lineTo(softCoordX, softCoordY);
                canvasContext.stroke();
            }

            function getSignature() {
				websocketMode = 0;
				
				//reset the pads properties
				document.getElementById("PadType_0").innerHTML = "";
				document.getElementById("SerialNumber_0").innerHTML = "";
				document.getElementById("FirmwareVersion_0").innerHTML = "";

				//delete the previous signature
				sigcanvas.getContext("2d").clearRect(0, 0, sigcanvas.width, sigcanvas.height);
				document.getElementById("Signature_0").src = "signo/White.png";
				document.getElementById("SignData_0").value = "";

                search_for_pads();
            }

            function clearSignature() {
//                document.getElementById("ModeList").selectedIndex = 0;
//                document.getElementById("PadTypeList").selectedIndex = 0;
                document.getElementById("SignData_0").value = "";
            }

            // TOKEN_CMD_SEARCH_FOR_PADS
            function search_for_pads() {
				message = '{ "TOKEN_TYPE":"TOKEN_TYPE_REQUEST", "TOKEN_CMD":"TOKEN_CMD_SEARCH_FOR_PADS", "TOKEN_PARAM_PAD_SUBSET":"HID" }';
				websocket.send(message);
				logMessage(message);
            }

            // TOKEN_CMD_OPEN_PAD
            function open_pad() {
                //open the pad with index 0
				var message;
				if (websocketMode == websocketModes.Default) {
					// default mode
					message = '{ "TOKEN_TYPE":"TOKEN_TYPE_REQUEST", "TOKEN_CMD":"TOKEN_CMD_OPEN_PAD", "TOKEN_PARAM_PAD_INDEX":"0" }';
				}
				else {
					alert("invalid websocketMode");
					return;
				}
                websocket.send(message);
                logMessage(message);
            }

            // TOKEN_CMD_SIGNATURE_START
            function signature_start() {
				var message;
				if (websocketMode == websocketModes.Default) {
					// default mode
					message = '{ "TOKEN_TYPE":"TOKEN_TYPE_REQUEST", "TOKEN_CMD":"TOKEN_CMD_SIGNATURE_START", "TOKEN_PARAM_FIELD_NAME":"Signature 1", "TOKEN_PARAM_CUSTOM_TEXT":"Please sign!" }';
				}
 				else {
					alert("invalid websocketMode");
					return;
				}
               websocket.send(message);
                logMessage(message);
            }

            // TOKEN_CMD_SIGNATURE_CONFIRM
            function signature_confirm() {
				var message;
				if (websocketMode == websocketModes.Default) {
					// default mode
					message = '{ "TOKEN_TYPE":"TOKEN_TYPE_REQUEST", "TOKEN_CMD":"TOKEN_CMD_SIGNATURE_CONFIRM" }';
				}
				else {
					alert("invalid websocketMode");
					return;
				}
                websocket.send(message);
                logMessage(message);
            }

            // TOKEN_CMD_SIGNATURE_RETRY
            function signature_retry() {
				var message;
				if (websocketMode == websocketModes.Default) {
					// default mode
					message = '{ "TOKEN_TYPE":"TOKEN_TYPE_REQUEST", "TOKEN_CMD":"TOKEN_CMD_SIGNATURE_RETRY" }';
				}
 				else {
					alert("invalid websocketMode");
					return;
				}
                websocket.send(message);
                logMessage(message);
            }

            // TOKEN_CMD_SIGNATURE_CANCEL
            function signature_cancel() {
				var message;
				if (websocketMode == websocketModes.Default) {
					// default mode
					message = '{ "TOKEN_TYPE":"TOKEN_TYPE_REQUEST", "TOKEN_CMD":"TOKEN_CMD_SIGNATURE_CANCEL" }';
				}
				else {
					alert("invalid websocketMode");
					return;
				}
                websocket.send(message);
                logMessage(message);
            }

            // TOKEN_CMD_DISCONNECT
            function disconnect() {
                alert("The pad has been disconnected.");
            }

            // TOKEN_CMD_CLOSE_PAD
            function close_pad() {
				var message;
				if (websocketMode == websocketModes.Default) {
					// default mode
					message = '{ "TOKEN_TYPE":"TOKEN_TYPE_REQUEST", "TOKEN_CMD":"TOKEN_CMD_CLOSE_PAD", "TOKEN_PARAM_PAD_INDEX":"0" }';
				}
				else {
					alert("invalid websocketMode");
					return;
				}
                websocket.send(message);
                logMessage(message);
            }

            // TOKEN_CMD_SIGNATURE_SIGN_DATA
            function signature_sign_data() {
				var message;
				if (websocketMode == websocketModes.Default) {
					// default mode
					message = '{ "TOKEN_TYPE":"TOKEN_TYPE_REQUEST", "TOKEN_CMD":"TOKEN_CMD_SIGNATURE_SIGN_DATA" }';
				}
				else {
					alert("invalid websocketMode");
					return;
				}
                websocket.send(message);
                logMessage(message);
            }

            // TOKEN_CMD_SIGNATURE_IMAGE
            function signature_image() {
				var message;
				if (websocketMode == websocketModes.Default) {
					// default mode
					message = '{ "TOKEN_TYPE":"TOKEN_TYPE_REQUEST", "TOKEN_CMD":"TOKEN_CMD_SIGNATURE_IMAGE' +
                                                                                           '", "TOKEN_PARAM_FILE_TYPE":"' + '1' + // PNG
                                                                                           '", "TOKEN_PARAM_PEN_WIDTH":"' + '5' +
                                                                                           '" }';
	            }
				else {
					alert("invalid websocketMode");
					return;
				}
                websocket.send(message);
                logMessage(message);
            }

			function getBackgroundImage(url) {
				var img = new Image();
				img.setAttribute('crossOrigin', 'anonymous');
				img.onload = function () {
					var canvas = document.createElement("canvas");
					canvas.width =this.width;
					canvas.height =this.height;

					var ctx = canvas.getContext("2d");
					ctx.drawImage(this, 0, 0);

					var dataURL = canvas.toDataURL("image/png");

					backgroundImage = dataURL.replace(/^data:image\/(png|jpg);base64,/, "");
				};
				img.src = url;
			}
        </script>
