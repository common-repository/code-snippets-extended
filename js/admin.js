jQuery(function($) {
	"use strict";

    $(document).ready(function(){

    	/** ACE code editor init **/

		$('.cse-editor').each( function(index){
            var textarea = $(this);
            var mode = $(this).attr('mode') || "php";
            var cls = textarea.attr('class').replace("cse-editor","");
            var editDiv = $('<div>', {
                'position'	: 'absolute',
                'width'  	: textarea.width(),
                'height' 	: textarea.height(),
                'class' 	: cls
            }).insertBefore(textarea);
            textarea.hide();
            var editor = ace.edit(editDiv[0]);
            editor.renderer.setShowGutter(true);
            editor.getSession().setValue(textarea.val());
            editor.getSession().setMode({path: "ace/mode/" + mode, inline: false});
            editor.setAutoScrollEditorIntoView(true);
            //editor.setOptions({ /*maxLines: 25*/ });
            editor.setTheme("ace/theme/xcode");
            $.data(this, 'editor', editor);

            var hresize = function() {
					// copy back to textarea on form submit...
					textarea.val(editor.getSession().getValue());

			        var newHeight =
			                  editor.getSession().getScreenLength()
			                  * editor.renderer.lineHeight
			                  + editor.renderer.scrollBar.getWidth();
			        if($(".cse-editor").first().height() < newHeight){
				        $('.cse-editor').each(function(index){
				        	$(this).height(newHeight.toString() + "px");
				        });
				        $('.cse-editor').first().parent().height(newHeight.toString() + "px");

				        editor.resize();
				    }
			    }
			hresize();
		    editor.getSession().on('change', hresize);

		    var wresize = function(event) {
					var w = window.innerWidth;
					console.log(w);
					if(w >= 2000){
						w -= w/100*10;
					}else if(w >= 1800){
						w -= w/100*20;
					}else if(w >= 1024){
						w -= w/100*18;
					}else if (w > 781) {
				    	w -= w/100*10; // 10% e.g. width = 75%
				    } else {
			    		w -= w/100*5;
				    }
			      	$('.cse-editor').each(function(index){
			        	$(this).width(w.toString() + "px");
			        });
					editor.resize();
				}
			wresize();
			window.onresize = wresize;
        });

        if($("#use-wordwrap").length > 0){
        	var add_wrap = function(){

		       	var editor = $('.cse-editor').first().data('editor');
		       	if($("#use-wordwrap").is(":checked")){
		       		editor.getSession().setUseWrapMode(true);
		       	}else{
		       		editor.getSession().setUseWrapMode(false);
		       	}

        	}
        	add_wrap();
        	$("#use-wordwrap").on("change",add_wrap);
    	}

		if($("#test_code").length != 0)
			$('#test_code').on('click',function(e){
				e.preventDefault();
				$(this).attr("disabled", "disabled");
				var macro_code = $('textarea[name="snippet_code"]').val();
				var test_args = $('#test_args').val();
				$.post(ajaxurl, {
					action	: 	'test_code',
					code	:	macro_code,
					args	:	test_args,
					nonce	: 	window.nonce_data,
				}, 
				function(response){
					var tr = $("#test_res");
					tr.empty();
					$("#test_res").css('display','block');
					tr.append('<div class="aft_info">'+response+'</div><span class="close-button">X</span>');
					// up!
					$('html, body').animate({
						scrollTop: 0
					}, 500);
				}).always(function(){
					$("#test_code").removeAttr("disabled");
				});
				return false;
			});

		var argschange = function(){
				var args = $("#test_args").val().split(",");
				if(args && args.length > 0){
					$('#arg-info').empty();
					var ocode = "";
					for (var i = 0; i < args.length; i++) {
						var arg = args[i].trim();
						var aname = arg.split("=");
						if(aname.length == 2 && aname[1] && aname[1] !=""){
							aname = aname[0].trim();
							aname = aname.replace(/[^a-zA-Z0-9]/, '_').trim();
							if(aname == "content"){
								ocode += "$content; //you can use $content variable to get [rsnippet]this content[/rsnippet]<br>";
							} else {
								ocode += "$"+aname+" = $atts['"+aname+"'];<br>";
							}
						}
					}
					if(ocode != "")
						$('#arg-info').append("<pre>"+
										"<em>//Use this variables in your php code:</em><br>\r\n"
										+ocode+"</pre>");
				}
			}

		/** generate code for test args **/
		if($("#test_args").length != 0){
			argschange();
			$("#test_args").on("keyup change",argschange);
		}

		/** Close button click **/
		$("body").on('click','.close-button',function(e){
			e.preventDefault();
			$("#test_res").empty();
			$("#test_res").css('display','none');
			$("#test_res").append($('<span class="close-button">X</span>'));
		});
			
		/** Тык на кнопку "Сниппет" над текстовым редактором при добавлении материала **/
		if($("#show_code_snippets").length != 0)
			$("#show_code_snippets").magnificPopup({
				type: 'ajax',
				preloader: false,
				callbacks: {
					open : function() {},
			    	ajaxContentAdded : function() {
								    		$("a#select_snippet").on("click", function(){
								    			var data = $(this).parent().parent().find("td.st_id").attr('data_str');
								    			var jsonp = JSON.parse(data);
								    			wp.media.editor.insert(jsonp.code.replace(/\0/g, '0').replace(/\\(.)/g, "$1"));
								    			$.magnificPopup.close();
								    		});
								    	  },
			    	close : function() {}
				}
			});
		
		/** Show Media Selection Thickbox (tb_whow)**/
		if($("#acs-insert-media-button").length != 0)
			$("#acs-insert-media-button").click(function(e) {
				e.preventDefault();

				var file_uploader = wp.media.frames.file_frame = wp.media({
					title: 'Choose File',
					library: {
						author: userSettings.uid, // specific user-posted attachment
						type : 'image'
					},
					multiple: false
				});

				// uploader closed
				file_uploader.on('close',function() {
					var selection = file_uploader.state().get('selection');
					if(!selection.length){
						$('#thtml').val("");
						$('#turl').val("");
					}
				});
			
				// select file button pressed
				file_uploader.on('select', function() {
					var attachment = file_uploader.state().get('selection').first().toJSON();
					//create image html
					let image = new Image(attachment.width,attachment.height);
					image.src = attachment.url;
					image.id = attachment.id;
					image.title = attachment.title;

					// update fields
					$('#thtml').val(image.outerHTML);
					$('#turl').val(attachment.url);
				});
				file_uploader.open();

				// in previous wewrsions of wordpress i used thickbox to select files but due to current wordpress changes i recreated this section
				//var formfield = $('#txt_img_url').attr('name');
				/*tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
				
				var original_send_to_editor = window.send_to_editor;
				// media selected
				window.send_to_editor = function(html) {
					console.log(html);
					
					/*var imgurl = $('img',html).attr('src');
					if(!imgurl) imgurl = $(html).attr("href");
					
					$('#thtml').val(html);
					$('#turl').val(imgurl);
					$("#TB_overlay").remove();
					$("#TB_window").remove();
					$("body").removeClass("modal-open");*/
					/*window.send_to_editor = original_send_to_editor;
					return false;
				}*/
				return false;
			});
    });
});