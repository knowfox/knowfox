/**
 */
fetch('https://knowfox.dev/bookmark', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: 'url=' + encodeURIComponent(location.href) + '&title=' + encodeURIComponent(document.title)
}).then(function(response) {
    var dialog = document.createElement('div');
    dialog.style = 'top:0;right:0;width:200px;height:200px;position:absolute;background:#FFF;'
    dialog.innerHTML = response;
    document.body.appendChild(dialog);
});
