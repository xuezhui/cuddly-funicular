/**
 * Created by Change on 2017-3-30.
 */
function _up(elFile,action,input) {
    var file = elFile.files[0];
    var fileExtend = file.name.substring(file.name.lastIndexOf('.')).toLowerCase();
    var ext = (".gif,.jpg,.jpeg,.bmp,.png").split(',');
    if (ext.indexOf(fileExtend) < 0) {
        // options.error(file.name);
        return;
    }

    var fd = new FormData();
    fd.append("width", 640);
    fd.append("height", 640);
    fd.append("file", file);
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var b = xhr.responseText;
            $(input).val(b);
            layer.msg('已上传!',{icon:1,time:1000});
            // options.success(b);
        }
    };

    xhr.open("POST", action);
    xhr.send(fd);
}
