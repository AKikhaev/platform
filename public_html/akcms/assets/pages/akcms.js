var _akcms = _akcms || {};
_akcms.loadaing = {
    template:$("<div class='loading'></div>"),
    tag:null,
    start: function(){this.tag = this.template.clone().appendTo(document.body);},
    finish: function(){if (this.tag!==null) { this.tag.remove(); }}
};
_akcms.ru2lt = {
    t : function (inp) {
        var a = inp.toLowerCase().split("");
        for (var i=0,aL=a.length;i<aL;i++) {var c = this.ru2en[a[i]]; a[i] = c==null?a[i]:c;}
        var s =  a.join("");
        return s.replace(/[^0-9a-z_-]/g, "");
    },
    init:function() {
        this.ru_str = "абвгдеёжзийклмнопрстуфхцчшщъыьэюя№ ";
        this.lt_str = ["a","b","v","g","d","e","e","zh","z","i","j","k","l","m","n","o","p","r","s","t","u","f",
            "h","c","ch","sh","shh","","y","","e","yu","ya","n","_"];
        this.ru2en = {};
        for(var i = 0,l = this.ru_str.length; i < l; i++) {
            this.ru2en[this.ru_str.charAt(i)] = this.lt_str[i];
        }
    }
};_akcms.ru2lt.init();
_akcms.alerts={
    template: $("<div class='alert alert-dismissible fade show' role='alert'><button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>"),
    show:function(msg,type,wait){
        var el = this.template.clone().prepend("<span>"+msg+"</span>");
        if (type!==undefined) { el.addClass("alert-"+type); }
        el.prependTo($("#alertContainer")).alert();
        if (wait!==undefined && wait>0) {
            window.setTimeout(function () {
                el.alert("close");
            }, wait);
        }
    },
    primary:  function (msg) { this.show(msg,"primary"); },
    secondary:function (msg) { this.show(msg,"secondary"); },
    success:  function (msg) { this.show(msg,"success",5000); },
    danger:   function (msg) { this.show(msg,"danger"); },
    warning:  function (msg) { this.show(msg,"warning"); },
    info:     function (msg) { this.show(msg,"info",5000); },
    light:    function (msg) { this.show(msg,"light"); },
    dark:     function (msg) { this.show(msg,"dark"); }
};
_akcms.floodSelect=function(selTag,selItms,_id,_name,defId,promptText,_class) {
    selTag.empty();
    if (promptText!==undefined && promptText!=="") { selTag.append($("<option value='A-' style='font-weight:bold;'/>").text(promptText)); }
    $.each(selItms,function(n,selItm) {
        var option = $("<option/>").text(selItm[_name]).val(selItm[_id]).appendTo(selTag);
        if (selItm[_class]!==undefined) { option.addClass(selItm[_class]); }
    });
    if (defId!==undefined && defId!=="") { selTag.val(defId); }
};
_akcms.switchClass=function(isTrue,el,className){
    if (isTrue) { el.addClass(className); }
    else { el.removeClass(className); }
};
