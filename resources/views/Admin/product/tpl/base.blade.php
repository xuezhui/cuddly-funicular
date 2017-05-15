<style type="text/css" media="screen">
	.choose{
		position: relative;
	}
	#upload{
		position: absolute;
		left: 0px;
		top:0px;
		opacity: 0;
		z-index: 1;
		width:100%;
	}
	.img-thumbnail{
	    display: block;
	    max-width: 100%;
	    height: auto;
	    padding: 4px;
	    line-height: 1.42857143;
	    background-color: #fff;
	    border: 1px solid #ddd;
	    border-radius: 4px;
	    -webkit-transition: all .2s ease-in-out;
	    -o-transition: all .2s ease-in-out;
	    transition: all .2s ease-in-out;
	    width: 140px;
	    height: 140px;
	    margin:5px;
	}
</style>
<link href="/lib/webuploader/0.1.5/webuploader.css" rel="stylesheet" type="text/css" />
@if($product)
<input type="hidden" name="id" value="{{$product->id}}">
@endif
<div class="row cl">
	<label class="form-label col-xs-4 col-sm-2"><span class="c-red">*</span>产品标题：</label>
	<div class="formControls col-xs-8 col-sm-9">
		<input type="text" class="input-text" value="@if($product){{$product->name}}@endif" placeholder="" id="name" name="name">
	</div>
</div>
<div class="row cl">
    <label class="form-label col-xs-4 col-sm-2"><span class="c-red">*</span>供应商：</label>
    <div class="formControls col-xs-8 col-sm-9"> 
 
        <select name="supplierid" class="select input-text" placeholder="供应商" id="supplierid">
            @foreach($suppliers as $s)
            <option value="{{$s->id}}" @if($product&&$product->supplierid==$s->id)selected @endif>{{$s->suppliername }}</option>
            @endforeach
        </select>

         
    </div>
</div>
<div class="row cl">
	<label class="form-label col-xs-4 col-sm-2"><span class="c-red">*</span>分类栏目：</label>
	<div class="formControls col-xs-8 col-sm-9"> 
		
		<div class="col-xs-6">
				<select name="pcate" class="select input-text" placeholder="一级分类" id="pcate">
					@foreach($level1 as $c)
					<option value="{{$c->id}}" @if($pid&&$pid->p_id==$c->id)selected @endif>{{$c->category_name }}</option>
					@endforeach
				</select>
		</div>
		<div class="col-xs-6">
				<select name="cate" class="select input-text" id="cate" >
					@if($level2)
						@foreach($level2 as $c)
						<option value="{{$c->id}}" @if($product&&$product->category==$c->id)selected @endif>{{$c->category_name }}</option>
						@endforeach
					@endif
				</select>
		</div>
		 
	</div>
</div>

<div class="row cl">
	<label class="form-label col-xs-4 col-sm-2"><span class="c-red">*</span>是否上架：</label>
	<div class="formControls col-xs-8 col-sm-9 skin-minimal">
		<div class="radio-box">
			<input name="isable" type="radio" id="sex-1" @if(!$product||!$product->isable)checked @endif value="0">
			<label for="sex-1">未上架</label>
		</div>
		<div class="radio-box">
			<input type="radio" id="sex-2" name="isable" @if($product&&$product->isable)checked @endif value="1">
			<label for="sex-2">上架</label>
		</div>
	</div>
</div>
<div class="row cl">
	<label class="form-label col-xs-4 col-sm-2">产品属性：</label>
	<div class="formControls col-xs-8 col-sm-9 skin-minimal">
		<div class="check-box">
			<input type="checkbox" id="checkbox-pinglun" name="params[]" @if($product&&$pros&&in_array(1,$pros)) checked @endif value='1'>
			<label for="checkbox-pinglun">爆款推荐&nbsp;</label>
		</div>
	</div>
