/*************************************************************************
*	Package: HTJS v1.0.0
*	Author: Bilal Iqbal
*************************************************************************/

/******************************* All TODOS ********************************

-------- High Priority ----------
- Improve regex to fetch specific _this. values
- Check for all reserved variables and prevent them from using in outside world
- Check if deep loop and multiple cloning then do child first than parent
- lazy loader
- Add functionality to prevent ajax multiple times or disable handler to send multiple ajax calls
- Remove extra function which is fetching proper attribute and than find element. As this can be done by default
- Set .data()
- Return proper compiled template after compilation

-------- Medium Priority ----------
- Apply else condition

-------- Low Priority -------------

***************************************************************************/

if (!XMLHttpRequest.prototype.sendAsBinary) {
  XMLHttpRequest.prototype.sendAsBinary = function(sData) {
    var nBytes = sData.length, ui8Data = new Uint8Array(nBytes);
    for (var nIdx = 0; nIdx < nBytes; nIdx++) {
      ui8Data[nIdx] = sData.charCodeAt(nIdx) & 0xff;
    }
    /* send as ArrayBufferView...: */
    this.send(ui8Data);
    /* ...or as ArrayBuffer (legacy)...: this.send(ui8Data.buffer); */
  };
}

(function(window, undefined){
	(function(){

		// Creating HTTP Ajax Class
		var HtjsAjax = function(options, element){
			return this.init(options, element);
		};

		HtjsAjax.prototype = {
			init: function(options, element){
				element = element || null;
				this.config = htjs.extends({
					url: "",
					element: element,
					method: "GET",
					contentType: "",
					responseType: "",
					data: {},
					target: "",
					templates: [],
					eventListener: {},
					progressBar: "",
					beforeSubmit: "",
					cache: false,					// Works with GET request only
					async: false,
					debug: "alert",					// Debug option "log", "alert", "false"
					preTemplatesCallback: "",
					callback: "",

				}, options);

				this.updateOptions();
				this.verifyParameters();

				this.generateRequest();
			},

			verifyParameters: function(){
				var allowedMethods = ["GET", "POST", "DELETE", "PUT", "PATCH"];
				if(htjs.inArray(this.config.method.toUpperCase(), allowedMethods) == -1)
					throw new Error("Invalid HTTP method, using " + this.config.method + ". Allowed methods are " + allowedMethods.join(", "));
			},

			updateOptions: function(){
				if(this.config.element != null)
					this.config = updateObjByAttr(this.config.element, this.config, 'ht-ajax');

				// Updating settings for form and Http request
				if(this.config.element != null && this.config.element.get().nodeName.toUpperCase() == "FORM"){
					this.form = this.config.element.get();
					this.config.url = (htjs.getLength(this.form.action) == 0) ? this.config.url : this.form.action;
				} else {
					this.form = false;
					this.config.url = (htjs.getLength(this.config.url) > 0) ? this.config.url : this.config.element.attr('href');
				}

				// Setting default URL - If http present in given config object or href or action of form than that will be used otherwise will be append at the end of global url.
				if(htjs.getLength(htjs.defaultUrl) > 0)
					this.config.url = (this.config.url.indexOf("http") > -1) ? this.config.url : htjs.defaultUrl + this.config.url;

				if(this.form != false){
					this.config.method = (htjs.getLength(this.form.method) == 0) ? this.config.method : this.form.method;

				} else {
					if(this.config.method.toUpperCase() == "GET"){
						this.config.url += (this.config.cache) ? ((this.config.url.indexOf("?") > -1) ? "&" : "?") + (new Date()).getTime() : "";
						if(htjs.getLength(this.config.data) > 0)
							this.config.url = htjs.params(this.config.data, this.config.url);
					}
				}
			},

			generateRequest: function(){

			    this.request = new XMLHttpRequest();
			    var object = this;

			    if(object.form !== false){
			    	object.config.element.get().onsubmit = function(e) {
			    		object.progressBar(object);
			    		e.preventDefault();
			    		object.prepareFormRequest(object);
			    	}

		    	} else {
					// Setting response type
					if(htjs.getLength(object.config.responseType) > 0)
						object.request.responseType = object.config.responseType;

					if(this.config.element == null){
						object.triggerRequestWithPromise(object);

					} else {
					    object.config.element.get().onclick = function(e){
					    	e.preventDefault();
							object.triggerRequestWithPromise(object);
						};
					}
				}
			},

			triggerRequestWithPromise: function(object){
				object = object || this;
				new Promise(function(resolve, reject) {
					var req = object.request;
					object.progressBar(object);
					object.request.open(object.config.method, object.config.url, object.config.async);

					req.onload = function() {
						if (req.status == 200) {
							resolve(object);
						} else {
							reject(object);
						}
					};

					// Handle network errors
					req.onerror = function() {
						reject(object);
					};

					// Make the request
					if(object.config.method.toUpperCase() == "GET"){
						object.request.send();

					} else {
						object.request.setRequestHeader("Content-Type", (htjs.getLength(object.config.contentType) > 0) ? object.config.contentType : "application\/x-www-form-urlencoded");
						object.request.send(htjs.params(object.config.data));
					}

					// Binding functions
				}).then(function(object) {
					object.success(object);

				}, function(object) {
					object.abort(object);
					object.error(object);

				});
			},

			prepareFormRequest: function(object){
				var request = {};
				var oTarget = object.form;

		        var nFile, sFieldType, oField, oSegmReq, oFile, bIsPost = object.config.method.toLowerCase() === "post";
			    /* console.log("AJAXSubmit - Serializing form..."); */
				request.contentType = bIsPost && oTarget.enctype ? oTarget.enctype : "application\/x-www-form-urlencoded";
				request.technique = bIsPost ? request.contentType === "multipart\/form-data" ? 3 : request.contentType === "text\/plain" ? 2 : 1 : 0;
				request.receiver = object.config.url;
				request.status = 0;
				request.segments = [];
			    var fFilter = request.technique === 2 ? object.plainEscape : escape;
			    for (var nItem = 0; nItem < oTarget.elements.length; nItem++) {
			      oField = oTarget.elements[nItem];
			      if (!oField.hasAttribute("name")) { continue; }
			      sFieldType = oField.nodeName.toUpperCase() === "INPUT" ? oField.getAttribute("type").toUpperCase() : "TEXT";
			      if (sFieldType === "FILE" && oField.files.length > 0) {
			        if (request.technique === 3) {
			          /* enctype is multipart/form-data */
			          for (nFile = 0; nFile < oField.files.length; nFile++) {
			            oFile = oField.files[nFile];
			            oSegmReq = new FileReader();
			            /* (custom properties:) */
			            oSegmReq.segmentIdx = request.segments.length;
			            oSegmReq.owner = request;
			            /* (end of custom properties) */
                        //oSegmReq.abort = function(){ object.abort(object); };
						//oSegmReq.error = function(){ object.error(object); };
						//oSegmReq.onload = function(){ object.success(object); };
			            oSegmReq.onload = function(evt){ object.pushSegment(this, evt, object); };
						request.segments.push("Content-Disposition: form-data; name=\"" + oField.name + "\"; filename=\""+ oFile.name + "\"\r\nContent-Type: " + oFile.type + "\r\n\r\n");
						request.status++;
			            oSegmReq.readAsBinaryString(oFile);
			          }
			        } else {
			          /* enctype is application/x-www-form-urlencoded or text/plain or method is GET: files will not be sent! */
			          for (nFile = 0; nFile < oField.files.length; request.segments.push(fFilter(oField.name) + "=" + fFilter(oField.files[nFile++].name)));
			        }
			      } else if ((sFieldType !== "RADIO" && sFieldType !== "CHECKBOX") || oField.checked) {
			        /* field type is not FILE or is FILE but is empty */
					  request.segments.push(
						request.technique === 3 ? /* enctype is multipart/form-data */
			            "Content-Disposition: form-data; name=\"" + oField.name + "\"\r\n\r\n" + oField.value + "\r\n"
			          : /* enctype is application/x-www-form-urlencoded or text/plain or method is GET */
			            fFilter(oField.name) + "=" + fFilter(oField.value)
			        );
			      }
			    }
			    object.processStatus(request, object);

			},

			pushSegment: function(xhr, oFREvt, object) {
				xhr.owner.segments[xhr.segmentIdx] += oFREvt.target.result + "\r\n";
				xhr.owner.status--;
				object.processStatus(xhr.owner, object);
			},

			plainEscape: function(sText) {
				/* how should I treat a text/plain form encoding? what characters are not allowed? this is what I suppose...: */
				/* "4\3\7 - Einstein said E=mc2" ----> "4\\3\\7\ -\ Einstein\ said\ E\=mc2" */
				return sText.replace(/[\s\=\\]/g, "\\$&");
			},

			processStatus: function(request, object) {
				if (request.status > 0) { return; }
				/* the form is now totally serialized! do something before sending it to the server... */
				if(object.config.beforeSubmit.length > 0){
					htjs.setCallback(object.config.beforeSubmit, [object.form, request]);
				}

				object.submitData(request, object);
			},

			submitData: function(oData, object) {
				var oAjaxReq = object.request;
				oAjaxReq.submittedData = oData;
				// Setting response type
				if(htjs.getLength(object.config.responseType) > 0)
					oData.responseType = object.config.responseType;
				oAjaxReq.abort = function(){ object.abort(object); };
				oAjaxReq.error = function(){ object.error(object); };
				oAjaxReq.onload = function(){ object.success(object); };
				if (oData.technique === 0) {
				  /* method is GET */
				  oAjaxReq.open("get", object.config.url.replace(/(?:\?.*)?$/, oData.segments.length > 0 ? "?" + oData.segments.join("&") : ""), object.config.async);
					// Managing cache
					if(!object.config.cache){
						oAjaxReq.setRequestHeader("Pragma", "no-cache");
						oAjaxReq.setRequestHeader("Cache-Control", "no-cache");
					}
				  oAjaxReq.send(null);
				} else {
				  /* method is POST */
				  oAjaxReq.open("post", oData.receiver, object.config.async);
					// Managing cache
					if(!object.config.cache){
						oAjaxReq.setRequestHeader("Pragma", "no-cache");
						oAjaxReq.setRequestHeader("Cache-Control", "no-cache");
					}
				  if (oData.technique === 3) {
				    /* enctype is multipart/form-data */
				    var sBoundary = "---------------------------" + Date.now().toString(16);
				    oAjaxReq.setRequestHeader("Content-Type", "multipart\/form-data; boundary=" + sBoundary);
				    oAjaxReq.sendAsBinary("--" + sBoundary + "\r\n" + oData.segments.join("--" + sBoundary + "\r\n") + "--" + sBoundary + "--\r\n");
				  } else {
				    /* enctype is application/x-www-form-urlencoded or text/plain */
				    oAjaxReq.setRequestHeader("Content-Type", (htjs.getLength(object.config.contentType) > 0) ? object.config.contentType : oData.contentType);
				    oAjaxReq.send((htjs.getLength(object.config.data) > 0) ? htjs.params(object.config.data) : (oData.segments.join(oData.technique === 2 ? "\r\n" : "&")));
				  }
				}
			},

			progressBar: function(obj){
				if(!htjs.isElement(obj.config.progressBar))
					return;

				obj.addListener("progress", function(oEvent){
					if (oEvent.lengthComputable) {
					    var percentComplete = oEvent.loaded / oEvent.total;
					    htjs(obj.config.progressBar).removeClass("ht-error ht-abort").addClass("ht-in-progress").css({width: percentComplete + "%"}).html(percentComplete + "%");
					} else {
					    htjs(obj.config.progressBar).attr({width: "100%"}).html("Working");
					}
				}, [], obj);

				obj.addListener("loadstart", function(oEvent){
				    htjs(obj.config.progressBar).attr({width: "0%"}).html("");
				}, [], obj);

				obj.addListener("load", function(oEvent){
				    htjs(obj.config.progressBar).removeClass("ht-error ht-in-progress ht-abort").addClass("ht-completed").css({width: "100%"}).html("100%");
				}, [], obj);

				obj.addListener("abort", function(oEvent){
				    htjs(obj.config.progressBar).removeClass("ht-error ht-in-progress").addClass("ht-abort").css({width: "100%"}).html("Cancelled");
				}, [], obj);

				obj.addListener("error", function(oEvent){
				    htjs(obj.config.progressBar).removeClass("ht-abort ht-in-progress").addClass("ht-error").css({width: "100%"}).html("Failed");
				}, [], obj);
			},

			// TODO - update this function properly with args
			addListener: function(event, listener, args, object){
				args = args || [];
				object = object || null;
				var request = this.request;
				if(object != null)
					request = object.request;
				request.addEventListener(event, listener);
			},

			getRequest: function(){
				return this.request;
			},

			abort: function(obj){
				var request = obj.request,
					response = request.response;
				if(htjs.isValidJson(response))
					response = htjs.parseJson(response);
				// Setting callback function
				htjs.setCallback(obj.config.callback, ['abort', response, request.status, obj.config.element, request]);
				obj.debugResponse(obj);
			},

			error: function(obj){
				var request = obj.request,
					response = request.response;
				if(htjs.isValidJson(response))
					response = htjs.parseJson(response);
				// Setting callback function
				htjs.setCallback(obj.config.callback, ['error', response, request.status, obj.config.element, request]);
				obj.debugResponse(obj);
			},

			success: function(obj){
				var request = obj.request,
					response = request.response;
				if(htjs.isValidJson(response))
					response = htjs.parseJson(response);

				if(isObject(obj.config.templates) && htjs.getLength(obj.config.templates) > 0){
					if(isFunction(obj.config.preTemplatesCallback) || htjs.getLength(obj.config.preTemplatesCallback) > 0) {
						response = htjs.setCallback(obj.config.preTemplatesCallback, [response]);
					}
					htjs.each(obj.config.templates, function(responseObjKey, templatesObj){
						htjs.each(templatesObj, function(i, templateObj){
							var responseObj = ((responseObjKey == 0) ? response : response[responseObjKey]),
								template = templateObj.template,
								target = templateObj.target,
								clearTarget = isUndefined(templateObj.clearTarget) ? true : templateObj.clearTarget,
								noRecordMsg = isUndefined(templateObj.noRecordMsg) ? "" : templateObj.noRecordMsg,
								templateCallback = templateObj.callback,
								beforeCallback = templateObj.beforeCallback;

							if(isObject(responseObj) && htjs.getLength(responseObj) > 0){
								var compiledTemplate = htjs.compileTemplate(template, responseObj, target, noRecordMsg, clearTarget);
								if(isFunction(templateCallback) || htjs.getLength(templateCallback) > 0)
									htjs.setCallback(templateCallback, [compiledTemplate, responseObj]);
							}
						});
					});
				}
				if(htjs.getLength(obj.config.target) > 0)
					htjs(obj.config.target).html(response);
				// Setting callback function
				htjs.setCallback(obj.config.callback, ['success', response, request.status, obj.config.element, request]);
			},

			debugResponse: function(obj){
				var response = obj.request;
				if(response.status != 200){
					if(obj.config.debug == "log"){
						console.log("Code: " + response.status, "Response message: " + response.statusText);
					} else if (obj.config.debug == "alert"){
						alert("Code: " + response.status, "Response message: " + response.statusText);
					}
				}
			},

		};

		// Creating HTJS Infinite Scroll Class
		function HtjsInfiniteScroll(element, options){
			return this.init(element, options);
		}

		HtjsInfiniteScroll.prototype = {
			init: function(element, options){
				if(element == null)
					throw new Error("Invalid element");

				this.config = htjs.extends({
					element: element,
					template: "",		// HTJS template
					ajaxLoad: true,		// this will verify if data must be fetch using ajax or all data provided
					endpoint: "",		// will be used if ajaxLoad = true
					method: "GET",
					perpage: 10,		// records to show per page
					autoLoad: true,		// will automatically load data if set to true other wise load data when Clicking loadMoreBtn
					loadMoreBtn: "",
					loaderImg: "",
					loadingMsg: "Loading...",
					scrollOffset: 200,	// pixels before which ajax request must send

					// Messages
					noRecordMsg: "No record found",
					noMoreRecordsMsg: "No records found anymore.",

					// Callback functions
					beforeAjax: "",
					success: "",
					error: "",
					abort: ""

				}, options);

				this.config = updateObjByAttr(this.config.element, this.config);

				this.loader();
			},

			loader: function(){
				this.fetchData();

			},

			fetchData: function(){
				if(this.config.ajaxLoad){
					if(htjs.getLength(this.config.endpoint) == 0)
						throw new Error("Endpoint required to fetch data.");

					htjs.ajax({
						url: this.config.endpoint,
						method: this.config.method,
						contentType: "application/json",
						data: {
							o: this.config.offset,
							perpage: this.config.perpage
						},
						success: function(response, status, xhr){

						},
						error: function(xhr){

						}
					});
				} else {

				}
			},

			success: function(response, status, xhr){

			},

			error: function(status, xhr){

			},

			abort: function(status, xhr){

			},

		};

		// Creating HTJS Element Class
		var HtjsElem = function(element){
			if(isElement(element))
				return this.init(element);
		}

		HtjsElem.prototype = {
			init: function(element){
				if(!htjs.isElement(element))
					return false;
				this.el = element;
				return this;
			},

			get: function(index){
				index = (isUndefined(index)) ? "" : index;
				return (isNull(this.el[index])) ? this.el : this.el[index];
			},

			find: function(selector){
				if(this.getLength() == 0)
					return [];
				var el = (this.getLength() > 1) ? this.get(0) : this.el;
				return htjs(el.querySelectorAll(selector));
			},

			hide: function(){
				if(this.getLength() > 1) {
					htjs.each(this.el, function (i, el) {
						htjs(el).hide();
					});
				} else {
					this.el.style.display = "none";
				}
				return this;
			},

			show: function(){
				if(this.getLength() > 1) {
					htjs.each(this.el, function (i, el) {
						htjs(el).show();
					});
				} else {
					this.el.style.display = null;
				}
				return this;
			},

			addClass: function(classes){
				var htjsObj = this;
				if(htjsObj.getLength() > 1) {
					htjs.each(htjsObj.el, function (i, el) {
						htjs(el).addClass(classes);
					});
				} else {
					classes = classes.split(" ");
					htjs.each(classes, function (i, e) {
						if (e.length > 0)
							htjsObj.el.classList.add(e.trim());
					});
				}
				return htjsObj;
			},

			removeClass: function(classes){
				var htjsObj = this;
				if(htjsObj.getLength() > 1) {
					htjs.each(htjsObj.el, function (i, el) {
						htjs(el).addClass(classes);
					});
				} else {
					if(!isElement(htjsObj.el) || isEmpty(htjsObj.el))
						return null;
					classes = classes.split(" ");
					htjs.each(classes, function (i, e) {
						if (e.length > 0)
							htjsObj.el.classList.remove(e);
					});
				}
				return htjsObj;
			},

			toggleClass: function(className, force){
				if(htjsObj.getLength() > 1) {
					htjs.each(htjsObj.el, function (i, el) {
						htjs(el).toggle(className, force);
					});
				} else {
					this.el.classList.toggle(className, force);
				}
				return this;
			},

			hasClass: function(className){
				var el = (this.getLength() > 1) ? this.get(0) : this.el;
				return el.classList.contains(className);
			},

			html: function(html){
				html = html || null;
				var el = (this.getLength() > 1) ? this.get(0) : this.el;
				if(html === null)
					return (el.nodeType == Node.ELEMENT_NODE) ? el.innerHTML : null;
				else
					el.innerHTML = html;
				return this;
			},

			text: function(text){
				text = text || null;
				var el = (this.getLength() > 1) ? this.get(0) : this.el;
				if(text === null)
					return (el.nodeType == Node.TEXT_NODE) ? el.textContent : ((el.nodeType == Node.ELEMENT_NODE) ? el.innerText : null);
				else
					el.innerText = text;
				return this;
			},

			clone: function(deep){
				deep = deep || true;
				var el = (this.getLength() > 1) ? this.get(0) : this.el;
				return el.cloneNode(deep);
			},

			insertBefore: function(insertEl){
				var parent = htjs(insertEl).parent();
				var el = (this.getLength() > 1) ? this.get(0) : this.el;
				return parent.insertBefore(parent, el);
			},

			parent: function(){
				var el = (this.getLength() > 1) ? this.get(0) : this.el;
				return el.parentNode;
			},

			remove: function(){
				var htjsObj = this;
				if(htjsObj.getLength() > 1) {
					htjs.each(htjsObj.el, function (i, el) {
						htjs(el).remove();
					});
				} else {
					this.parent().removeChild(this.el);
				}
			},

			getLength: function(){
				// TODO - Make it proper, issue is element is returning count of its direct childs
				return (this.el.nodeType == Node.ELEMENT_NODE) ? 1 : this.el.length;
			},

			css: function(name, value){
				var htjsObj = this;
				value = value || null;
				if(htjsObj.getLength() > 1) {
					htjs.each(htjsObj.el, function (i, el) {
						htjs(el).css(name, value);
					});
				} else {
					if (value == null && typeof name != "object")
						return htjsObj.el.getAttribute(name);
					else {
						if (typeof name == "object") {
							htjs.each(name, function (i, e) {
								htjsObj.css(i, e);
							});
						} else {
							htjsObj.el.style[name] = value;
						}
					}
					return htjsObj;
				}
			},

			getAttributes: function(){
				var el = (this.getLength() > 1) ? this.get(0) : this.el;
				return el.attributes;
			},

			attr: function(name, value) {
				var htjsObj = this;
				value = value || null;
				if(htjsObj.getLength() > 1) {
					htjs.each(htjsObj.el, function (i, el) {
						htjs(el).attr(name, value);
					});
				} else {
					if (value == null && typeof name != "object"){
						return htjsObj.el.getAttribute(name);
					} else {
						if (typeof name == "object") {
							htjs.each(name, function (i, e) {
								htjsObj.attr(i, e);
							});
						} else {
							htjsObj.el.setAttribute(name, value);
						}
					}
					return htjsObj;
				}
			},

			hasAttr: function(name){
				var el = (this.getLength() > 1) ? this.get(0) : this.el;
				return el.hasAttribute(name);
			},

			val: function(value){
				value = value || null;
				var el = (this.getLength() > 1) ? this.get(0) : this.el;
				if(isNull(value))
					return el.value;
				else
					el.value = value;
			},

			removeAttr: function(name){
				var el = (this.getLength() > 1) ? this.get(0) : this.el;
				el.removeAttribute(name);
	     		return this;
			},

			data: function(name, value){
				var htjsObj = this;
				value = value || "";
				if(htjsObj.getLength() > 1) {
					htjs.each(htjsObj.el, function (i, el) {
						htjs(el).data(name, value);
					});
				} else {
					if (htjs.getLength(value) > 0)
						this.el.setAttribute(name, value);
					else
						this.el.getAttribute(name);
					return this;
				}
			},

			hasData: function(name){
				var el = (this.getLength() > 1) ? this.get(0) : this.el;
				return el.hasAttribute(name);
			},

			removeData: function(name){
				var el = (this.getLength() > 1) ? this.get(0) : this.el;
				el.removeAttribute(name);
				return this;
			},

			removeWrapper: function(){
				var htjsObj = this;
				if(htjsObj.getLength() > 1) {
					htjs.each(htjsObj.el, function (i, el) {
						htjs(el).removeWrapper();
					});
				} else {
					while (this.el.firstChild) {
						this.el.parentNode.insertBefore(this.el.firstChild, this.el);
					}
					this.el.parentNode.removeChild(this.el);
				}
			},

			append: function(child){
				var htjsObj = this;
				if(htjsObj.getLength() > 1) {
					htjs.each(htjsObj.el, function (i, el) {
						htjs(el).append(child);
					});
				} else {
					child = (htjs.isValidInstance(child)) ? child.get() : child;
					return this.el.appendChild(child);
				}
			},

			children: function(){
				return htjs(this.el.childNodes);
			},

			// TODO - update later
			closest: function (selector) {
				var el = (this.getLength() > 1) ? this.el.get(0) : this.el;
				var matchesFn;

				// find vendor prefix
				['matches','webkitMatchesSelector','mozMatchesSelector','msMatchesSelector','oMatchesSelector'].some(function(fn) {
					if (typeof document.body[fn] == 'function') {
						matchesFn = fn;
						return true;
					}
					return false;
				});
				var parent;
				// traverse parents
				while (el) {
					parent = el.parentElement;
					if (parent && parent[matchesFn](selector)) {
						return htjs(parent);
					}
					el = parent;
				}
				return null;
			}
		};

		function updateObjByAttr(el, object, prefix) {
			prefix = prefix || "";
			el = (htjs.isValidInstance(el)) ? el : htjs(el);
			htjs.each(object, function(i, e){
				var attrName = (prefix.length > 0) ? (prefix + '-' + i) : i;
				object[i] = (el.hasAttr(attrName)) ? el.attr(attrName) : ((el.hasAttr("data-" + attrName)) ? el.attr("data-" + attrName) : e);
			});
			return object;
		}

		var htjs = function(context){
			// TODO - Set false in return if element is invalid
			if(context.length == 0)
				return [];

			if(context instanceof HtjsElem)
				return context;

			if(typeof context == "string")
				context = document.querySelectorAll(context);

			if(typeof context == "undefined")
				throw new Error("Invalid context");


			if(typeof context == "object" && htjs.getLength(context) > 0){
				if(!NodeList.prototype.isPrototypeOf(context)) {
					return new HtjsElem(context);
				} else if(NodeList.prototype.isPrototypeOf(context) && htjs.getLength(context) == 1){
					return new HtjsElem(context.item(0));
				} else {
					return new HtjsElem(context);
				}
			} else {
				return new HtjsElem(context);
			}

		};

		// Extending htjs, to extends HtjsElem plugins functionality
		htjs.fn = HtjsElem.prototype;

		// HTJS Global vars
		htjs.defaultUrl = (typeof htjs_globalAppUrl != 'undefined') ? htjs_globalAppUrl : "";

		htjs.isValidInstance = function(element){
			return (element instanceof HtjsElem);
		};

		htjs.params = function(object, url){
			url = url || null;
			var params = Object.keys(object).map(function(k) {
				return encodeURIComponent(k) + "=" + encodeURIComponent(object[k]);
			}).join('&');
			if(url != null)
				params = url + ((url.indexOf("?") == -1) ? "?" : "&") + params;
			return params;
		};

		htjs.array_unique = function(array){
			var obj = {}, i, l = array.length, result = [];
		    for(i=0; i<l;i+=1) obj[array[i]] = array[i];
		    for(i in obj) result.push(obj[i]);
		    return result;
		}

		htjs.isValidJson = function(obj){
			try{
				if(typeof obj == 'object')
					return true;
				JSON.parse(obj);
			} catch (e){
				return false;
			}
			return true;
		}

		htjs.parseJson = function(obj){
			if(htjs.isValidJson(obj)){
				return JSON.parse(obj);
			} else {
				return false;
			}
		};

		htjs.inArray = function(el, array){
			return (el == null) ? -1 : array.indexOf( el );
		};

		htjs.getLength = function(el){
			var length = 0;
			if(el == null)
				return length;
			if(el.nodeType){
				length = 1;

			} else if(typeof el == 'string'){
				length = el.length;

			} else if(typeof el == 'array'){
				length = el.length;

			} else if(typeof el == 'object'){
				length = Object.keys(el).length;
			}
			return length;
		};

		htjs.isElement = function(element) {
			return (element.nodeType != undefined || element instanceof NodeList);
		};

		htjs.getKey = function(object, value) {
			value = value || "";
			var keys = Object.keys(object);
			return (value.length > 0) ? keys[value] : keys;
		};

		htjs.getValue = function(path, object) {
			path = (typeof path == "object") ? path : path.split('.');
			var index = 0,
				length = path.length;
			if(htjs.getLength(object) == 0)
				return null;
			while(length > index){
				object = object[path[index++]];
			}
			return object;
		};

		htjs.replaceArray = function(replaceString, find, replace) {
			var regex; 
			for (var i = 0; i < find.length; i++) {
				if(typeof replace == 'string') {
					replaceString = replaceString.replace(regex, replace);
				} else {
					regex = (find[i] instanceof RegExp) ? find[i] : new RegExp(find[i], "g");
					replaceString = replaceString.replace(regex, replace[i]);
				}
			}
			return replaceString;
		};

		htjs.each = function( obj, callback ){
			var length, i = 0;
			if(typeof obj == 'array' || isElement(obj)){
				length = obj.length;
				for (; i < length; i++){
					if (callback.call(obj[i], i, obj[i]) === false){
						break;
					}
				}
			} else {
				for(i in obj){
					if(callback.call(obj[i], i, obj[i]) === false){
						break;
					}
				}
			}
			return obj;
		};

		htjs.extends = function(obj1, obj2){
		    var obj3 = {};
		    for (var attrname in obj1) { obj3[attrname] = obj1[attrname]; }
		    for (var attrname in obj2) { obj3[attrname] = obj2[attrname]; }
		    return obj3;
		};

		htjs.deepExtend = function(obj1, obj2){
		    var obj3 = {};
		    for (var attrname in obj1) { obj3[attrname] = isObject(obj1[attrname]) ? htjs.deepExtend(obj1[attrname], obj2[attrname]) : obj1[attrname]; }
		    for (var attrname in obj2) { obj3[attrname] = isObject(obj2[attrname]) ? htjs.deepExtend(obj1[attrname], obj2[attrname]) : obj2[attrname]; }
		    return obj3;
		};

		htjs.setCallback = function(funcName, args, call){
			args = args || [];
			call = call || false;
			var func = (isFunction(funcName)) ? funcName : window[funcName];
			if (isFunction(func)) {
				var returnValue = func.apply(func, args);
				return returnValue;
			}
		};

		htjs.ajax = function(options, element){
			element = element || null;
			if(element == null){
				return new HtjsAjax(options);
			}
			var requests = {};
			element = (htjs.isValidInstance(element)) ? element : htjs(element);
			if(htjs.isValidInstance(element))
				element = [element];
			for(var e = 0; e < htjs.getLength(element); e++){
				var el = element[e];
				// requests[(new Date()).getTime()] = new HtjsAjax(element[e].get(), url, options);
				requests[(new Date()).getTime()] = new HtjsAjax(options, el);
			}
			return requests;
		};

		htjs.infiniteScroll = function(element, options){
			var requests = {};
			element = (htjs.isValidInstance(element)) ? element : htjs(element);
			if(htjs.isValidInstance(element))
				element = [element];
			for(var e = 0; e < htjs.getLength(element); e++){
				var el = element[e];
				requests[(new Date()).getTime()] = new HtjsInfiniteScroll(el, options);
			}
			return requests;
		};

		return (window.htjs = htjs);

	}());
}(window));

