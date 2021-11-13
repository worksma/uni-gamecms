function toasty(alert = "success", message = "") {var toast = new Toasty({classname: "toast",transition: "slideLeftRightFade",insertBefore: false,progressBar: true,enableSounds: true});switch(alert) {case "success": toast.success(message); break;case "error": toast.error(message); break;case "info": toast.info(message); break;case "warning": toast.warning(message); break;}}
function get_url() {return "https://" + location.host + "/";}
function send_post(website, form_data, callback, method = "POST") {form_data.append("phpaction", "1"); form_data.append("token", $("#token").val());$.ajax({type: method, url: website, processData: false, contentType: false, data: form_data, dataType: "json", success: function(result) { callback(result); }});}
function show_input_error(input_name,text,time){if(time==null){time=2000;}
if(text==null){text='';}
let input=$("#"+input_name);input.next(".error_message").remove();input.addClass("input_error");input.after("<div class='error_message'>"+text+"</div>");if(time===99999){input.attr("disabled","");}else{setTimeout(function(){input.removeClass("input_error");input.next(".error_message").fadeOut(0);},time);}}
function show_input_success(input_name,text,time){if(text==null){text='';}
let input=$("#"+input_name);input.next(".success_message").remove();input.addClass("input_success");input.after("<div class='success_message'>"+text+"</div>");setTimeout(function(){input.removeClass("input_success");input.next(".success_message").fadeOut(0);},time);}
function show_ok(){let scrollTop=window.pageYOffset?window.pageYOffset:(document.documentElement.scrollTop?document.documentElement.scrollTop:document.body.scrollTop);if(scrollTop>50){$(".result_ok_b").fadeIn();setTimeout(function(){$(".result_ok_b").fadeOut();},1500);}else{$(".result_ok").fadeIn();setTimeout(function(){$(".result_ok").fadeOut();},1500);}}
function show_error(){let scrollTop=window.pageYOffset?window.pageYOffset:(document.documentElement.scrollTop?document.documentElement.scrollTop:document.body.scrollTop);if(scrollTop>50){$(".result_error_b").fadeIn();setTimeout(function(){$(".result_error_b").fadeOut();},1500);}else{$(".result_error").fadeIn();setTimeout(function(){$(".result_error").fadeOut();},1500);}}
function scrollToBox(argument){$("html, body").animate({scrollTop:$(argument).offset().top+"px"},{duration:500,easing:"swing"});}
function reset_page(){location.reload();}
function go_to(link){location.href=link;}
function send_value(id,value){document.getElementById(id).value=value;}
function stop_button(id,time){let button=$(id);let name=button.val();let onclick=button.attr('onclick');button.addClass('disabled');button.attr('onclick','');button.val('Отправлено');setTimeout(function(){button.removeClass('disabled');button.attr('onclick',onclick);button.val(name);},time)}
function clean_tiny(area){tinymce.get(area).setContent('');}
function focus_input(id){let input=$("#"+id);if(input.size()>0){input.focus();}}
function play_sound(file,volume){audio=new Audio();audio.src=file;audio.volume=volume;audio.autoplay=true;}
function set_cookie(name,value,expires,path,domain,secure){document.cookie=name+"="+escape(value)+((expires)?"; expires="+expires:"")+((path)?"; path="+path:"")+((domain)?"; domain="+domain:"")+((secure)?"; secure":"");}
function get_cookie(name){let cookie=" "+document.cookie;let search=" "+name+"=";let str=null;let offset=0;let end=0;if(cookie.length>0){offset=cookie.indexOf(search);if(offset!=-1){offset+=search.length;end=cookie.indexOf(";",offset);if(end==-1){end=cookie.length;}
str=unescape(cookie.substring(offset,end));}}
return(str);}
function dell_block(id){$("#"+id).remove();}
function set_enter(input,func){$(input).keydown(function(event){if(event.which==13&&!event.shiftKey){event.preventDefault();eval(func);}});}
function send_form(form,func){$(form).submit(function(event){event.preventDefault();eval(func);});}
function create_material(data,dataType=0){data['phpaction']=1;data['token']=$('#token').val();let material='';if(dataType===0){$.each(data,function(key,value){material=material+key+'='+encodeURIComponent(value)+'&';});material.substring(0,material.length-1);}else{material=new FormData();$.each(data,function(key,value){material.append(key,value);});}
return material;}
function show_stub(message='Авторизуйтесь, чтобы выполнить действие'){NProgress.start();NProgress.done();setTimeout(show_error,500);show_noty('Down','info','<a>'+message+'</a>',2000);}
function setImagePreview(fileInput,imagePreviewSelector){if(fileInput.files&&fileInput.files[0]){let reader=new FileReader();reader.onload=function(event){document.querySelector(imagePreviewSelector).setAttribute('src',event.target.result);};reader.readAsDataURL(fileInput.files[0]);}}
function empty(mixed_var){return(mixed_var===''||mixed_var===' '||mixed_var===0||mixed_var==='0'||mixed_var===null||mixed_var===false||mixed_var==={}||mixed_var===[]);}
function ajax(parameters){if(!parameters.hasOwnProperty('data')){parameters.data={};}
if(!parameters.hasOwnProperty('inputs')){parameters.inputs={};}
if(!parameters.hasOwnProperty('dataType')){parameters.dataType='json';}
if(!parameters.hasOwnProperty('progress')){parameters.progress=false;}
if(!parameters.hasOwnProperty('inputs')){parameters.inputs={};}
if(parameters.progress){NProgress.start();}
let materialType=0;if(parameters.hasOwnProperty('processData')&&parameters.processData===false){materialType=1;}
let ajax={type:'POST',url:parameters.controller,data:create_material(parameters.data,materialType),success:(result)=>{if(parameters.progress){NProgress.done();setTimeout(show_ok,500);}
if(result.alert){alert(result.alert);}
if(result.evalJs){eval(result.evalJs);}
if(parameters.hasOwnProperty('success')){parameters.success(result);}},error:(result)=>{if(parameters.progress){NProgress.done();setTimeout(show_error,500);}
if(parameters.dataType==='json'){let responseJson=$.parseJSON(result.responseText);if(responseJson.hasOwnProperty('errors')){for(let input in responseJson.errors){if(responseJson.errors.hasOwnProperty(input)){let inputId=input;if(parameters.inputs.hasOwnProperty(input)){inputId=parameters.inputs[input];}
show_input_error(inputId,responseJson.errors[input]);}}}
if(responseJson.alert){alert(responseJson.alert);}
if(parameters.hasOwnProperty('error')){parameters.error(responseJson);}}}}
if(parameters.dataType==='json'){ajax.dataType=parameters.dataType;}
if(parameters.hasOwnProperty('processData')&&parameters.processData===false){ajax.contentType=false;ajax.processData=false;}
$.ajax(ajax);}