</div>
{{--
<!-- <div class="row cl">
	<label class="form-label col-xs-4 col-sm-2"><span class="c-red">*</span>现价：</label>
	<div class="formControls col-xs-8 col-sm-9">
		<input type="number" name="cur_price" id="cur_price" placeholder="" value="@if($product){{$product->cur_price}}@endif" class="input-text" style="width:90%">
		元</div>
</div> -->
<!-- <div class="row cl">
	<label class="form-label col-xs-4 col-sm-2"><span class="c-red">*</span>市场价格：</label>
	<div class="formControls col-xs-8 col-sm-9">
		<input type="number" name="market_price" id="market_price" placeholder="" value="@if($product){{$product->market_price}}@endif" class="input-text" style="width:90%">
		元</div>
</div> -->
<!-- <div class="row cl">
	<label class="form-label col-xs-4 col-sm-2">库存：</label>
	<div class="formControls col-xs-8 col-sm-9">
		<input type="number" name="stock" id="" placeholder="" value="@if($product){{$product->stock}}@endif" class="input-text" style="width:100%">
	</div>
</div> -->
--}}
<div class="row cl">
	<label class="form-label col-xs-4 col-sm-2">产品摘要：</label>
	<div class="formControls col-xs-8 col-sm-9">
		<textarea name="remark" placeholder="空制在50个汉字，100个字符以内" class="textarea" rows="3" id="remark">@if($product){{$product->remarks}}@endif</textarea>
	</div>
</div>
<div class="row cl">
	<label class="form-label col-xs-4 col-sm-2"><span class="c-red">*</span>缩略图：</label>
	<div class="formControls col-xs-8 col-sm-9">
		<div class="uploader-thum-container">
			<input name="photos" class="input-text" id="photos" value="@if($product){{$product->photos}}@endif" readonly>
			<span class="choose">
				<div id="" class=" btn btn-primary radius">选择图片</div>
				<input type="file" id="upload" accept="image/jpg,image/jpeg,image/png,image/gif,image/bmp">
			</span>
			
			<span id="btn-star" class="btn btn-default btn-uploadstar radius ml-10">开始上传</span>
			<img src="@if($product){{$product->photos}}@endif" alt="" id="img" class="img-thumbnail">
		</div>
	</div>
</div>
<div class="row cl">
	<label class="form-label col-xs-4 col-sm-2">详细内容：</label>
	<div class="formControls col-xs-8 col-sm-9">
		<div class="uploader-list-container">
			<div class="queueList " >
				<div id="dndArea" class="placeholder @if($product&&$product->imgs&&count($product->imgs)>0)element-invisible @endif">
					<div id="filePicker-2"></div>
					<p>或将照片拖到这里</p>
				</div>
				@if($product&&$product->imgs&&count($product->imgs)>0)
				<ul class="filelist">
					@foreach($product->imgs as $img)
					<li id="">
						<p class="title"></p>
						<p class="imgWrap">
							<img src="{{$img}}">
						</p>
						<span class="success"></span>
						<input name="desc[]" type="hidden" value="{{$img}}">
						<div class="file-panel" style="height: 0px;"><span class="cancel">删除</span></div>
					</li>
					@endforeach
				</ul>
				@endif
			</div>
			<div class="statusBar" @if(!$product || count($product->imgs)<1)style="display:none;" @endif>
				<div class="progress"> <span class="text">0%</span> <span class="percentage"></span> </div>
				<div class="info"></div>
				<div class="btns">
					<div id="filePicker2"></div>
					<div class="uploadBtn">开始上传</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="/lib/webuploader/0.1.5/webuploader.min.js"></script> 
