<?php
//pr($WriteSignatoryColor);
$signtransport = $this->html->url(array_merge(array('action' => 'signtransport'), $this->request->projectvars['VarsArray']));
?>
</div>

<script type="text/javascript">

		var signtransport = "<?php echo $signtransport;?>";

		$(window).on('beforeunload', function(){
			onMainWindowBeforeUnload();
		});

		$("a").click(function(){
			default_close_pad();
			api_close_pad();
			onMainWindowBeforeUnload();
			return;
		});

		$("input#SignBtnRetry").click(function(){
			signature_cancel_send();
		});

		$("input#SignBtnConfirm").click(function(){
			signature_confirm_send();
		});

/*
		STPadServerLibDefault.handleCancelSignature = function () {
				signature_cancel_send();
		};
*/
		function cancel_signatory() {
		}

		var padStates = {
				closed: 0,
				opened: 1
		};
		var padState = padStates.closed;

		var padModes = {
				Default: 0,
				API: 1
		};
		var padMode = padModes.Default;

		var padTypes = {
				sigmaUSB: 1,
				sigmaSerial: 2,
				zetaUSB: 5,
				zetaSerial: 6,
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

		var deviceCapabilities = {
				HasColorDisplay: 0x00000001,
				HasBacklight: 0x00000002,
				SupportsVerticalScrolling: 0x00000004,
				SupportsHorizontalScrolling: 0x00000008,
				SupportsPenScrolling: 0x00000010,
				SupportsServiceMenu: 0x00000020,
				SupportsRSA: 0x00000040,
				SupportsContentSigning: 0x00000080,
				SupportsH2ContentSigning: 0x00000100,
				CanGenerateSignKey: 0x00000200,
				CanStoreSignKey: 0x00000400,
				CanStoreEncryptKey: 0x00000800,
				CanSignExternalHash: 0x00001000,
				SupportsRSAPassword: 0x00002000,
				SupportsSecureModePassword: 0x00004000,
				Supports4096BitKeys: 0x00008000,
				HasNFCReader: 0x00010000,
				SupportsKeyPad: 0x00020000,
				SupportsKeyPad32: 0x00040000,
				HasDisplay: 0x00080000,
				SupportsRSASignPassword: 0x00100000
		};

		var docHashes = {
				kSha1: 0,
				kSha256: 1
		};

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

		var supportsRSA = false;
		var canStoreEncryptKey = false;

		var field_name = "Signature 1";
		var custom_text = "Please sign!";

		var docHash_type = docHashes.kSha256;
		var sha1_dochash = "AAECAwQFBgcICQoLDA0ODxAREhM=";
		var sha256_dochash = "AAECAwQFBgcICQoLDA0ODxAREhMUFRYXGBkaGxwdHh8=";
		var encryption_cert = "MIICqTCCAZGgAwIBAgIBATANBgkqhkiG9w0BAQUFADAYMRYwFAYDVQQKEw1EZW1vIHNpZ25vdGVjMB4XDTE1MTAwNzA5NDc1MFoXDTI1MTAwNDA5NDc1MFowGDEWMBQGA1UEChMNRGVtbyBzaWdub3RlYzCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBAOFFpsZexYW28Neznn26Bp9NVCJywFFj1QYXg3DDsaSyr6ubuqXKSC4jkenIGBnom/zKPxwPDtNXuy+nyDYFXYNn87TUdh/51CCr3uk9kR9hvRIzBKwkOx0DGLdCoSGAKDOPHwx1rE0m/SOqYOQh6XFjlybw+KzDZcPvhf2Fq/IFNXHpk8m0YHMAReW8q34CYjk9ZtcIlrcYGTikQherOtYM8CaEUPDd6vdJgosGWEnDeNXDCAIWTFc5ECJm9Hh7a47eF3BG5Pjl1QfOSA8lQBV5eTjQc1n1rWCWULt143nIbN5yCFrn0D8W6+eKJV5urETxWUQ208iqgeU1bIgKSEUCAwEAATANBgkqhkiG9w0BAQUFAAOCAQEAt2ax8iwLFoOmlAOZTQcRQtjxseQAhgOTYL/vEP14rPZhF1/gkI9ZzhESdkqR8mHIIl7FnfBg9A2v9ZccC7YgRb4bCXNzv6TIEyz4EYXNkIq8EaaQpvsX4+A5jKIP0PRNZUaLJaDRcQZudd6FMyHxrHtCUTEvORzrgGtRnhBDhAMiSDmQ958t8RhET6HL8C7EnL7f8XBMMFR5sDC60iCu/HeIUkCnx/a2waZ13QvhEIeUBmTRi9gEjZEsGd1iZmgf8OapTjefZMXlbl7CJBymKPJgXFe5mD9/yEMFKNRy5Xfl3cB2gJka4wct6PSIzcQVPaCts6I0V9NfEikXy1bpSA==";
		var encryption_cert_only_when_empty = "TRUE";
		var rsa_scheme = STPadServerLibDefault.RsaScheme.PSS;

		var padIndex = 0;
		var padConnectionType;
		var sampleRate;

		var wsUri = "wss://local.signotecwebsocket.de:49494";

		var state = document.getElementById("status_ws");
		var sigcanvas = document.getElementById("sigCanvas");

		var PEN_COLOR_GREY = "0x007f7f7f";
		var PEN_COLOR_RED = "0x000000ff";
		var PEN_COLOR_GREEN = "0x0000ff00";
		var PEN_COLOR_BLUE = "0x00ff0000";
		var PEN_COLOR_BLACK = "0x00000000";

		var MODE_LIST_DEFAULT = "Default";
		var MODE_LIST_API = "API";

		if (window.WebSocket === undefined) {
				state.innerHTML = "sockets not supported " + evt.target.url;
				state.className = "fail";
		}
		else {
				if (typeof String.prototype.startsWith != "function") {
						String.prototype.startsWith = function (str) {
								return this.indexOf(str) == 0;
						};
				}
		}

		function onMainWindowLoad() {
				STPadServerLibCommons.handleLogging = logMessage;
				STPadServerLibCommons.createConnection(wsUri, onOpen, onClose, onError);

				clearSignature();
//				check_boxes_selectedElements_onchange();
//				ModeListName_onchange();
		}

		function onMainWindowBeforeUnload() {
				STPadServerLibCommons.destroyConnection();
		}

		function onOpen(evt) {
				state.className = "success";
				if ((evt.target === undefined) || (evt.target.url === undefined)) {
						state.innerHTML = "ActiveX loaded";
				}
				else {
						state.innerHTML = "Connected to " + evt.target.url;
				}
		}

		function onClose(evt) {
				state.className = "fail";
				if ((evt.target === undefined) || (evt.target.url === undefined)) {
						state.innerHTML = "ActiveX unloaded";
				}
				else {
						state.innerHTML = "Disconnected from " + evt.target.url;
				}
		}

		function onError(evt) {
				state.className = "fail";
				if ((evt.target === undefined) || (evt.target.url === undefined)) {
						state.innerHTML = "Communication error";
				}
				else {
						state.innerHTML = "Communication error " + evt.target.url;
				}
		}

		function logMessage(msg) {
//				log.innerHTML = "<li>" + msg + "</li>";
		}

		/**
		* Draws a stroke start point into the canvas
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
		* Draws a stroke point into the canvas
		*/
		function drawStrokePoint(canvasContext, softCoordX, softCoordY) {
				// continue after start or not start point
				canvasContext.lineTo(softCoordX, softCoordY);
				canvasContext.stroke();
		}

		/**
		* The send events
		*/
		STPadServerLibCommons.handleDisconnect = function (index) {
				disconnect_send(index);
		};

		STPadServerLibCommons.handleNextSignaturePoint = function (x, y, p) {
				signature_point_send(x, y, p);
		}

		STPadServerLibDefault.handleRetrySignature = function () {
				signature_retry_send();
		};

		STPadServerLibDefault.handleConfirmSignature = function () {
				signature_confirm_send();
		};

		STPadServerLibDefault.handleCancelSignature = function () {
				signature_cancel_send();
		};

		STPadServerLibDefault.handleConfirmSelection = function () {
				selection_confirm_send();
		};

		STPadServerLibDefault.handleSelectionChange = function (fieldId, fieldChecked) {
				selection_change_send(fieldId, fieldChecked);
		};

		STPadServerLibDefault.handleCancelSelection = function () {
				selection_cancel_send();
		};

		STPadServerLibDefault.handleError = function (error_context, return_code, error_description) {
				error_send(error_context, return_code, error_description);
		};

		STPadServerLibApi.Sensor.handleHotSpotPressed = function (button) {
				api_sensor_hot_spot_pressed_send(button);
		};

		STPadServerLibApi.Sensor.handleDisplayScrollPosChanged = function (xPos, yPos) {
				api_display_scroll_pos_changed_send(xPos, yPos);
		};

		// disconnect send begin
		function disconnect_send(index) {
				var msg = "The pad (index: " + index + ") has been disconnected.";
				alert(msg);

				padState = padStates.closed;
		}
		// disconnect send end

		// signature point send begin
		function signature_point_send(x, y, p) {
				var ctx = sigcanvas.getContext("2d");

				ctx.fillStyle = "#fff";

				switch (document.getElementById("signaturePenColorSelect").value) {
						case PEN_COLOR_GREY:
								ctx.strokeStyle = "#7F7F7F";
								break;

						case PEN_COLOR_RED:
								ctx.strokeStyle = "#FF0000";
								break;

						case PEN_COLOR_GREEN:
								ctx.strokeStyle = "#00FF00";
								break;

						case PEN_COLOR_BLUE:
								ctx.strokeStyle = "#0000FF";
								break;

						case PEN_COLOR_BLACK:
								ctx.strokeStyle = "#000000";
								break;

						default:
								ctx.strokeStyle = "#FF0000";
								break;
				}

				ctx.lineWidth = 4.5;
				ctx.lineCap = "round";

				if (p == 0) {
						drawStrokeStartPoint(ctx, x * scaleFactorX, y * scaleFactorY);
				}
				else {
						drawStrokePoint(ctx, x * scaleFactorX, y * scaleFactorY);
				}
		}
		// signature point send end

		// signature retry send begin
		function signature_retry_send() {
				if (padMode == padModes.Default) {
						// default mode
						STPadServerLibDefault.retrySignature()
								.then(function (value) {
										var ctx = sigcanvas.getContext("2d");
										ctx.clearRect(0, 0, sigcanvas.width, sigcanvas.height);
								}, STPadServerLibCommons.defaultReject)
								.then(null, function (reason) {
										error_message(reason);
										default_close_pad();
								});
				}
				else if (padMode == padModes.API) {
						// API mode
						STPadServerLibApi.Signature.retry()
								.then(function (value) {
										var ctx = sigcanvas.getContext("2d");
										ctx.clearRect(0, 0, sigcanvas.width, sigcanvas.height);
								}, STPadServerLibCommons.defaultReject)
								.then(null, function (reason) {
										error_message(reason);
										api_close_pad();
								});
				}
				else {
						alert("invalid padMode");
						return;
				}
		}
		// signature retry send end

		// signature confirm send begin
		function signature_confirm_send() {
				if (padMode == padModes.Default) {
						// default mode
						STPadServerLibDefault.confirmSignature()
								.then(function (value) {
										// check if there are enough points for a valid signature
										if ((value.countedPoints / sampleRate) <= 0.2) {
												alert("The signature is too short. Please sign again!");
												return STPadServerLibDefault.retrySignature();
										}
										if (supportsRSA) {
												return STPadServerLibDefault.getSigningCert();
										}
										else {
												return value;
										}
								}, STPadServerLibCommons.defaultReject)
								.then(function (value) {
										if (value.command == "TOKEN_CMD_SIGNATURE_RETRY") {
												var ctx = sigcanvas.getContext("2d");
												ctx.clearRect(0, 0, sigcanvas.width, sigcanvas.height);
												return value;
										}
										if (supportsRSA) {
												if (value.signingCert !== undefined) {
//														document.getElementById("signatureCert_0").innerHTML = value.signingCert;
												}
										}
										else {
//												document.getElementById("signatureCert_0").innerHTML = "none";
										}
										var getSignatureImageParams = new STPadServerLibDefault.Params.getSignatureImage();
										getSignatureImageParams.setFileType(STPadServerLibDefault.FileType.PNG);
										getSignatureImageParams.setPenWidth(5);
										return STPadServerLibDefault.getSignatureImage(getSignatureImageParams);
								}, STPadServerLibCommons.defaultReject)
								.then(function (value) {
										if (value.command == "TOKEN_CMD_SIGNATURE_RETRY") {
												// just do nothing but returning original promise object
												return value;
										}
										if (value.file !== undefined) {

												var div_height = $("img#Signature_0").parent().closest("div").height();
												var div_width = $("img#Signature_0").parent().closest("div").width();

												$("canvas#sigCanvas").hide();
												document.getElementById("Signature_0").src = "data:image/png;base64," + value.file;

												$("img#Signature_0").parent().closest("div").width(div_width);
												$("img#Signature_0").parent().closest("div").height(div_height);
												$("div.current_signs").css("background-image","img/indicator.gif");

												var data = new Array();

												data.push({name: "ajax_true", value: 1});
												data.push({name: "dialog", value: 1});
												data.push({name: "image", value: value.file});
												data.push({name: "color", value: $("#showPaletteOnly").val()});

												$.ajax({
														type	: "POST",
														cache	: false,
														url		: signtransport,
														data	: data,
														dataType: "json",
														success: function(data) {
															//Signatur erfolgreich
														}
													});

										}
										var getSignatureDataParams = new STPadServerLibDefault.Params.getSignatureData();
										getSignatureDataParams.setRsaScheme(rsa_scheme);
										return STPadServerLibDefault.getSignatureData(getSignatureDataParams);
								}, STPadServerLibCommons.defaultReject)
								.then(function (value) {

										if (value.command == "TOKEN_CMD_SIGNATURE_RETRY") {
												// just do nothing but returning original promise object
												return value;
										}
										if (supportsRSA) {

												if (value.certId !== undefined) {
														document.getElementById("biometryCertID_0").innerHTML = value.certId;
												}

												document.getElementById("RSAScheme_0").innerHTML = rsa_scheme;

												if (value.rsaSignature !== undefined) {

														$("#AjaxSvgLoader").show();
														$("dl#status_ws").hide();
														$("dl#mode_ws").hide();

														var data = new Array();
														data.push({name: "ajax_true", value: 1});
														data.push({name: "dialog", value: 1});
														data.push({name: "signature", value: value.rsaSignature});

														$.ajax({
																type	: "POST",
																cache	: false,
																url		: signtransport,
																data	: data,
																dataType: "json",
																success: function(data) {
																	$("#AjaxSvgLoader").hide();
																	ShowSignatoryAfterSaving(data);
																}
															});
												} else {

												}
										}
										else {

											document.getElementById("biometryCertID_0").innerHTML = "none";
												document.getElementById("RSAScheme_0").innerHTML = "none";
//												document.getElementById("RsaSignature_0").value = "";
										}
										if (value.signData !== undefined) {
//												document.getElementById("SignData_0").value = value.signData;
										}

										default_close_pad();
								}, STPadServerLibCommons.defaultReject)
								.then(null, function (reason) {
										error_message(reason);
										default_close_pad();
								});

				}
				else if (padMode == padModes.API) {
						// API mode
						STPadServerLibApi.Signature.confirm()
								.then(function (value) {
										// check if there are enough points for a valid signature
										if ((value.countedPoints / sampleRate) <= 0.2) {
												alert("The signature is too short. Please sign again!");
												return STPadServerLibApi.Signature.retry();
										}
										var saveAsStreamExParams = new STPadServerLibApi.Signature.Params.saveAsStreamEx(300, 0, 0, STPadServerLibApi.FileType.PNG, 5, document.getElementById("signaturePenColorSelect").value, 0);
										return STPadServerLibApi.Signature.saveAsStreamEx(saveAsStreamExParams);
								}, STPadServerLibCommons.defaultReject)
								.then(function (value) {
										if (value.command == "TOKEN_CMD_API_SIGNATURE_RETRY") {
												var ctx = sigcanvas.getContext("2d");
												ctx.clearRect(0, 0, sigcanvas.width, sigcanvas.height);
												return value;
										}
										if (value.image !== undefined) {
												document.getElementById("Signature_0").src = "data:image/png;base64," + value.image;
										}
										return STPadServerLibApi.Signature.getSignData();
								}, STPadServerLibCommons.defaultReject)
								.then(function (value) {
										if (value.command == "TOKEN_CMD_API_SIGNATURE_RETRY") {
												// just do nothing but returning original promise object
												return value;
										}
										if (value.signData !== undefined) {
//												document.getElementById("SignData_0").value = value.signData;
										}
										api_close_pad();
								}, STPadServerLibCommons.defaultReject)
								.then(function (value) {
								}, function (reason) {
										error_message(reason);
										api_close_pad();
								});
				}
				else {
						alert("invalid padMode");
						return;
				}
		}
		// signature confirm send end

		// signature cancel send begin
		function signature_cancel_send() {
				if (padMode == padModes.Default) {
						// default mode
						STPadServerLibDefault.cancelSignature()
								.then(function (value) {
										var ctx = sigcanvas.getContext("2d");
										ctx.clearRect(0, 0, sigcanvas.width, sigcanvas.height);
										default_close_pad();
								}, STPadServerLibCommons.defaultReject)
								.then(null, function (reason) {
										error_message(reason);
										default_close_pad();
								});
				}
				else if (padMode == padModes.API) {
						// API mode
						var cancelParams = new STPadServerLibApi.Signature.Params.cancel();
						cancelParams.setErase(0);
						STPadServerLibApi.Signature.cancel(cancelParams)
								.then(function (value) {
										var ctx = sigcanvas.getContext("2d");
										ctx.clearRect(0, 0, sigcanvas.width, sigcanvas.height);
										api_close_pad();
								}, STPadServerLibCommons.defaultReject)
								.then(function (value) {
								}, function (reason) {
										error_message(reason);
										api_close_pad();
								});
				}
				else {
						alert("invalid padMode");
						return;
				}
		}
		// signature cancel send end

		// selection confirm send begin
		function selection_confirm_send() {
				if (padMode == padModes.Default) {
						// default mode
						var status = '';
/*
						for (i = 1; i <= document.getElementById("check_boxes_selectedElements").value; i++) {
								status += 'Feld ' + i + ' = ' + document.getElementById("fieldChecked" + i).checked + '\n';
						}
*/
						alert(status);
						signature_start();
				}
				else if (padMode == padModes.API) {
						// API mode
						// do nothing
				}
				else {
						alert("invalid padMode");
						return;
				}
		}
		// selection confirm send end

		// selection change send begin
		function selection_change_send(fieldId, fieldChecked) {
				if (padMode == padModes.Default) {
						// default mode
						/*
						for (i = 1; i <= document.getElementById("check_boxes_selectedElements").value; i++) {
								if (document.getElementById("fieldID" + i).value == fieldId) {
										if (fieldChecked == "TRUE") {
												document.getElementById("fieldChecked" + i).checked = true;
										} else {
												document.getElementById("fieldChecked" + i).checked = false;
										}
								}
						}
						*/
				}
				else if (padMode == padModes.API) {
						// API mode
						// do nothing
				}
				else {
						alert("invalid padMode");
						return;
				}
		}
		// selection change send end

		// selection cancel send begin
		function selection_cancel_send() {
				if (padMode == padModes.Default) {
						// default mode
						var ctx = sigcanvas.getContext("2d");
						ctx.clearRect(0, 0, sigcanvas.width, sigcanvas.height);

						STPadServerLibDefault.cancelSignature()
								.then(function (value) {
										default_close_pad();
								}, STPadServerLibCommons.defaultReject)
								.then(null, function (reason) {
										error_message(reason);
										default_close_pad();
								});
				}
				else if (padMode == padModes.API) {
						// API mode
						// do nothing
				}
				else {
						alert("invalid padMode");
						return;
				}
		}
		// selection cancel send end

		// error send begin
		function error_send(error_context, return_code, error_description) {
				var ret = return_code;
				if (ret < 0) {
						alert("Failed to confirm the signature. Reason: " + error_description + ", Context: " + error_context);
				}
		}
		// error send end

		// api sensor hot spot pressed send begin
		function api_sensor_hot_spot_pressed_send(button) {
				switch (button) {
						// cancel signing process
						case cancelButton:
								signature_cancel_send();
								break;

						// restart signing process
						case retryButton:
								signature_retry_send();
								break;

						// confirm signing process
						case confirmButton:
								signature_confirm_send();
								break;

						default:
								alert("unknown button id: " + button);
				}
		}
		// api sensor hot spot pressed send end

		// api display scroll pos changed send begin
		function api_display_scroll_pos_changed_send(xPos, yPos) {
		}
		// api display scroll pos changed send end

		// getSignature begin
		function getSignature() {
				//var version;

				//version = "1.0.0.0";
				//version = "1.0.2.4";
				//version = "1.7.0.0";

				// set the json interface version
				//var setInterfaceVersionParams = new STPadServerLibCommons.Params.setInterfaceVersion(version);
				//STPadServerLibCommons.setInterfaceVersion(setInterfaceVersionParams);

				//reset the pads properties
				document.getElementById("PadType_0").innerHTML = "";
				document.getElementById("SerialNumber_0").innerHTML = "";
				document.getElementById("FirmwareVersion_0").innerHTML = "";
				document.getElementById("RSASupport_0").innerHTML = "";
				document.getElementById("biometryCertID_0").innerHTML = "";
				document.getElementById("RSAScheme_0").innerHTML = "";
//				document.getElementById("signatureCert_0").innerHTML = "";

				//delete the previous signature
				var ctx = sigcanvas.getContext("2d");
				ctx.clearRect(0, 0, sigcanvas.width, sigcanvas.height);

				document.getElementById("Signature_0").src = "signo/White.png";
//				document.getElementById("SignData_0").value = "";
//				document.getElementById("RsaSignature_0").value = "";

				var padConnectionTypeList = document.getElementById("PadConnectionTypeList");
				if (padConnectionTypeList.selectedIndex == -1) {
						//if the pad type is not selected message to user and return
						alert("Please select a pad type from the list!");
						padConnectionTypeList.focus();
						return;
				}

				if (padConnectionTypeList.selectedIndex == 0) {
						//search for USB pads
						padConnectionType = "HID";
				}
				else {
						//search for serial pads
						padConnectionType = "All";
				}

				if (padMode == padModes.Default) {
						// default mode
						getSignatureDefault();
				}
				else if (padMode == padModes.API) {
						// API mode
						getSignatureAPI();
				}
				else {
						alert("invalid padMode");
						return;
				}
		}
		// getSignature end

		// getSignatureDefault begin
		function getSignatureDefault() {

				// search for pads begin
				var searchForPadsParams = new STPadServerLibDefault.Params.searchForPads();
				searchForPadsParams.setPadSubset(padConnectionType);
				STPadServerLibDefault.searchForPads(searchForPadsParams)
						.then(function (pads) {
								if (pads.foundPads.length == 0) {
										alert("No connected pads have been found.");
										return Promise.reject("No connected pads have been found.");
								}

								padType = pads.foundPads[padIndex].type;

								document.getElementById("PadType_0").innerHTML = getReadableType(padType);
								document.getElementById("SerialNumber_0").innerHTML = pads.foundPads[padIndex].serialNumber;
								document.getElementById("FirmwareVersion_0").innerHTML = pads.foundPads[padIndex].firmwareVersion;

								if (pads.foundPads[padIndex].capabilities & deviceCapabilities.SupportsRSA) {
										supportsRSA = true;
								} else {
										supportsRSA = false;
								}

								if (pads.foundPads[padIndex].capabilities & deviceCapabilities.CanStoreEncryptKey) {
										canStoreEncryptKey = true;
								} else {
										canStoreEncryptKey = false;
								}

								if (supportsRSA) {
										document.getElementById("RSASupport_0").innerHTML = "Yes";
								}
								else {
										document.getElementById("RSASupport_0").innerHTML = "No";
								}
						}, STPadServerLibCommons.defaultReject)
						// search for pads end

						// open pad begin
						.then(function (value) {
								var openPadParams = new STPadServerLibDefault.Params.openPad(padIndex);
								return STPadServerLibDefault.openPad(openPadParams);
						}, STPadServerLibCommons.defaultReject)
						.then(function (padInfo) {
								padState = padStates.opened;

								sigcanvas.width = padInfo.padInfo.displayWidth;
								sigcanvas.height = padInfo.padInfo.displayHeight;

								//get scale factor from signature resolution to canvas
								scaleFactorX = padInfo.padInfo.displayWidth / padInfo.padInfo.xResolution;
								scaleFactorY = padInfo.padInfo.displayHeight / padInfo.padInfo.yResolution;

								//get sample rate
								sampleRate = padInfo.padInfo.samplingRate;

								//start the signature process
								selection_dialog();
						}, STPadServerLibCommons.defaultReject)
						// open pad end

						.then(function (selection) {
						}, function (reason) {
								error_message(reason);
								default_close_pad();
						});
		}
		// getSignatureDefault end

		// getSignatureAPI begin
		function getSignatureAPI() {

				// api search for pads begin
				var setComPortParams = new STPadServerLibApi.Device.Params.setComPort(padConnectionType);
				STPadServerLibApi.Device.setComPort(setComPortParams)
						.then(function (value) {
								return STPadServerLibApi.Device.getCount();
						}, STPadServerLibCommons.defaultReject)
						.then(function (value) {
								if (value.countedDevices == 0) {
										alert("No connected pads have been found.");
										return Promise.reject("No connected pads have been found.");
								}
						}, STPadServerLibCommons.defaultReject)
						.then(function (value) {
								return STPadServerLibApi.Device.getInfo(new STPadServerLibApi.Device.Params.getInfo(padIndex));
						}, STPadServerLibCommons.defaultReject)
						.then(function (info) {
								padType = info.type;
								document.getElementById("PadType_0").innerHTML = getReadableType(padType);
								document.getElementById("SerialNumber_0").innerHTML = info.serial;
								var imageName = getReadableType(padType).split(" ");
								var padName = imageName[0];
								switch (padName) {
										case "Sigma":
												buttonSize = 36;
												buttonTop = 2;
												break;
										case "Zeta":
												buttonSize = 36;
												buttonTop = 2;
												break;
										case "Omega":
										case "Gamma":
										case "Delta":
												buttonSize = 48;
												buttonTop = 4;
												break;
										case "Alpha":
												buttonSize = 80;
												buttonTop = 10;
												break;
								}
								getBackgroundImage(padName);
								return STPadServerLibApi.Device.getVersion(new STPadServerLibApi.Device.Params.getVersion(padIndex));
						}, STPadServerLibCommons.defaultReject)
						.then(function (version) {
								document.getElementById("FirmwareVersion_0").innerHTML = version.version;
						}, STPadServerLibCommons.defaultReject)
						// api search for pads end

						// api device open begin
						.then(function (value) {
								var openParams = new STPadServerLibApi.Device.Params.open(padIndex);
								openParams.setEraseDisplay(true);
								return STPadServerLibApi.Device.open(openParams);
						}, STPadServerLibCommons.defaultReject)
						.then(function (value) {
								padState = padStates.opened;
								var params = new STPadServerLibApi.Display.Params.configPen(3, document.getElementById("signaturePenColorSelect").value);
								return STPadServerLibApi.Display.configPen(params);
						}, STPadServerLibCommons.defaultReject)
						.then(function (value) {
								return STPadServerLibApi.Display.getWidth();
						}, STPadServerLibCommons.defaultReject)
						.then(function (width) {
								sigcanvas.width = width.width;
								return STPadServerLibApi.Display.getHeight();
						}, STPadServerLibCommons.defaultReject)
						.then(function (height) {
								sigcanvas.height = height.height;
								return STPadServerLibApi.Signature.getResolution();
						}, STPadServerLibCommons.defaultReject)
						.then(function (resolution) {
								scaleFactorX = sigcanvas.width / resolution.xResolution;
								scaleFactorY = sigcanvas.height / resolution.yResolution;
								return STPadServerLibApi.Sensor.getSampleRateMode();
						}, STPadServerLibCommons.defaultReject)
						.then(function (mode) {
								//get sample rate
								switch (mode.sampleRateMode) {
										case 0:
												sampleRate = 125;
												break;
										case 1:
												sampleRate = 250;
												break;
										case 2:
												sampleRate = 500;
												break;
										case 3:
												sampleRate = 280;
												break;
										default:
												alert("Failed to get sample rate. Reason: Unexpected sample rate mode: " + mode.sampleRateMode);
												return;
								}
						}, STPadServerLibCommons.defaultReject)
						// api device open end

						// api signature start begin
						.then(function (value) {
								var displayRotation = 0;
								return STPadServerLibApi.Display.setRotation(new STPadServerLibApi.Display.Params.setRotation(displayRotation));
						}, STPadServerLibCommons.defaultReject)
						.then(function (value) {
								return STPadServerLibApi.Display.getRotation();
						}, STPadServerLibCommons.defaultReject)
						.then(function (value) {
								var displayRotation = value;
								var params = new STPadServerLibApi.Display.Params.setTarget(1);
								return STPadServerLibApi.Display.setTarget(params);
						}, STPadServerLibCommons.defaultReject)
						.then(function (value) {
								var setImageParams = new STPadServerLibApi.Display.Params.setImage(0, 0, backgroundImage);
								return STPadServerLibApi.Display.setImage(setImageParams);
						}, STPadServerLibCommons.defaultReject)
						.then(function (value) {
								buttonDiff = sigcanvas.width / 3;
								buttonLeft = (buttonDiff - buttonSize) / 2;
								return STPadServerLibApi.Sensor.addHotSpot(new STPadServerLibApi.Sensor.Params.addHotSpot(Math.round(buttonLeft), buttonTop, buttonSize, buttonSize));
						}, STPadServerLibCommons.defaultReject)
						.then(function (value) {
								cancelButton = value.hotspotId;
								buttonLeft = buttonLeft + buttonDiff;
								return STPadServerLibApi.Sensor.addHotSpot(new STPadServerLibApi.Sensor.Params.addHotSpot(Math.round(buttonLeft), buttonTop, buttonSize, buttonSize));
						}, STPadServerLibCommons.defaultReject)
						.then(function (value) {
								retryButton = value.hotspotId;
								buttonLeft = buttonLeft + buttonDiff;
								return STPadServerLibApi.Sensor.addHotSpot(new STPadServerLibApi.Sensor.Params.addHotSpot(Math.round(buttonLeft), buttonTop, buttonSize, buttonSize));
						}, STPadServerLibCommons.defaultReject)
						.then(function (value) {
								confirmButton = value.hotspotId;
								var top = 0;
								switch (padType) {
										case padTypes.sigmaUSB:
										case padTypes.sigmaSerial:
												top = 40;
												break;
										case padTypes.zetaUSB:
										case padTypes.zetaSerial:
												top = 40;
												break;
										case padTypes.omegaUSB:
										case padTypes.omegaSerial:
										case padTypes.gammaUSB:
										case padTypes.gammaSerial:
										case padTypes.deltaUSB:
										case padTypes.deltaSerial:
										case padTypes.deltaIP:
												top = 56;
												break;
										case padTypes.alphaUSB:
										case padTypes.alphaSerial:
										case padTypes.alphaIP:
												top = 100;
												break;
										default:
												alert("unkown pad type: " + padType);
												return;
								}

								return STPadServerLibApi.Sensor.setSignRect(new STPadServerLibApi.Sensor.Params.setSignRect(0, top, sigcanvas.width, sigcanvas.height));
						}, STPadServerLibCommons.defaultReject)
						.then(function (value) {
								var left;
								var top;
								var width;
								var height;
								switch (padType) {
										case padTypes.sigmaUSB:
										case padTypes.sigmaSerial:
												left = 15;
												top = 43;
												width = 285;
												height = 18;
												break;
										case padTypes.zetaUSB:
										case padTypes.zetaSerial:
												left = 15;
												top = 43;
												width = 285;
												height = 18;
												break;
										case padTypes.omegaUSB:
										case padTypes.omegaSerial:
												left = 40;
												top = 86;
												width = 570;
												height = 40;
												break;
										case padTypes.gammaUSB:
										case padTypes.gammaSerial:
												left = 40;
												top = 86;
												width = 720;
												height = 40;
												break;

										case padTypes.deltaUSB:
										case padTypes.deltaSerial:
										case padTypes.deltaIP:
												left = 40;
												top = 86;
												width = 1200;
												height = 50;
												break;
										case padTypes.alphaUSB:
										case padTypes.alphaSerial:
										case padTypes.alphaIP:
												left = 20;
												top = 120;
												width = 730;
												height = 30;
												break;
										default:
												alert("unkown pad type: " + padType);
												return;
								}
								var setTextInRectParams = new STPadServerLibApi.Display.Params.setTextInRect(left, top, width, height, STPadServerLibApi.TextAlignment.LEFT, field_name, 0);
								return STPadServerLibApi.Display.setTextInRect(setTextInRectParams);
						}, STPadServerLibCommons.defaultReject)
						.then(function (textWidth) {
								var left;
								var top;
								var width;
								var height;
								switch (padType) {
										case padTypes.sigmaUSB:
										case padTypes.sigmaSerial:
												left = 15;
												top = 110;
												width = 265;
												height = 18;
												break;
										case padTypes.zetaUSB:
										case padTypes.zetaSerial:
												left = 15;
												top = 150;
												width = 265;
												height = 18;
												break;
										case padTypes.omegaUSB:
										case padTypes.omegaSerial:
												left = 40;
												top = 350;
												width = 520;
												height = 40;
												break;
										case padTypes.gammaUSB:
										case padTypes.gammaSerial:
												left = 40;
												top = 350;
												width = 670;
												height = 40;
												break;
										case padTypes.deltaUSB:
										case padTypes.deltaSerial:
										case padTypes.deltaIP:
												left = 40;
												top = 640;
												width = 670;
												height = 50;
												break;
										case padTypes.alphaUSB:
										case padTypes.alphaSerial:
										case padTypes.alphaIP:
												left = 20;
												top = 1316;
												width = 730;
												height = 30;
												break;
										default:
												alert("unkown pad type: " + padType);
												return;
								}

								var setTextInRectParams = new STPadServerLibApi.Display.Params.setTextInRect(left, top, width, height, STPadServerLibApi.TextAlignment.LEFT, custom_text, 0);
								return STPadServerLibApi.Display.setTextInRect(setTextInRectParams);
						}, STPadServerLibCommons.defaultReject)
						.then(function (value) {
								var params = new STPadServerLibApi.Display.Params.setTarget(0);
								return STPadServerLibApi.Display.setTarget(params);
						}, STPadServerLibCommons.defaultReject)
						.then(function (value) {
								var params = new STPadServerLibApi.Display.Params.setImageFromStore(1);
								return STPadServerLibApi.Display.setImageFromStore(params);
						}, STPadServerLibCommons.defaultReject)
						.then(function (value) {
								return STPadServerLibApi.Signature.start();
						}, STPadServerLibCommons.defaultReject)
						// api signature start end

						.then(function (value) {
						}, function (reason) {
								error_message(reason);
								api_close_pad();
						});
		}
		// getSignatureAPI end

		// selection dialog begin
		function selection_dialog() {
				if (padMode == padModes.Default) {
						// default mode
						var selectedElement = 0;
						if (selectedElement > 0) {
								// prepare selection dialog
								var checkBoxInformation = [];
								for (var i = 1; i <= selectedElement; i++) {
										var box = {};
										box.id = document.getElementById("fieldID" + i).value;
										box.text = document.getElementById("fieldText" + i).value;
										box.checked = document.getElementById("fieldChecked" + i).checked;
										box.required = document.getElementById("fieldRequired" + i).checked;
										checkBoxInformation[i - 1] = box;
								}

								var startSelectionDialogParams = new STPadServerLibDefault.Params.startSelectionDialog();
								startSelectionDialogParams.addCheckboxInformation(checkBoxInformation);
								STPadServerLibDefault.startSelectionDialog(startSelectionDialogParams)
										.then(function (value) {
										}, STPadServerLibCommons.defaultReject)
										.then(null, function (reason) {
												error_message(reason);
												default_close_pad();
										});
						} else {
								// start signature capture
								signature_start();
						}
				}
				else if (padMode == padModes.API) {
						// API mode
						// do nothing
				}
				else {
						alert("invalid padMode");
						return;
				}
		}
		// selection dialog end

		// signature start begin
		function signature_start() {
				var dochash;

				switch (docHash_type) {
						case docHashes.kSha1:
								dochash = sha1_dochash;
								break;

						case docHashes.kSha256:
								dochash = sha256_dochash;
								break;

						default:
								alert("unknown doc hash");
								return;
				}

				if (supportsRSA) {
						var startSignatureParams = new STPadServerLibDefault.Params.startSignature();
						startSignatureParams.setFieldName(field_name);
						startSignatureParams.setCustomText(custom_text);
						if (canStoreEncryptKey) {
								startSignatureParams.enablePadEncryption(dochash, encryption_cert, encryption_cert_only_when_empty);
						} else {
								startSignatureParams.enablePadEncryption(dochash, null);
						}
						STPadServerLibDefault.startSignature(startSignatureParams)
								.then(function (value) {
								}, STPadServerLibCommons.defaultReject)
								.then(null, function (reason) {
										error_message(reason);
										default_close_pad();
								});
				}
				else {
						var startSignatureParams = new STPadServerLibDefault.Params.startSignature();
						startSignatureParams.setFieldName(field_name);
						startSignatureParams.setCustomText(custom_text);
						STPadServerLibDefault.startSignature(startSignatureParams)
								.then(function (value) {
								}, STPadServerLibCommons.defaultReject)
								.then(null, function (reason) {
										error_message(reason);
										default_close_pad();
								});
				}
		};
		// signature start end

		// close pad begin
		function default_close_pad() {
				if (padState == padStates.opened) {
						var closePadParams = new STPadServerLibDefault.Params.closePad(padIndex);
						STPadServerLibDefault.closePad(closePadParams)
								.then(function (value) {
										padState = padStates.closed;
								}, STPadServerLibCommons.defaultReject)
								.then(null, function (reason) {
										error_message(reason);
										return;
								});
				}
		}

		function api_close_pad() {
				if (padState == padStates.opened) {
						var closePadParams = new STPadServerLibApi.Device.Params.close(padIndex);
						STPadServerLibApi.Device.close(closePadParams)
								.then(function (value) {
										padState = padStates.closed;
								}, STPadServerLibCommons.defaultReject)
								.then(null, function (reason) {
										error_message(reason);
										return;
								});
				}
		}
		// close pad end

		function getBackgroundImage(padName) {
				var img = new Image();
				img.setAttribute('crossOrigin', 'anonymous');
				img.onload = function () {
						var sigcanvas = document.createElement("canvas");
						sigcanvas.width = this.width;
						sigcanvas.height = this.height;

						var ctx = sigcanvas.getContext("2d");
						ctx.drawImage(this, 0, 0);

						var dataURL = sigcanvas.toDataURL("image/png");
						backgroundImage = dataURL.replace(/^data:image\/(png|jpg);base64,/, "");
				};
				var element = document.getElementById(padName);
				img.src = element.src;
		}

		function getReadableType(intTypeNumber) {
				switch (intTypeNumber) {
						case padTypes.sigmaUSB:
								return "Sigma USB";
						case padTypes.sigmaSerial:
								return "Sigma serial";
						case padTypes.zetaUSB:
								return "Zeta USB";
						case padTypes.zetaSerial:
								return "Zeta serial";
						case padTypes.omegaUSB:
								return "Omega USB";
						case padTypes.omegaSerial:
								return "Omega serial";
						case padTypes.gammaUSB:
								return "Gamma USB";
						case padTypes.gammaSerial:
								return "Gamma serial";
						case padTypes.deltaUSB:
								return "Delta USB";
						case padTypes.deltaSerial:
								return "Delta serial";
						case padTypes.deltaIP:
								return "Delta IP";
						case padTypes.alphaUSB:
								return "Alpha USB";
						case padTypes.alphaSerial:
								return "Alpha serial";
						case padTypes.alphaIP:
								return "Alpha IP";
						default:
								return "Unknown";
				}
		}

		function clearSignature() {
//				document.getElementById("ModeList").selectedIndex = 0;
				document.getElementById("PadConnectionTypeList").selectedIndex = 0;
				document.getElementById("Signature_0").src = "signo/White.png";
//				document.getElementById("SignData_0").value = "";
//				document.getElementById("RsaSignature_0").value = "";
		}

		function error_message(param) {
				if (param.errorCode < 0) {
						alert("Function " + param.command + " failed. Reason: " + param.errorMessage);
				}
		}

		function ShowSignatoryAfterSaving(data){

			if(data.error){
				json_request_animation("Error");
				return false;
			}

			if(!data.colored_image){
				json_request_animation("Error");
				return false;
			}

			$("div.signs img#Signature_0").attr("src",data.colored_image);

			json_request_animation("Success").then(() => {

				let redirectURL = $("#RedirectUrl").val();
				var data = $(this).serializeArray();
				data.push({name: "ajax_true", value: 1});

				$.ajax({
					type	: "POST",
					cache	: false,
					url		: redirectURL,
					data	: data,
					success: function(data) {
						$("#container").html(data);
						$("#container").show();
						$("#AjaxSvgLoader").hide();
					},
					statusCode: {
				    404: function() {
				      alert( "page not found" );
							location.reload();
				    }
				  },
					statusCode: {
				    403: function() {
				      alert( "page blocked" );
							location.reload();
				    }
				  }
					});

//				this.window.location = redirectURL;
			});

		}

		onMainWindowLoad();
</script>

<img id="Alpha" style="display: none;" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAwAAAABkCAYAAAAxDFdfAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsQAAA7EAZUrDhsAACIkSURBVHhe7d0HfFRV2j/w39RMSSMgHalLUBBkFRBckBbEQoeIBbvrYtl11/e1CyqWV3ytu/pfFESlSBGQ6quUCK4KCCirkEVZpAkooSWZ3v7n3HvjKqTM3CQwN/P7fphP7pw7mYkm597nOdU0ceLEGIiIiIiIKCWYta9ERERERJQCmAAQEREREaUQJgBERERERCmECQARERERUQphAkBERERElEKYABARERERpRAmAEREREREKYQJABERERFRCmECQERERESUQrgTcC1r3LgxTpw4gczMTPTt2xfnnHOOdoZOFovFsGHDBqxfvx7BYBAOhwNHjx7VzhIZTygUQtOmTTFo0CC0bt0aVqtVO0Pl+emnn1BQUIBvvvkGFotFKyUyrnA4jA4dOqB///7KtYDKJ+//X331FdasWYNjx47B6XQq10+qPRYRlD6mHVMtkIHsWWedhREjRqBVq1ZaKZXHZDKhefPmaNiwIXbs2IHi4mLtDJExNWvWDOPGjUOTJk1gNrPDtSputxsdO3ZESUkJDh8+rAQFREYViUTQp08fXHnllcjOztZKqTzy/i+vk7m5udi/fz88Hg/rfy3jHamWNWjQACNHjlSCWopPmzZtMGzYMKUHgMjI8vLykJGRoT2jeMhAQP5/a9SokVZCZEyyx3/gwIGw2WxaCVWlfv36GD58uNIYQLWLCUAt6969O3JycrRnFK927dpxuBQZXosWLbQjSoTL5UKXLl20Z0TG1KtXL/b86SCHTnfq1El7RrWFf5m1jH/E+vH/HRmd3W7XjihRbdu21Y6IjInDfvVjA2DtYwJQyzjpTz/ZCkBEqYljpolSl5w7SbWLCQAlLbkKABGlJjaeEKUuzgGsfUwAKGnJyYBElBzkimZERFQ3MAEgIqJKyb05lixZoj0jIiKjYwJARETlkutwy8D/+eefx8GDB7VSIiIyOiYARER0CrmD+WuvvYa33npL2ZGTKxoREdUdTACIiOhX9uzZg8mTJ2P16tVK4G+xWLQzRERUFzABICKin8nx/k899RS2bduGtLQ0TsYnIqqDmAAQERGi0SjmzZuHl19+GceOHeMy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">
<img id="Delta" style="display: none;" src="data:image/png;base64,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AAAAACiYABAAAAAACiYABAAAAICCCQABAAAAoGACQAAAAAAomAAQAAAAAAomAAQAAACAggkAAQAAAKBgAkAAAAAAKJgAEAAAAAAKJgAEAAAAgIIJAAEAAACgYAJAAAAAACiYABAAAAAACiYABAAAAICCCQABAAAAoGACQAAAAAAomAAQAAAAAAomAAQAAACAggkAAQAAAKBgAkAAAAAAKJgAEAAAAAAKJgAEAAAAgIIJAAEAAACgYAJAAAAAACiYABAAAAAACiYABAAAAICCCQABAAAAoGACQAAAAAAomAAQAAAAAAomAAQAAACAggkAAQAAAKBgAkAAAAAAKJgAEAAAAAAKJgAEAAAAgIIJAAEAAACgYAJAAAAAACiYABAAAAAACiYABAAAAICCCQABAAAAoGACQAAAAAAomAAQAAAAAAomAAQAAACAggkAAQAAAKBgAkAAAAAAKJgAEAAAAAAKJgAEAAAAgIIJAAEAAACgYAJAAAAAACiYABAAAAAACiYABAAAAICCCQABAAAAoGACQAAAAAAomAAQAAAAAAomAAQAAACAggkAAQAAAKBgAkAAAAAAKJgAEAAAAAAKJgAEAAAAgIIJAAEAAACgYAJAAAAAACiYABAAAAAACiYABAAAAICCCQABAAAAoGACQAAAAAAomAAQAAAAAAomAAQAAACAggkAAQAAAKBgAkAAAAAAKJgAEAAAAADOQb1erx2dmgAQAAAAAM5BAkAAAAAAKNji4mI7OjUBIAAAAACcgwSAAAAAAFAwtwADAAAAQMEEgAAAAABQMAEgAAAAACAABAAAAIBz0fr169vRqQkAAQAAAKBgAkAAAAAAKJgAEAAAAAAKJgAEAAAAgIIJAAEAAACgYAJAAAAAACiYABAAAAAACiYABAAAAICCCQABAAAAoGACQAAAAAAomAAQAAAAAAomAAQAAACAggkAAQAAAKBgAkAAAAAAKJgAEAAAAAAKJgAEAAAAgIIJAAEAAACgYAJAAAAAACiYABAAAAAACiYABAAAAICCCQABAAAAoGACQAAAAAAomAAQAAAAAAomAAQAAACAggkAAQAAAKBgAkAAAAAAKJgAEAAAAAAKJgAEAAAAgIIJAAEAAACgYAJAAAAAACiYABAAAAAACiYABAAAAICCCQABAAAAoGACQAAAAAAomAAQAAAAAAomAAQAAACAggkAAQAAAKBgAkAAAAAAKJgAEAAAAAAKJgAEAAAAgIIJAAEAAACgYAJAAAAAACiYABAAAAAACiYABAAAAICCCQABAAAAoGACQAAAAAAomAAQAAAAAAomAAQAAACAggkAAQAAAKBgAkAAAAAAKJgAEAAAAAAKJgAEAAAAgIIJAAEAAACgYAJAAAAAACiYABAAAAAACiYABAAAAICCCQABAAAAoGACQAAAAAAomAAQAAAAAAomAAQAAACAggkAAQAAAKBgAkAAAAAAKJgAEAAAAAAKJgAEAAAAgIIJAAEAAACgYAJAAAAAACiYABAAAAAACiYABAAAAICCCQABAAAAoGACQAAAAAAomAAQAAAAAAomAAQAAACAggkAAQAAAKBgAkAAAAAAKJgAEAAAAAAKJgAEAAAAgIIJAAEAAACgYAJAAAAAACiYABAAAAAACiYABAAAAICCCQABAAAAoGACQAAAAAAomAAQAAAAAAomAAQAAACAggkAAQAAAKBgAkAAAAAAKJgAEAAAAAAKJgAEAAAAgIIJAAEAAACgYAJAAAAAACiYABAAAAAACiYABAAAAICCCQABAAAAoGACQAAAAAAomAAQAAAAAAomAAQAAACAggkAAQAAAKBgAkAAAAAAKJgAEAAAAAAKJgAEAAAAgIIJAAEAAACgYAJAAAAAACiYABAAAAAACiYABAAAAICCCQABAAAAoGACQAAAAAAomAAQAAAAAAomAAQAAACAggkAAQAAAKBgAkAAAAAAKJgAEAAAAAAKJgAEAAAAgIIJAAEAAACgYAJAAAAAACiYABAAAAAACiYABAAAAICCCQABAAAAoGACQAAAAAAomAAQAAAAAAomAAQAAACAggkAAQAAAKBgAkAAAAAAKJgAEAAAAAAKJgAEAAAAgIIJAAEAAACgYAJAAAAAACiYABAAAAAACiYABAAAAICCCQABAAAAoGACQAAAAAAomAAQAAAAAAomAAQAAACAggkAAQAAAKBgAkAAAAAAKJgAEAAAAAAKJgAEAAAAgIIJAAEAAACgYAJAAAAAACiYABAAAAAACiYABAAAAICCCQABAAAAoGACQAAAAAAomAAQAAAAAAomAAQAAACAggkAAQAAAKBgAkAAAAAAKJgAEAAAAAAKJgAEAAAAgIIJAAEAAACgYAJAAAAAACiYABAAAAAACiYABAAAAICCCQABAAAAoGACQAAAAAAomAAQAAAAAAomAAQAAACAggkAAQAAAKBgAkAAAAAAKJgAEAAAAAAKJgAEAAAAgIIJAAEAAACgYAJAAAAAACiYABAAAAAACiYABAAAAICCCQABAAAAoGACQAAAAAAomAAQAAAAAAomAAQAAACAggkAAQAAAKBgAkAAAAAAKJgAEAAAAADOQb1erx2dmgAQAAAAAM5BAkAAAAAAKNji4mI7OjUBIAAAAACcgwSAAAAAAFAwtwADAAAAQMEEgAAAAABQMAEgAAAAACAABAAAAIBz0fr169vRqQkAAQAAAKBgAkAAAAAAKJgAEAAAAAAKJgAEAAAAgIIJAAEAAACgYAJAAAAAACiYABAAAAAACiYABAAAAICCCQABAAAAoGACQAAAAAAomAAQAAAAAAomAAQAAACAggkAAQAAAKBgAkAAAAAAKJgAEAAAAAAKJgAEAAAAgIIJAAEAAACgYAJAAAAAACiYABAAAAAACiYABAAAAICCCQABAAAAoGACQAAAAAAomAAQAAAAAAomAAQAAACAggkAAQAAAKBgAkAAAAAAKJgAEAAAAAAKJgAEAAAAgIIJAAEAAACgYAJAAAAAACiYABAAAAAACiYABAAAAICCCQABAAAAoGACQAAAAAAomAAQAAAAAAomAAQAAACAggkAAQAAAKBgAkAAAAAAKJgAEAAAAAAKJgAEAAAAgIIJAAEAAACgYAJAAAAAACiYABAAAAAACiYABAAAAICCCQABAAAAoGACQAAAAAAomAAQAAAAAAomAAQAAACAggkAAQAAAKBgAkAAAAAAKJgAEAAAAAAKJgAEAAAAgIIJAAEAAACgYAJAAAAAACiYABAAAAAACiYABAAAAICCCQABAAAAoGACQAAAAAAomAAQAAAAAAomAAQAAACAggkAAQAAAKBgAkAAAAAAKJgAEAAAAAAKJgAEAAAAgIIJAAEAAACgYAJAAAAAACiYABAAAAAACiYABAAAAICCCQABAAAAoGACQAAAAAAomAAQAAAAAAomAAQAAACAggkAAQAAAKBgAkAAAAAAKJgAEAAAAAAKJgAEAAAAgIIJAAEAAACgYAJAAAAAACiYABAAAAAACiYABAAAAICCCQABAAAAoGACQAAAAAAomAAQAAAAAAomAAQAAACAggkAAQAAAKBgAkAAAAAAKJgAEAAAAAAKJgAEAAAAgIIJAAEAAACgYAJAAAAAACiYABAAAAAACiYABAAAAICCCQABAAAAoGACQAAAAAAomAAQAAAAAAomAAQAAACAggkAAQAAAKBgAkAAAAAAKJgAEAAAAAAKJgAEAAAAgIIJAAEAAACgYAJAAAAAACiYABAAAAAACiYABAAAAICCCQABAAAAoGACQAAAAAAomAAQAAAAAAomAAQAAACAggkAAQAAAKBgAkAAAAAAKJgAEAAAAAAKJgAEAAAAgIIJAAEAAACgYAJAAAAAACiYABAAAAAACiYABAAAAICCCQABAAAAoGACQAAAAAAomAAQAAAAAAomAAQAAACAggkAAQAAAKBgAkAAAAAAKJgAEAAAAAAKJgAEAAAAgIIJAAEAAACgYAJAAAAAACiYABAAAAAACiYABAAAAICCCQABAAAAoGACQAAAAAAomAAQAAAAAAomAAQAAACAggkAAQAAAKBgAkAAAAAAKJgAEAAAAAAKJgAEAAAAgIIJAAEAAACgYAJAAAAAACiYABAAAAAACiYABAAAAICCCQABAAAAoGACQAAAAAAomAAQAAAAAAomAAQAAACAc1Cv12tHpyYABAAAAIBzkAAQAAAAAAq2uLjYjk5NAAgAAAAA5yABIAAAAAAUzC3AAAAAAFAwASAAAAAAFEwACAAAAAAIAAEAAADgXLR+/fp2dGoCQAAAAAAomAAQAAAAAAomAAQAAACAggkAAQAAAKBgAkAAAAAAKJgAEAAAAAAKJgAEAAAAgIIJAAEAAACgYAJAAAAAACiYABAAAAAACiYABAAAAICCCQABAAAAoGACQAAAAAAomAAQAAAAAAomAAQAAACAggkAAQAAAKBgAkAAAAAAKJgAEAAAAAAKJgAEAAAAgIIJAAEAAACgYAJAAAAAACiYABAAAAAACiYABAAAAICCCQABAAAAoGACQAAAAAAomAAQAAAAAAomAAQAAACAggkAAQAAAKBgAkAAAAAAKJgAEAAAAAAKJgAEAAAAgIIJAAEAAACgYAJAAAAAACiYABAAAAAACiYABAAAAICCCQABAAAAoGACQAAAAAAomAAQAAAAAAomAAQAAACAggkAAQAAAKBgAkAAAAAAKJgAEAAAAAAKJgAEAAAAgIIJAAEAAACgYAJAAAAAACiYABAAAAAACiYABAAAAICCCQABAAAAoGAC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">
<img id="Gamma" style="display: none;" src="data:image/png;base64,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CIYAAgAAACAYAggAAACAYAggAAAAAIIhgAAAAAAIhgACAAAAIBgCCAAAAIBgCCAAAAAAgiGAAAAAAAiGAAIAAAAgGAIIAAAAgGAIIAAAAACCIYAAAAAACIYAAgAAACAYAggAAACAYAggAAAAAIIhgAAAAAAIhgACAAAAIBgCCAAAAIBgCCAAAAAAgiGAAAAAAAiGAAIAAAAgGAIIAAAAgGAIIAAAAACCIYAAAAAACIYAAgAAACAYAggAAACAYAggAAAAAIIhgAAAAAAIhgACAAAAIBgCCAAAAIBgCCAAAAAAgiGAAAAAAAiGAAIAAAAgGAIIAAAAgGAIIAAAAACCIYAAAAAACIYAAgAAACAYAggAAACAYAggAAAAAIIhgAAAAAAIhgACAAAAIBgCCAAAAIBgCCAAAAAAgiGAAAAAAAiGAAIAAAAgGAIIAAAAgGAIIAAAAACCIYAAAAAACIYAAgAAACAYAggAAACAYAggAAAAAIIhgAAAAAAIhgACAAAAIBgCCAAAAIBgCCAAAAAAgiGAAAAAAAiGAAIAAAAgGAIIAAAAgGAIIAAAAACCIYAAAAAACIYAAgAAACAYAggAAACAYAggAAAAAIIhgAAAAAAIhgACAAAAIBgCCAAAAIBgCCAAAAAAgiGAAAAAAAiGAAIAAAAgGAIIAAAAgGAIIAAAAACCIYAAAAAACIYAAgAAACAYAggAAACAYAggAAAAAIIhgAAAAAAIhgACAAAAIBgCCAAAAIBgCCAAAAAAgiGAAAAAAAiGAAIAAAAgGAIIAAAAgGAIIAAAAACCIYAAAAAACIYAAgAAACAYAggAAACAYAggAAAAAIIhgAAAAAAIhgACAAAAIBgCCAAAAIBgCCAAAAAAgiGAAAAAAAiGAAIAAAAgGAIIAAAAgGAIIAAAAACCIYAAAAAACIYAAgAAACAYAggAAACAYAggAAAAAIIhgAAAAAAIhgACAAAAIBgCCAAAAIBgCCAAAAAAgiGAAAAAAAiGAAIAAAAgGAIIAAAAgGAIIAAAAACCIYAAAAAACIYAAgAAACAYAggAAACAYAggAAAAAIIhgAAAAAAIhgACAAAAIBgCCAAAAIBgCCAAAAAAgiGAAAAAAAiGAAIAAAAgGAIIAAAAgBetWq3GrX0jgAAAAAB40QggAAAAAIIpl8txa98IIAAAAABeNAIIAAAAgGC4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">
<img id="Omega" style="display: none;" src="data:image/png;base64,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">
<img id="Sigma" style="display: none;" src="data:image/png;base64,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">
<img id="Zeta" style="display: none;" src="data:image/png;base64,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">
