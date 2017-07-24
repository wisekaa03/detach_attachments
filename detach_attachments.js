/***************************************************************************
 * This file is part of Roundcube "detach_attachments" plugin.              
 ***************************************************************************/

function detach_attachments_counts(a) {
    rcmail.http_post("plugin.dta_counts","_file=" + encodeURIComponent(a)+"&_hash=" + rcmail.env.user_hash);
}

function detach_attachments_callback(a) {
    "?" == a ?
        document.getElementById("messagepartframe").contentWindow.document.getElementById("counts").style.display = "none"
    :
        (
            document.getElementById("messagepartframe").contentWindow.document.getElementById("downloads").innerHTML = a,
            document.getElementById("messagepartframe").contentWindow.document.getElementById("counts").style.display = "block"
        )
    ;
    document.getElementById("messagepartframe").contentWindow.document.body.style.display = "block";
}


function detach_attachments_download(a) {
    confirm(rcmail.gettext("detach_attachments.downloadquestion")) ?
        (
            a = a + "&_delete=" + rcmail.env.user_hash,
            document.getElementById("messagepartframe").contentWindow.document.getElementById("counts").innerHTML = "",
            document.location.href = a
        )
    :
        confirm(rcmail.gettext("detach_attachments.deleteconfirmation")) && (
            a = a.split("&_dla="),
            rcmail.http_post("plugin.dla_delete","_file=" + encodeURIComponent(a[1]) + "&_hash=" + rcmail.env.user_hash)
        )
    ;
    return !1;
}


$(window).load(function(){

    var a = document.getElementById("messagepartframe").contentWindow.document.getElementById(rcmail.env.user_hash);

    if(a && a.href) {
        $(".download-link").remove();
        var b = document.getElementById("messagepartframe").contentWindow.document.body.innerHTML,
            c = '<div id="counts" style="display: none;"><div><p><a onclick="return parent.detach_attachments_download(\'' +
                a.href + '\')" href="' + a.href + "&_delete="+rcmail.env.user_hash+'" target=_self>'+rcmail.gettext("detach_attachments.deletelink") +
                '</a>&nbsp;[<span id="downloads">0</span>&nbsp;download(s)]</p></div>',
            c = c +
                "<div><p><small>" + rcmail.gettext("detach_attachments.hint") + "</small></p></div>",
            b = b + ( c + "</div></body>");
        document.getElementById("messagepartframe").contentWindow.document.body.innerHTML = b;
        a = unescape(a.href).split("&_dla=");
        rcmail.addEventListener("plugin.dta_counts",detach_attachments_callback);
        detach_attachments_counts(a[1])
    }

});