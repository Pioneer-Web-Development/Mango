/*
 * jQuery Brooser Plugin (jQuery Version: 1.4.2)
 * http://whocar.es/about/jbrooser
 * http://whocar.es/about/jbrooser/doc
 * http://whocar.es/about/jbrooser/articles
 * http://files.philip-ehret.de/dev/examples/jquery/jbrooser/
 * 
 * Author:
 * 	Philip Ehret
 * 
 * Originally written in MooTools by Yannick Croissant
 * 	http://code.google.com/p/brooser/
 * 	http://dev.k1der.net/dev/brooser-un-browser-de-fichier-pour-mootools/
 * 
 * Licensed under the MIT licence
 * 	(../MIT-LICENCE.txt)
 * 
 * Version:
 * 	0.9.1 (as I used the original MooTools implementation of version 0.9.1)
 * 
 * Documentation
 * 	http://whocar.es/about/jbrooser
 *	http://whocar.es/about/jbrooser/doc
 * 
 * Example Usage
 * 	$('#browse').brooser({
 * 		currentDir:	'./../test',
 * 		onFinish:	function(file){
 * 			$('#file').val(file);
 * 		}
 * 	});
 */

/*
 * 
 */
(function($) {
	$.brooser = function(btn, options){
		var $element = $(btn);
		var XHRRequest;
		
		var setOptions = function(newOptions){
			var defaultOptions = {
				onFinish:	function(){
					debug('You need to set options.onFinish to a function in order to see something happening.')
				},
				targetData:	'path',
				currentDir:	'../../',
				phpFile:	'includes/brooser/Brooser.php',
                changeDirAllowed: false
			};
			
			if (newOptions) {
				options.onFinish = newOptions.onFinish || defaultOptions.onFinish;				
				options.targetData = newOptions.targetData || defaultOptions.targetData;
				options.currentDir = newOptions.currentDir || defaultOptions.currentDir;
                options.phpFile = newOptions.phpFile || defaultOptions.phpFile;
				options.changeDirAllowed = newOptions.changeDirAllowed || defaultOptions.changeDirAllowed;
			} else {
				options = defaultOptions;
			}
		};
		
		var construct = function(){
			if($('#brooser').length) return false;	// Browser already exist, skiping
			
			$('<div></div>').attr({
				id:	'brooser-overlay'
			}).css({
				display:	'none',
				position:	'absolute',
				left:		0,
				top:		0
			}).appendTo(document.body);

			$brooser = $('<div></div>').attr({
				id:	'brooser'
			}).css({
				display:	'none'
			}).appendTo(document.body);

			$('<ul></ul>').attr({
				id:	'brooser-browser'
			}).appendTo($brooser);
			
            $('<input />').attr({
                id:        'brooser-open',
                type:    'button',
                value:    'Get full path'
            }).appendTo($brooser);
            
            $('<input />').attr({
				id:		'brooser-name',
				type:	'button',
				value:	'Get name only'
			}).appendTo($brooser);

			$infos = $('<div></div>').attr({
				id:	'brooser-infos'
			}).appendTo($brooser);
			
			$head = $('<div></div>').attr({
				id:'brooser-head'
			}).appendTo($infos);
			
			$('<img />').attr({
				id:	'brooser-icon'
			}).appendTo($head);

			$('<h1></h1>').attr({
				id:	'brooser-title'
			}).appendTo($head);
			
			$('<span></span>').attr({
				id:	'brooser-date'
			}).text('Modified : ').appendTo($head);

			$('<h2></h2>').text('Informations').appendTo($infos);
			
			$list = $('<dl></dl>').appendTo($infos);
			$dt = $('<dt></dt>');
			$dd = $('<dd></dd>');
			
			$dt.clone().text('Type :').appendTo($list);
			$dd.clone().attr({
				id:	'brooser-type'
			}).appendTo($list);
			$dt.clone().text('Size :').appendTo($list);
			$dd.clone().attr({
				id:	'brooser-size'
			}).appendTo($list);
			$dt.clone().text('Directory :').appendTo($list);
			$dd.clone().attr({
				id:	'brooser-dir'
			}).appendTo($list);

			$('<h2></h2>').text('Preview').appendTo($infos);
			$('<div></div>').attr({
				id:	'brooser-preview'
			}).appendTo($infos);
		
			$('<a></a>').attr({
				id:		'brooser-close',
				href:	'#'
			}).text('Close').appendTo($brooser);
		};
		
		var bindEvents = function() {
            $('#brooser-open').unbind('click').bind('click',open);
			$('#brooser-name').unbind('click').bind('click',nameOnly);
			$('#brooser-close').unbind('click').bind('click',close);
		};
		
		var open = function(e) {
            if(!options.currentFile) return false;
            var data;
            data = options.currentFile.dir + '/' + options.currentFile.name;
            hide();
            e.preventDefault();
            onFinish(data);
        };
        
        var nameOnly = function(e) {
			if(!options.currentFile) return false;
			var data;
			data = options.currentFile.name;
			hide();
			e.preventDefault();
			onFinish(data);
		};
		
		
		var close = function(e){
			hide();
			e.preventDefault();
		};
		
		var setTarget = function(){
			debug('setTarget');
			$element.bind('click',function() {
				setDir(options.currentDir);
				display();
			});
		};
		
		var display = function(){
			bindEvents();
			$('#brooser-overlay').css({
				display:'block',
				width:	$(document).width()+'px',
				height:	$(document).height()+'px'
			});
			$('#brooser').css({
				display: 'block'
			});
			
			// Fuck IE
			// Check this
			if($.browser.msie && ($.browser.version < 7)) {
				$('select').css({
					display:	'none'
				});
			}
		};
		
		var hide = function() {
			$('#brooser-browser').empty();
			$('#brooser-overlay').css({
				display:	'none'
			});
			$('#brooser').css({
				display:	'none'
			});
			
			// Fuck IE
			// Check this
			if($.browser.msie && ($.browser.version < 7)) {
				$('select').css({
					display:	''
				});
			}
		};
		
		var setDir = function(dir) {
			debug('setDir');
			options.current = null;
			$('#brooser-infos').css({
				visibility: 'hidden'
			});
			if(XHRRequest) XHRRequest.abort();
			
			debug(options.phpFile);
			XHRRequest = $.ajax({
				url:			options.phpFile,
				type:			'POST',
				data: 			{
					action:	'browse',
					dir:	dir,
					time:	new Date().getTime()
				},
				beforeSend:		loadingBrowser,
				success:		fillDir,
				dataType:		'json'
			});
		};
		
		var loadingBrowser = function() {
			debug('loadingBrowser');
			$('#brooser-browser').empty().addClass('loading');
			debug('loadingBrowser');
		};
		
		var loadingPreview = function() {
			if($('#preview-style').length)  $('#preview-style').remove();
			if($('#preview-script').length) $('#preview-script').remove();
			$('#brooser-preview').empty().addClass('loading');
		};
		
		var fillDir = function(files) {
            debug('fillDir');
			debug('allow change'+options.changeDirAllowed);
			$('#brooser-browser').removeClass('loading');
			$.each(files, function(index, file) {
				options.currentDir = file.dir;
				$li = $('<li></li>').appendTo('#brooser-browser');
				$a = $('<a></a>').appendTo($li);
				$a.text(file.name)
				
				if(file.access) {
					$a.attr({
						href: file.dir + '/' + file.name
					}).bind('click',function(e){
						e.preventDefault();
						// isDir ?
                        if(file.mime == 'text/directory') {
							if(options.changeDirAllowed==true)
                            {
                                setDir(options.currentDir + '/' + file.name);
							    return;
                            } else {
                                alert ('You are not allowed to change directory');
                            }
						} else {
						    var $el = $(e.target);
						    if ($el.get(0).tagName.toLowerCase() != 'a') $el = $el.parent('a');
						    fillInfos(file);
						    if (options.current) {
							    options.current.removeClass('selected');
						    }
						    $el.addClass('selected');
						    options.current = $el;
						    options.currentFile = file;
                        }
					});
				} else {
					$a.addClass('denied');
				}
                if(file.mime == 'text/directory' && options.changeDirAllowed==false)
                {
                    $a.addClass('denied');
                }
				$('<img />').attr({
					src:	file.icon
				}).prependTo($a);
			});
		};
		
		var fillInfos = function(file) {
			$('#brooser-infos').css({
				visibility:	''
			});
			$('#brooser-preview').empty();
			$('#brooser-icon').attr({
				src:	file.icon,
				alt:	file.mime
			});
			$('#brooser-title').empty().text(file.name);
			$('#brooser-date').empty().text('Modified : ' + file.date);
			$('#brooser-type').empty().text(file.mime);
			var filesize = sizeCalc(file.size,true,0);
			$('#brooser-size').empty().text(filesize+((filesize.search('Bytes')==-1)?' ('+file.size+' Bytes)':''));
			$('#brooser-dir' ).empty().text(file.dir);
			
			// Preview
			if(XHRRequest) XHRRequest.abort();
			XHRRequest = $.ajax({
				url:			options.phpFile,
				type:			'POST',
				data:			{
					action:		'preview',
					dir:		file.dir,
					file:		encodeURI(file.name),
					mimetype:	file.mime,
					time:		new Date().getTime()
				},
				beforeSend:		loadingPreview,
				success:		fillPreview,
				dataType:		'json'
			});
		};
		
		var fillPreview = function(data) {			
			// Inject styles
			if(data.style.length>0) {
				var style = document.createElement('style');
				$(style).attr({
					id:		'preview-style',
					media:	'screen',
					type:	'text/css'
				}).prependTo($head);
				
				// Fuck IE
				if($.browser.msie){
					style.styleSheet.cssText = data.style;
				} else {
					$(style).text(data.style);
				}
			}
						
			// Inject scripts
			if(data.script.length>0) {
				var script = document.createElement('script');
				$(script).attr({
					type:	'text/javascript',
					id:		'preview-script'
				}).text(data.script).prependTo($head);
			}
			
			// Inject preview's content
			$('#brooser-preview').removeClass('loading');
			$('#brooser-preview').html(data.content);

		};
		
		var sizeCalc = function(size,unit,prec) {
			if (prec === null) {
				prec=2;
			}
			prec = Math.pow(10,prec);		
			var tab = [' Bytes',' KB',' MB',' GB',' TB',' PB'];
			for(var i = 0;size>1024;i++) {
				size=size/1024;
			}
			if (!unit) {
				return Math.round(size*prec)/prec;
			}
			return Math.round(size*prec)/prec+tab[i];
		};
		
		var onFinish = function(file){
			options.onFinish(file);
		};
		
		this.setOptions = setOptions;
		
		setOptions(options);
		construct();
		setTarget();
	};
	
	$.fn.brooser = function(options){		
		this.each(function(){
			if($(this).data('brooser')){ // Well, there is already a brooser instance covering this object
				if(options.remove){ // Check whether it should be removed
					$(this).data('brooser').remove();
					$(this).removeData('brooser');
				} else { // if not set Options
					$(this).data('brooser').setOptions(options);
				}
			} else { // Theres no brooser instance, so we're going to create one
				$(this).data('brooser', new $.brooser(this, options));
			}
		});
		
		return this;
	};
	
	function debug($obj) {
		if (window.console && window.console.log)
			window.console.log($obj);
	};
}) (jQuery);