/************************************************************************
 * HTJS Helper Functions
 ************************************************************************/
function isNull(value) { return value == null }
function isEmpty(value) { return isNull(value) || (htjs.getLength(value) == 0) }
function isFunction(value) { return typeof value == "function" }
function isUndefined(value) { return typeof value == "undefined" }
function isObject(value) { return typeof value == "object" }
function isArray(value) { return typeof value == "array" }
function isString(value) { return typeof value == "string" }
function isBoolean(value) { return typeof value == "boolean" }
function isElement(element) {
	var returnValue;
	try{
		returnValue = element instanceof NodeList || htjs.isValidInstance(element);
		returnValue = (returnValue === true) ? true : element.nodeType;
	} catch (err){
		returnValue = false;
	}
	return returnValue;
}

/************************************************************************
* HTJS Closure which is not accessible from outside world to maintain
* encapsulation. 
************************************************************************/
(function(){
	// Making script Strict to prevent any unsafe actions by making things properly managed.
	// i.e, throws error while accessing an undefined variable or improve basic coding practives 
	// by making such errors and helps to maintain code in future. 
	"use strict";

	/************************************************************************
	* HTJS Global variables accessible only in closure
	************************************************************************/
	var _htjs = {},
		document = window.document;
	_htjs._occurences = {};
	_htjs._templates = {};
	_htjs.textToReplace = /{{(\s+|)_this.text(\s+|)}}/g;
	_htjs.textRegex = /{{(\s+|)_this.[.A-Za-z0-9-_]+(\s+|)}}/g;
	_htjs.templateNameSelector = "name, data-name";
	_htjs.loopAttrSelector = "[ht-loop], [data-ht-loop]";
	_htjs.bindAttrSelector = "[ht-bind], [data-ht-bind]";
	_htjs.bindAjaxSelector = "[ht-ajax], [data-ht-ajax]";
	_htjs.ifSelector = "[ht-if], [data-ht-if]";
	_htjs.eventAttrSelector = "[ht-trigger], [data-ht-trigger]";

	/************************************************************************
	* HTJS Helper Functions
	************************************************************************/
	function parseVariable(value) { return value.replace(/^({{(\s+|))/, '').replace(/((\s+|)}})$/, ''); }
	function varToReplace(value) { return new RegExp("{{(\\s+|)" + value + "(\\s+|)}}", "g"); }
	function trimAttributeKey(attribute) { return attribute.replace(/^data-/, ''); }
	function parseBinders(func) { 
		var validList = [];
		var functions = func.split(/;(\s+|)/g);
		htjs.each(functions, function(i, e){
			if(!isEmpty(e))
				validList.push(e);
		});
		return validList;
	}
	function fetchAttrName(el, attributes) {
		var attribute = false;
		if(!isElement(el))
			return null;
		attributes = attributes.replace(/\[/g, '').replace(/\]/g, '').split(', ');
		// TODO - Must do something to return from this each loop. As this is now returning value from main function.
		htjs.each(attributes, function(i, e){
			var attr = el.attr(e);
			if(!isNull(attr)){
				attribute = e;
				return attribute;
			}
		});
		return attribute;
	}
	function getAttributeValue(el, attribute) {
		var firstAttribute = (isString(attribute)) ? attribute : attribute[0];
		return (!isEmpty(el.attr(firstAttribute)) ? el.attr(firstAttribute) : (!isEmpty(el.attr(attribute[1])) ? el.attr(attribute[1]) : ''));
	}
	function processCondition(condition){
		var result = (new Function("return " + condition)());
		return (isBoolean(result)) ? result : false;
	}

	/************************************************************************
	* HTJS Template Portion
	************************************************************************/

	// Replace HTML code in defined template
	_htjs.replaceHtml = function(element, replaceWith, textToReplaceWith){
		textToReplaceWith = textToReplaceWith || _htjs.textToReplace;
		var fromContent = element.html();
		var toContent = replaceWith.html();
		element.html(fromContent.replace(textToReplaceWith, toContent.trim()));
		return element;
	};

	// Assign all Custom attributes values to the template where required
	_htjs.replaceAttributes = function(element, attributes, toReplace, toReplaceArr){
		attributes = attributes || element.getAttributes();
		var fromContent = element.html();
		if(fromContent != null || htjs.getLength(fromContent) > 0){
			// Managing attribute containing "data-" and those which are not using it, with them
			var replaceWith = (!isUndefined(attributes[toReplaceArr[2]])) ? attributes[toReplaceArr[2]].textContent 
				: ((!isUndefined(attributes["data-" + toReplaceArr[2]])) ? attributes["data-" + toReplaceArr[2]].textContent : '');
			element.html(fromContent.replace(varToReplace(toReplace), replaceWith));
		}
		return element;
	};

	// Assign all Custom attributes values to the template where required
	_htjs.replaceObject = function(element, object, toReplace){
		var fromContent = element.html(),
			toReplaceArr = toReplace,
			stringToReplace = varToReplace(toReplace.join('.'));
		//just removing first _this element for getting proper value from object
		toReplaceArr.shift();
		if(fromContent != null || htjs.getLength(fromContent) > 0){
			var value = htjs.getValue(toReplaceArr, object);
			element.html(fromContent.replace(stringToReplace, (isUndefined(value) ? '' : value)));
		}
		return element;
	};

	// Fetch all templates and assign to global variable to access in other functions in closure
	_htjs._fetchTemplates = function(){
		var templates = document.querySelectorAll('htjs-template');
		for(var i=0; i < htjs.getLength(templates); i++){
			if(isUndefined(templates[i]))
				return;
			var template = htjs(templates[i]);
			if(template.attr('name') == null)
				_htjs._throwMessage('Unknown template found. Please specify name.', 'error');
			else {
				var templateName = template.attr('name');
				if(!isUndefined(_htjs._templates[templateName]))
					_htjs._throwMessage('Multiple occurrences of template named ' + templateName + ' found.', 'error');
				else
					_htjs._templates[templateName] = template;
			}
			template.hide();
		}
	};

	// Compile all HTJS Custom variables and assign them their values properly
	_htjs._compileText = function(element, template, object){
		var templateText = template.html();
		if(templateText.length > 0){
			var toCompile = htjs.array_unique(templateText.match(_htjs.textRegex));
			for (var i = 0; i < htjs.getLength(toCompile); i++) {
				var compileFrom = parseVariable(toCompile[i]);
				var toCompileArr = compileFrom.split('.');
				switch (true) {
					case (toCompileArr[1] == "text"):
						if(htjs.getLength(element) > 0)
							template = _htjs.replaceHtml(template, element);
						break;
					case (toCompileArr[1] == "attr"):
						if(htjs.getLength(element) > 0) {
							var attributes = htjs(element).getAttributes();
							template = _htjs.replaceAttributes(template, attributes, compileFrom, toCompileArr);
						}
						break;
					default:
						template = _htjs.replaceObject(template, object, toCompileArr);
						break;
				}
			}
		}
		return template;
	};

	// Handle loops at HTML
	_htjs._applyLoop = function(){
		var elements = document.querySelectorAll(_htjs.loopAttrSelector);
		htjs.each(elements, function(i, el){
			if(el.nodeType != Node.ELEMENT_NODE)
				return;
			el = htjs(el);
			var attribute = fetchAttrName(el, _htjs.loopAttrSelector);
			var loopEl = getAttributeValue(el, attribute);

			if(isNull(loopEl))
				return;
			if(loopEl.length > 0){
				var stParts;
				var parts = loopEl.split(' in ');
				var st = parts[0];
				// Doing this to prevent extra splitting if 'in' found in the given array or object.
				parts = parts.splice(1);
				var obj = parts.join(' in ');
				if(!htjs.isValidJson(obj) && !isArray(obj))
					return;

				var elKey = "", elValue = "";
				if(st.indexOf('=>') > -1){
					stParts = st.split('=>');
					elKey = varToReplace("_this." + stParts[0]);
					elValue = varToReplace("_this." + stParts[1]);
				} else {
					elValue = varToReplace("_this." + st);
				}
				if(htjs.isValidJson(obj))
					obj = htjs.parseJson(obj);
				
				for(var attr in _htjs.loopAttrSelector)
					el.removeAttr(_htjs.loopAttrSelector[attr]);

				htjs.each(obj, function(i, e) {
					var clonned = htjs(el.get().parentNode.insertBefore(el.clone(), el.get()));
					var elHtml = clonned.html();
					if(!isUndefined(stParts) && stParts[0].length > 0)
						clonned.html(htjs.replaceArray(elHtml, [elKey, elValue], [i, e]));
					else
						clonned.html(htjs.replaceArray(elHtml, [elValue], [e]));
				});

				el.remove();
			}
		});
	};

	// Validate if condition
	_htjs._processIfCondition = function(parent){
		parent = parent || "";
		var elements = (htjs.getLength(parent) > 0) ? parent.find(_htjs.ifSelector) : document.querySelectorAll(_htjs.ifSelector);
		elements = (htjs.isValidInstance(elements)) ? elements.get() : elements;
		htjs.each(elements, function(i, el){
			//if(el.nodeType != Node.ELEMENT_NODE)
			//	return;
			el = htjs(el);
			var attribute = fetchAttrName(el, _htjs.ifSelector);
			var condition = getAttributeValue(el, attribute);
			if(isNull(condition))
				return;
			if(!isEmpty(condition)){
				if(processCondition(condition) === true){
					el.removeAttr(attribute);
				} else {
					el.remove();
				}
			}
		});
	};

	// Compile custom template and clone them to required defined template 
	_htjs._compileTemplates = function(){
		_htjs._fetchTemplates();

		for (var t in _htjs._templates) {
			htjs.compileTemplate(_htjs._templates[t], {});
			// removing templates after execution
			_htjs._templates[t].remove();
		}
	};


	// Manage events binding
	_htjs._bindEvents = function(parent){
		parent = parent || "";
		var elements = (htjs.getLength(parent) > 0) ? parent.find(_htjs.bindAttrSelector) : document.querySelectorAll(_htjs.bindAttrSelector);
		elements = (htjs.isValidInstance(elements)) ? elements.get() : elements;
		if(isEmpty(elements))
			return false;
		elements = (elements instanceof NodeList) ? elements : [elements];
		htjs.each(elements, function(i, el){
			if(el.nodeType != Node.ELEMENT_NODE)
				return;
			el = htjs(el);
			var attribute = fetchAttrName(el, _htjs.bindAttrSelector);
			var binder = getAttributeValue(el, attribute);

			if(isNull(binder))
				return;
			if(htjs.getLength(binder) > 0){
				var allBinders = binder.split('|');
				htjs.each(allBinders, function(i, e){
					var parts = e.trim().split('@');
					var toBind = parts[0];
					var event = parts[1];

					// check for valid functions and binders
					var allFunc = parseBinders(toBind);
					if(htjs.getLength(allFunc) == 0)
						return;

					htjs.each(allFunc, function(i, e){
						_htjs._applyEvent(el, event, e);
					});

				});
			}
			// Removing attribute to prevent additional bindings
			el.removeAttr(attribute);
		});
	};

	// Event Logger
	_htjs._applyEvent = function(el, event, callback){
		var cb = callback.split('(');
		var cbArgs = cb[1].slice(0, -1).split(',').map(function(i, e){ return i.replace(/"/g, '').replace(/'/g, ''); });
		// Passing element to callback function as first parameter
		cbArgs = (htjs.getLength(cbArgs) == 1 && cbArgs[0] == '') ? [] : cbArgs;
		cbArgs.push(el.get());
		if(cb[0].indexOf('.') > -1)
			cb = htjs.getValue(cb[0], window);
		else
			cb = window[cb[0].trim()];

		if(isUndefined(cb))
			return;
		if(isFunction(cb)){
			el.get().addEventListener(event, function(){ cb.apply(cb, cbArgs); });
		}
	};

	// Ajax Binder
	_htjs._bindAjax = function(parent){
		parent = parent || "";
		var ajaxAttr = _htjs.bindAjaxSelector;
		var elements = (htjs.getLength(parent) > 0) ? parent.find(ajaxAttr) : document.querySelectorAll(ajaxAttr);
		elements = (htjs.isValidInstance(elements)) ? elements.get() : elements;
		if(htjs.getLength(elements) == 0)
			return false;
		//var elements = document.querySelectorAll(_htjs.bindAjaxSelector);
		if(elements) {
			elements = (elements instanceof NodeList) ? elements : [elements];
			htjs.each(elements, function (i, el) {
				if (el.nodeType != Node.ELEMENT_NODE)
					return;
				el = htjs(el);
				if (!el.hasData('ht_loaded')) {
					htjs.ajax({}, el.get());
					el.attr('ht_loaded', true);

					// Removing attribute to prevent additional bindings
					el.removeAttr(fetchAttrName(el, ajaxAttr));
				}
			});
		}
	};

	/************************************************************************
	* End HTJS Template Portion
	************************************************************************/

	// Throw messages in console or popup or alerts etc.
	_htjs._throwMessage = function(message, severity, type) {
		type = type || 'console';
		severity = severity || 'log';

		if(type == 'console'){
			switch (severity) {
			  case 'error':
			    console.error('Error: ' + message);
			    break;
			  case 'warning':
			    console.warn(message);
			    break;
			  case 'info':
			    console.info(message);
			    break;
			  default:
			    console.log(message);
			    break;
			}
		} else if(type == 'popup'){

		}
	};

	// HTJS main function which works like its constructor and call all required functions on loading script
	_htjs.init = function() {
		var compileStart = new Date();
		_htjs._applyLoop();
		_htjs._compileTemplates();
		_htjs._processIfCondition();
		_htjs._bindAjax();
		_htjs._bindEvents();
		var compileEnd = new Date();
		_htjs._throwMessage('Templates compilation time: ' + (compileEnd - compileStart) / 1000 + 's', 'info');
	};

	// Public functions which will be used outside the closure
	htjs.compileTemplate = function(template, object, target, noRecordMsg, clearTarget){
		target = target || "";
		clearTarget = isUndefined(clearTarget) ? true : clearTarget;
		noRecordMsg = noRecordMsg || " ";
		var reqCompiledTemplate = "";
		if(htjs.getLength(target) > 0 && !isUndefined(target) && isObject(object)){
			template = _htjs._templates[template];
			var compiledTemplates = "";
			// Checking if it is simple object than first make it array's first element to process loop easily
			if(isUndefined(object[0]))
				object = [object];
			htjs.each(object, function(i, e){
				var templateCopy = htjs(template.clone());
				// element param is not required
				templateCopy = _htjs._compileText("", templateCopy, e);
				compiledTemplates += htjs(templateCopy).html();
			});
			// clearTarget will clear old data in target element and put the new compiled data in it
			target = (htjs.isValidInstance(target)) ? target : htjs(target);
			if(clearTarget)
				target = target.html((compiledTemplates.length > 0) ? compiledTemplates : noRecordMsg);
			else{
				var div = document.createElement('div');
				div.innerHTML = (compiledTemplates.length > 0) ? compiledTemplates : noRecordMsg;
				var appendedDiv = target.append(div);
				//target = htjs(appendedDiv).children();
				htjs(appendedDiv).removeWrapper();
			}
			// Bind events and ajax on elements having it.
			target = (htjs.isValidInstance(target)) ? target : htjs(target);
			_htjs._processIfCondition();
			_htjs._bindAjax(target);
			_htjs._bindEvents(target);

			reqCompiledTemplate = target;

		} else {
			var templateName = fetchAttrName(template, _htjs.templateNameSelector);
			var occurences = document.getElementsByTagName('htjs:' + template.attr(templateName));
			for (var i = occurences.length - 1; i >= 0; --i) {
				// Cloning template for each element.
				var templateCopy = htjs(template.clone());
				var element = htjs(occurences.item(i));
				var parent = element.parent();
				// Compiling text
				templateCopy = _htjs._compileText(element, templateCopy, object);
				templateCopy.hide();
				parent.replaceChild(templateCopy.get(), element.get());
				// Removing template wrapper over the childs
				templateCopy.removeWrapper();

				reqCompiledTemplate = templateCopy;
			}
		}
		return reqCompiledTemplate;
	}

	// Calling to HTJS main init function to start the application and make HTJS to perform its duty.
	_htjs.init();

}());