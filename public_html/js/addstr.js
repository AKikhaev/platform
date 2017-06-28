// объект для работы с uri
function js_http(params) {
	this.time_step = 80;
	this.interval = null;
	this.obj = {};
	
	if (params.callFunct) this.callFunctionIsChangeUri = params.callFunct;
                     else this.callFunctionIsChangeUri = '';
	
	var base_root = window.location.href;
	
	this.old_uri = this.base_root = base_root.replace(new RegExp("#.*","ig"), "");
	
	// Возвращает базовый путь включая GET параметры
	this.getBaseUri = function () {
        return this.base_root;
    };
	
	// Устанавливает обрабатывающую функцию в случии изменения адресной строки
	this.setCallIsChange = function (call) {
        this.callFunctionIsChangeUri = call;
    };
	
	// Воззвращает запрашиваемый параметр
	this.getParam = function (name) {
        try {
            var out = eval('this.obj.' + name);
            if (out == undefined) out = false;
        } catch (e) {
            var out = false;
        }
        return out;
    };
	
	this.setParam = function (name, val) {
        eval('this.obj.' + name + '= val;');
        var new_href = this.getBaseUri() + '#';
        var first = true;
        for (ch in this.obj) {
            if (first) {
                first = false;
                new_href += ch + '=' + encodeURIComponent(this.obj[ch]);
            } else {
                new_href += '/' + ch + '=' + encodeURIComponent(this.obj[ch]);
            }
        }
        window.location.href = new_href;
    };
	
	this.setParams = function (obj) {
        for (pref in obj) {
            eval('this.obj.' + pref + '= obj[pref];');
        }
        var new_href = this.getBaseUri() + '#';
        var first = true;
        for (ch in this.obj) {
            if (first) {
                first = false;
                new_href += ch + '=' + encodeURIComponent(this.obj[ch]);
            } else {
                new_href += '/' + ch + '=' + encodeURIComponent(this.obj[ch]);
            }
        }
        window.location.href = new_href;
    };
	
	this.clear = function () {
        this.obj = {};
        //window.location.href = this.getBaseUri() + '#';
    };
	
	// Проверяет на изменеия в адресной строке
	this.changeUri = function(){
		if (this.old_uri != window.location.href) {
			this.old_uri = window.location.href;
			this.creatUriObj();
			try {
				var vr_cl = this;
				this.callFunctionIsChangeUri(vr_cl);
			}catch(e) {
			}
		}
		if (!this.interval)	this.interval = setInterval(this.changeUri.bind(this), this.time_step);
	};
	
	// Служебная функция
	this.creatUriObj = function () {
        this.obj = {};
        var hparam = window.location.href;
        hparam = hparam.replace(new RegExp(".*#", "ig"), "");
        if (hparam == this.getBaseUri()) return false;
        var m_param = hparam.split("/");

        for (var i = 0; i < m_param.length; i++) {
            var vr_m = m_param[i].split("=");
            try {
                var val = decodeURIComponent(vr_m[1]);
                if (val == undefined) val = 'is';
            } catch (e) {
                var val = 'is';
            }
            if (vr_m[0].length > 0) eval('this.obj.' + vr_m[0] + '= val;');
        }
    };
	
	this.creatUriObj();
	this.changeUri();
}