<script src="/js/uploads.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">
	$("select[name='pcate']").change(function(){

		var pid = $(this).val();
		$.get('/admin/category/getchilds',{'id':pid},function(result){
			var ops = result.results;
			var html = "";
			$(ops).each(function(){
				html += "<option value='"+this.id+"'>"+this.category_name+"</option>";
			});
			$("select[name='cate']").html(html);
		},'json');
	});
	(function( $ ){
    // 当domReady的时候开始初始化
    $('.filelist li').on('mouseenter', function() {
        $(this).find('.file-panel').stop().animate({height: 30});
    });
    $('.filelist li').on( 'mouseleave', function() {
        $(this).find('.file-panel').stop().animate({height: 0});
    });
    $(document).on('click','.cancel',function(){
    	$(this).parents('li').remove();
    })
    $(function() {
        var $wrap = $('.uploader-list-container'),

            

            // 状态栏，包括进度和控制按钮
            $statusBar = $wrap.find( '.statusBar' ),

            // 文件总体选择信息。
            $info = $statusBar.find( '.info' ),

            // 上传按钮
            $upload = $wrap.find( '.uploadBtn' ),

            // 没选择文件之前的内容。
            $placeHolder = $wrap.find( '.placeholder' ),

            $progress = $statusBar.find( '.progress' ).hide(),

            // 添加的文件数量
            fileCount = 0,

            // 添加的文件总大小
            fileSize = 0,

            // 优化retina, 在retina下这个值是2
            ratio = window.devicePixelRatio || 1,

            // 缩略图大小
            thumbnailWidth = 110 * ratio,
            thumbnailHeight = 110 * ratio,

            // 可能有pedding, ready, uploading, confirm, done.
            state = 'pedding',

            // 所有文件的进度信息，key为file id
            percentages = {},
            // 判断浏览器是否支持图片的base64
            isSupportBase64 = ( function() {
                var data = new Image();
                var support = true;
                data.onload = data.onerror = function() {
                    if( this.width != 1 || this.height != 1 ) {
                        support = false;
                    }
                }
                data.src = "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==";
                return support;
            } )(),

            // 检测是否已经安装flash，检测flash的版本
            flashVersion = ( function() {
                var version;

                try {
                    version = navigator.plugins[ 'Shockwave Flash' ];
                    version = version.description;
                } catch ( ex ) {
                    try {
                        version = new ActiveXObject('ShockwaveFlash.ShockwaveFlash')
                                .GetVariable('$version');
                    } catch ( ex2 ) {
                        version = '0.0';
                    }
                }
                version = version.match( /\d+/g );
                return parseFloat( version[ 0 ] + '.' + version[ 1 ], 10 );
            } )(),

            supportTransition = (function(){
                var s = document.createElement('p').style,
                    r = 'transition' in s ||
                            'WebkitTransition' in s ||
                            'MozTransition' in s ||
                            'msTransition' in s ||
                            'OTransition' in s;
                s = null;
                return r;
            })(),

            // WebUploader实例
            uploader;
            // 图片容器
            if($('.filelist').length>0){
            	$queue = $('.filelist');
            	console.log($queue);
            }else{
            	$queue = $( '<ul class="filelist"></ul>' )
                .appendTo( $wrap.find( '.queueList' ) );
                console.log($queue);
            }
            
        if ( !WebUploader.Uploader.support('flash') && WebUploader.browser.ie ) {

            // flash 安装了但是版本过低。
            if (flashVersion) {
                (function(container) {
                    window['expressinstallcallback'] = function( state ) {
                        switch(state) {
                            case 'Download.Cancelled':
                                alert('您取消了更新！')
                                break;

                            case 'Download.Failed':
                                alert('安装失败')
                                break;

                            default:
                                alert('安装已成功，请刷新！');
                                break;
                        }
                        delete window['expressinstallcallback'];
                    };

                    var swf = 'expressInstall.swf';
                    // insert flash object
                    var html = '<object type="application/' +
                            'x-shockwave-flash" data="' +  swf + '" ';

                    if (WebUploader.browser.ie) {
                        html += 'classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" ';
                    }

                    html += 'width="100%" height="100%" style="outline:0">'  +
                        '<param name="movie" value="' + swf + '" />' +
                        '<param name="wmode" value="transparent" />' +
                        '<param name="allowscriptaccess" value="always" />' +
                    '</object>';

                    container.html(html);

                })($wrap);

            // 压根就没有安转。
            } else {
                $wrap.html('<a href="http://www.adobe.com/go/getflashplayer" target="_blank" border="0"><img alt="get flash player" src="http://www.adobe.com/macromedia/style_guide/images/160x41_Get_Flash_Player.jpg" /></a>');
            }

            return;
        } else if (!WebUploader.Uploader.support()) {
            alert( 'Web Uploader 不支持您的浏览器！');
            return;
        }

        // 实例化
        uploader = WebUploader.create({
            pick: {
                id: '#filePicker-2',
                label: '点击选择图片'
            },
            formData: {
                uid: 123
            },
            dnd: '#dndArea',
            paste: '#uploader',
            swf: 'lib/webuploader/0.1.5/Uploader.swf',
            chunked: false,
            chunkSize: 512 * 1024,
            server: '/admin/util/uploadfile',
            // runtimeOrder: 'flash',

            accept: {
                title: 'Images',
                extensions: 'gif,jpg,jpeg,bmp,png',
                mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif,image/bmp',
            },

            // 禁掉全局的拖拽功能。这样不会出现图片拖进页面的时候，把图片打开。
            disableGlobalDnd: true,
            fileNumLimit: 300,
            fileSizeLimit: 200 * 1024 * 1024,    // 200 M
            fileSingleSizeLimit: 50 * 1024 * 1024    // 50 M
        });

        // 拖拽时不接受 js, txt 文件。
        uploader.on( 'dndAccept', function( items ) {
            var denied = false,
                len = items.length,
                i = 0,
                // 修改js类型
                unAllowed = 'text/plain;application/javascript ';

            for ( ; i < len; i++ ) {
                // 如果在列表里面
                if ( ~unAllowed.indexOf( items[ i ].type ) ) {
                    denied = true;
                    break;
                }
            }

            return !denied;
        });

        uploader.on('dialogOpen', function() {
            console.log('here');
        });

        // uploader.on('filesQueued', function() {
        //     uploader.sort(function( a, b ) {
        //         if ( a.name < b.name )
        //           return -1;
        //         if ( a.name > b.name )
        //           return 1;
        //         return 0;
        //     });
        // });

        // 添加“添加文件”的按钮，
        uploader.addButton({
            id: '#filePicker2',
            label: '继续添加'
        });

        uploader.on('ready', function() {
            window.uploader = uploader;
        });

        // 当有文件添加进来时执行，负责view的创建
        function addFile( file ) {
            var $li = $( '<li id="' + file.id + '">' +
                    '<p class="title">' + file.name + '</p>' +
                    '<p class="imgWrap"></p>'+
                    '<p class="progress"><span></span></p>' +
                    '</li>' ),

                $btns = $('<div class="file-panel">' +
                    '<span class="cancel">删除</span>' +
                    '<span class="rotateRight">向右旋转</span>' +
                    '<span class="rotateLeft">向左旋转</span></div>').appendTo( $li ),
                $prgress = $li.find('p.progress span'),
                $wrap = $li.find( 'p.imgWrap' ),
                $info = $('<p class="error"></p>'),

                showError = function( code ) {
                    switch( code ) {
                        case 'exceed_size':
                            text = '文件大小超出';
                            break;

                        case 'interrupt':
                            text = '上传暂停';
                            break;

                        default:
                            text = '上传失败，请重试';
                            break;
                    }

                    $info.text( text ).appendTo( $li );
                };

            if ( file.getStatus() === 'invalid' ) {
                showError( file.statusText );
            } else {
                // @todo lazyload
                $wrap.text( '预览中' );
                uploader.makeThumb( file, function( error, src ) {
                    var img;

                    if ( error ) {
                        $wrap.text( '不能预览' );
                        return;
                    }

                    if( isSupportBase64 ) {
                        img = $('<img src="'+src+'">');
                        $wrap.empty().append( img );
                    } else {
                        $.ajax('/lib/webuploader/0.1.5/server/preview.php', {
                            method: 'POST',
                            data: src,
                            dataType:'json'
                        }).done(function( response ) {
                            if (response.result) {
                                img = $('<img src="'+response.result+'">');
                                $wrap.empty().append( img );
                            } else {
                                $wrap.text("预览出错");
                            }
                        });
                    }
                }, thumbnailWidth, thumbnailHeight );

                percentages[ file.id ] = [ file.size, 0 ];
                file.rotation = 0;
            }

            file.on('statuschange', function( cur, prev ) {
                if ( prev === 'progress' ) {
                    $prgress.hide().width(0);
                } else if ( prev === 'queued' ) {
                    // $li.off( 'mouseenter mouseleave' );
                    // $btns.remove();
                }

                // 成功
                if ( cur === 'error' || cur === 'invalid' ) {
                    console.log( file.statusText );
                    showError( file.statusText );
                    percentages[ file.id ][ 1 ] = 1;
                } else if ( cur === 'interrupt' ) {
                    showError( 'interrupt' );
                } else if ( cur === 'queued' ) {
                    percentages[ file.id ][ 1 ] = 0;
                } else if ( cur === 'progress' ) {
                    $info.remove();
                    $prgress.css('display', 'block');
                } else if ( cur === 'complete' ) {
                    $li.append( '<span class="success"></span>' );
                    $li.append('<div class="file-panel" style="height: 0px;"><span class="cancel">删除</span></div>');
                }

                $li.removeClass( 'state-' + prev ).addClass( 'state-' + cur );
            });

            $li.on( 'mouseenter', function() {
                $btns.stop().animate({height: 30});
            });

            $li.on( 'mouseleave', function() {
                $btns.stop().animate({height: 0});
            });

            $btns.on( 'click', 'span', function() {
                var index = $(this).index(),
                    deg;

                switch ( index ) {
                    case 0:
                        uploader.removeFile( file );
                        return;

                    case 1:
                        file.rotation += 90;
                        break;

                    case 2:
                        file.rotation -= 90;
                        break;
                }

                if ( supportTransition ) {
                    deg = 'rotate(' + file.rotation + 'deg)';
                    $wrap.css({
                        '-webkit-transform': deg,
                        '-mos-transform': deg,
                        '-o-transform': deg,
                        'transform': deg
                    });
                } else {
                    $wrap.css( 'filter', 'progid:DXImageTransform.Microsoft.BasicImage(rotation='+ (~~((file.rotation/90)%4 + 4)%4) +')');
                    // use jquery animate to rotation
                    // $({
                    //     rotation: rotation
                    // }).animate({
                    //     rotation: file.rotation
                    // }, {
                    //     easing: 'linear',
                    //     step: function( now ) {
                    //         now = now * Math.PI / 180;

                    //         var cos = Math.cos( now ),
                    //             sin = Math.sin( now );

                    //         $wrap.css( 'filter', "progid:DXImageTransform.Microsoft.Matrix(M11=" + cos + ",M12=" + (-sin) + ",M21=" + sin + ",M22=" + cos + ",SizingMethod='auto expand')");
                    //     }
                    // });
                }


            });

            $li.appendTo( $queue );
        }

        // 负责view的销毁
        function removeFile( file ) {
            var $li = $('#'+file.id);

            delete percentages[ file.id ];
            updateTotalProgress();
            // $li.off().find('.file-panel').off().end().remove();
        }

        function updateTotalProgress() {
            var loaded = 0,
                total = 0,
                spans = $progress.children(),
                percent;

            $.each( percentages, function( k, v ) {
                total += v[ 0 ];
                loaded += v[ 0 ] * v[ 1 ];
            } );

            percent = total ? loaded / total : 0;


            spans.eq( 0 ).text( Math.round( percent * 100 ) + '%' );
            spans.eq( 1 ).css( 'width', Math.round( percent * 100 ) + '%' );
            updateStatus();
        }

        function updateStatus() {
            var text = '', stats;

            if ( state === 'ready' ) {
                text = '选中' + fileCount + '张图片' +'。';
            } else if ( state === 'confirm' ) {
                stats = uploader.getStats();
                if ( stats.uploadFailNum ) {
                    text = '已成功上传' + stats.successNum+ '张照片至XX相册，'+
                        stats.uploadFailNum + '张照片上传失败，<a class="retry" href="#">重新上传</a>失败图片或<a class="ignore" href="#">忽略</a>'
                }

            } else {
                stats = uploader.getStats();
                text = '共' + $('.filelist li').length + '张' +'，已上传' + $('.filelist li').length + '张';

                if ( stats.uploadFailNum ) {
                    text += '，失败' + stats.uploadFailNum + '张';
                }
            }

            $info.html( text );
        }

        function setState( val ) {
            var file, stats;

            if ( val === state ) {
                return;
            }

            $upload.removeClass( 'state-' + state );
            $upload.addClass( 'state-' + val );
            state = val;

            switch ( state ) {
                case 'pedding':
                    $placeHolder.removeClass( 'element-invisible' );
                    $queue.hide();
                    $statusBar.addClass( 'element-invisible' );
                    uploader.refresh();
                    break;

                case 'ready':
                    $placeHolder.addClass( 'element-invisible' );
                    $( '#filePicker2' ).removeClass( 'element-invisible');
                    $queue.show();
                    $statusBar.removeClass('element-invisible');
                    uploader.refresh();
                    break;

                case 'uploading':
                    $( '#filePicker2' ).addClass( 'element-invisible' );
                    $progress.show();
                    $upload.text( '暂停上传' );
                    break;

                case 'paused':
                    $progress.show();
                    $upload.text( '继续上传' );
                    break;

                case 'confirm':
                    $progress.hide();
                    $( '#filePicker2' ).removeClass( 'element-invisible' );
                    $upload.text( '开始上传' );

                    stats = uploader.getStats();
                    if ( stats.successNum && !stats.uploadFailNum ) {
                        setState( 'finish' );
                        return;
                    }
                    break;
                case 'finish':
                    stats = uploader.getStats();
                    if ( stats.successNum ) {
                        alert( '上传成功' );
                    } else {
                        // 没有成功的图片，重设
                        state = 'done';
                        location.reload();
                    }
                    break;
            }

            updateStatus();
        }

        uploader.onUploadProgress = function( file, percentage ) {
            var $li = $('#'+file.id),
                $percent = $li.find('.progress span');

            $percent.css( 'width', percentage * 100 + '%' );
            percentages[ file.id ][ 1 ] = percentage;
            updateTotalProgress();
        };

        uploader.onFileQueued = function( file ) {
            fileCount++;
            fileSize += file.size;

            if ( fileCount === 1 ) {
                $placeHolder.addClass( 'element-invisible' );
                $statusBar.show();
            }

            addFile( file );
            setState( 'ready' );
            updateTotalProgress();
        };

        uploader.onFileDequeued = function( file ) {
            fileCount--;
            fileSize -= file.size;

            if ( !fileCount ) {
                setState( 'pedding' );
            }

            removeFile( file );
            updateTotalProgress();

        };

        uploader.on( 'all', function( type ) {
            var stats;
            switch( type ) {
                case 'uploadFinished':
                    setState( 'confirm' );
                    break;

                case 'startUpload':
                    setState( 'uploading' );
                    break;

                case 'stopUpload':
                    setState( 'paused' );
                    break;

            }
        });

        uploader.onError = function( code ) {
            alert( 'Eroor: ' + code );
        };
        uploader.on( 'uploadSuccess', function( file,response) {
        	$("li#"+file.id).append("<input name='desc[]' type='hidden' value='"+response._raw+"'>");
        })
        $upload.on('click', function() {
            if ( $(this).hasClass( 'disabled' ) ) {
                return false;
            }

            if ( state === 'ready' ) {
                uploader.upload();
            } else if ( state === 'paused' ) {
                uploader.upload();
            } else if ( state === 'uploading' ) {
                uploader.stop();
            }
        });

        $info.on( 'click', '.retry', function() {
            uploader.retry();
        } );

        $info.on( 'click', '.ignore', function() {
            alert( 'todo' );
        } );

        $upload.addClass( 'state-' + state );
        updateTotalProgress();
    });

})( jQuery );
	// $(function(){
	// 	var ue = UE.getEditor('editor');
	// 	//判断ueditor 编辑器是否创建成功
		
 //        ue.addListener("ready", function () {
	//         // editor准备好之后才可以使用
	//         UE.getEditor('editor').execCommand('insertHtml', '@if($product){!!htmlspecialchars_decode($product->description)!!}@endif')

 //        });
	// });
	$("#upload").change(function(){
		if(window.FileReader){
            var oFReader = new FileReader();
            var file = this.files[0];
            //如果要限定上传文件的类型，可以通过文件选择器获取文件对象并通过type属性来检查文件类型
            var sReg = /^(?:image\/bmp|image\/cis\-cod|image\/gif|image\/ief|image\/jpeg|image\/jpeg|image\/jpeg|image\/pipeg|image\/png|image\/svg\+xml|image\/tiff|image\/x\-cmu\-raster|image\/x\-cmx|image\/x\-icon|image\/x\-portable\-anymap|image\/x\-portable\-bitmap|image\/x\-portable\-graymap|image\/x\-portable\-pixmap|image\/x\-rgb|image\/x\-xbitmap|image\/x\-xpixmap|image\/x\-xwindowdump)$/i;

            if(!sReg.test(file.type)){
                layer.msg('只允许上传图片文件!',{icon:0,time:1000});
            }
            oFReader.onloadend = function(e) {
                document.getElementById("img").src = e.target.result;
            };

            oFReader.readAsDataURL(file);
        }
	});
	$("#btn-star").click(function(){
		var src = document.getElementById("img").src;
		if($.trim(src)==""){
			layer.msg('请上传资源!',{icon:0,time:1000});
		}
		var file = $("#upload")[0];
		if(file.files[0]!=undefined){
			_up(file,'{{ route('admin.util.uploadfile') }}',$("input[name=photos]"));
		}
		
	})
</script>