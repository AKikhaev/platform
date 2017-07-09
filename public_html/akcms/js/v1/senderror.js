var sendErrorObj = {
    title:"Орфографическая ошибка в тексте",
    info:"Заголовок статьи: ",
    errWinId:"send_error_text_b",

    initialize:function () {
        window.addEvent('keydown', function (event) {
            if (event.control && event.key === 'enter') {
                if ($(this.errWinId) != undefined) {
                    event.stop();
                    return;
                }
                this.initErrorText();
            }
        }.bind(this));
    },

    /**
     * @return object {w, h, x, y}
     */
    createPosition:function () {
        var _2a = 0, _2b = 0;
        if (typeof (window.innerWidth) == "number") {
            _2a = window.innerWidth;
            _2b = window.innerHeight;
        } else {
            if (document.documentElement && (document.documentElement.clientWidth || document.documentElement.clientHeight)) {
                _2a = document.documentElement.clientWidth;
                _2b = document.documentElement.clientHeight;
            } else {
                if (document.body && (document.body.clientWidth || document.body.clientHeight)) {
                    _2a = document.body.clientWidth;
                    _2b = document.body.clientHeight;
                }
            }
        }
        var _2c = 0, _2d = 0;
        if (typeof (window.pageYOffset) == "number") {
            _2d = window.pageYOffset;
            _2c = window.pageXOffset;
        } else {
            if (document.body && (document.body.scrollLeft || document.body.scrollTop)) {
                _2d = document.body.scrollTop;
                _2c = document.body.scrollLeft;
            } else {
                if (document.documentElement && (document.documentElement.scrollLeft || document.documentElement.scrollTop)) {
                    _2d = document.documentElement.scrollTop;
                    _2c = document.documentElement.scrollLeft;
                }
            }
        }
        return {
            w:_2a,
            h:_2b,
            x:_2c,
            y:_2d
        };

    },

    addToBody:function (elem) {
        elem.style.position = "absolute";
        elem.style.top = "-10000px";
        if (document.body.lastChild) {
            document.body.insertBefore(elem, document.body.lastChild);
        } else {
            document.body.appendChild(elem);
        }
    },

    createForm:function (text, arrinfo) {
        var _12 = "";
        var b = document.body;
        var div = document.createElement("DIV");
        var w = 550;
        if (w > b.clientWidth - 10) {
            w = b.clientWidth - 10;
        }
        div.style.zIndex = "10001";
        div.innerHTML = "" + "<div id=\"" + this.errWinId + "\" style=\"background:#d6cefd; width:"
            + w + "px; z-index:10001; border: 1px solid #555; padding:1em; font-family: Arial; font-size: 90%; color:black\">" + "<div style=\"font-weight:bold; padding-bottom:0.2em\">"
            + this.title + "</div>" + "<div style=\"padding: 0 0 1em 1em\">"
            + text + "</div>" + "<div style=\"padding: 0 0 1em 0\">"
            + this.info + arrinfo.title + "</div>" + "<form style=\"padding:0; margin:0; border:0\">" + "<div>"
            + 'Вы можете оставить комментарий (необязательно)' + "</div>" + "<input type=\"text\" maxlength=\"250\" style=\"width:100%; margin: 0.2em 0\" />"
            + "<div style=\"text-align:right; font-family: Tahoma,serif\">" + "<input type=\"submit\" value=\"Отправить\" style=\"width:9em; font-weight: bold\">&nbsp;" + "<input type=\"button\" value=\"Отменить\" style=\"width:9em\">" + "</div>" + "</form>" + "</div>" + "";

        this.addToBody(div);
        var _3a = div.getElementsByTagName("input");
        var _3b = div.getElementsByTagName("form");
        var t = _3a[0];
        var _3d = null;
        var _3e = [];
        var _3f = function () {
            document.onkeydown = _3d;
            _3d = null;
            div.parentNode.removeChild(div);
            for (var i = 0; i < _3e.length; i++) {
                _3e[i][0].style.visibility = _3e[i][1];
            }
            _12 = t.value;
        };
        var pos = function (p) {
            var s = {
                x:0,
                y:0
            };
            while (p.offsetParent) {
                s.x += p.offsetLeft;
                s.y += p.offsetTop;
                p = p.offsetParent;
            }
            return s;
        };
        setTimeout(function () {
            var w = div.clientWidth;
            var h = div.clientHeight;
            var dim = sendErrorObj.createPosition();
            var x = (dim.w - w) / 2 + dim.x;
            if (x < 10) {
                x = 10;
            }
            var y = (dim.h - h) / 2 + dim.y - 10;
            if (y < 10) {
                y = 10;
            }
            div.style.left = x + "px";
            div.style.top = y + "px";
            if (navigator.userAgent.match(/MSIE (\d+)/) && RegExp.$1 < 7) {
                var _49 = document.getElementsByTagName("SELECT");
                for (var i = 0; i < _49.length; i++) {
                    var s = _49[i];
                    var p = pos(s);
                    if (p.x > x + w || p.y > y + h || p.x + s.offsetWidth < x || p.y + s.offsetHeight < y) {
                        continue;
                    }
                    _3e[_3e.length] = [s, s.style.visibility];
                    s.style.visibility = "hidden";
                }
            }
            t.value = _12;
            t.focus();
            t.select();
            _3d = document.onkeydown;
            document.onkeydown = function (e) {
                if (!e) {
                    e = window.event;
                }
                if (e.keyCode == 27) {
                    _3f();
                }
            };
            _3b[0].addEvent('submit', function (event) {
                event.stop();
                sendErrorObj.sendErrorText(text, t.value, arrinfo);
                _3f();
            });
            _3a[2].onclick = function () {
                _3f();
            };

        }, 10);
    },

    initErrorText:function () {
        var selText = this.getText();//this.getSelText()[0].toString().trim();
        if (selText == false) return;
        var arrinfo = {
            title:$$('h1')[0].get('text').trim()
        };
        if (selText == '' || selText == undefined) {
            alert('Вы не выделили текст с ошибкой');
            return;
        }

        this.createForm(selText, arrinfo);
    },

    sendErrorText:function (selText, comment, arrinfo) {
        new Request.JSON({
            url:'/ajx/_smdmstk',
            onComplete:function (sres) {
                if (sres != undefined) {
                    if (sres == 't') {
                        alert('Ваше сообщение отправлено');
                    } else {
                        alert('Ошибка отравки сообщения\n');
                    }
                } else {
                    alert('Ошибка отравки сообщения\n');
                }

            }
        }).post({
                selText:selText,
                href:location.href,
                comment:comment,
                title:arrinfo.title
            });

    },

    getText:function () {
        var _4 = "<!!!>";
        var _5 = "<!!!>";
        var _6 = 60;
        var _7 = 256;
        var _8 = {
            badbrowser:"Ваш браузер не поддерживает возможность перехвата выделенного текста или IFRAME. Возможно, слишком старая версия, а возможно, еще какая-нибудь ошибка.",
            toobig:"Вы выбрали слишком большой объем текста!"
        };
        var w = window;
        var d = w.document;
        var _4e = function (_4f) {
            return ("" + _4f).replace(/[\r\n]+/g, " ").replace(/^\s+|\s+$/g, "");
        };
        var _50 = function () {
            try {
                var _51 = null;
                var _52 = null;
                if (w.getSelection) {
                    _52 = w.getSelection();
                } else {
                    if (d.getSelection) {
                        _52 = d.getSelection();
                    } else {
                        _52 = d.selection;
                    }
                }
                var _53 = null;
                if (_52 != null) {
                    var pre = "", _51 = null, suf = "", pos = -1;
                    if (_52.getRangeAt) {
                        var r = _52.getRangeAt(0);
                        _51 = r.toString();
                        var _58 = d.createRange();
                        _58.setStartBefore(r.startContainer.ownerDocument.body);
                        _58.setEnd(r.startContainer, r.startOffset);
                        pre = _58.toString();
                        var _59 = r.cloneRange();
                        _59.setStart(r.endContainer, r.endOffset);
                        _59.setEndAfter(r.endContainer.ownerDocument.body);
                        suf = _59.toString();
                    } else {
                        if (_52.createRange) {
                            var r = _52.createRange();
                            _51 = r.text;
                            var _58 = _52.createRange();
                            _58.moveStart("character", -_6);
                            _58.moveEnd("character", -_51.length);
                            pre = _58.text;
                            var _59 = _52.createRange();
                            _59.moveEnd("character", _6);
                            _59.moveStart("character", _51.length);
                            suf = _59.text;
                        } else {
                            _51 = "" + _52;
                        }
                    }
                    var p;
                    var s = (p = _51.match(/^(\s*)/)) && p[0].length;
                    var e = (p = _51.match(/(\s*)$/)) && p[0].length;
                    pre = pre + _51.substring(0, s);
                    suf = _51.substring(_51.length - e, _51.length) + suf;
                    _51 = _51.substring(s, _51.length - e);
                    if (_51 == "") {
                        return null;
                    }
                    return {
                        pre:pre,
                        text:_51,
                        suf:suf,
                        pos:pos
                    };

                } else {
                    alert(_8.badbrowser);
                }
            } catch (e) {
                return null;
            }
        };
        var _5d = function () {
            if (navigator.appName.indexOf("Netscape") != -1 && eval(navigator.appVersion.substring(0, 1)) < 5) {
                alert(_8.badbrowser);
                return;
            }
            var _63 = _50();
            if (!_63) {
                return;
            }
            with (_63) {
                pre = pre.substring(pre.length - _6, pre.length).replace(/^\S{1,10}\s+/, "");
                suf = suf.substring(0, _6).replace(/\s+\S{1,10}$/, "");
            }
            var _64 = _4e(_63.pre + _4 + _63.text + _5 + _63.suf);
            if (_64.length > _7) {
                alert(_8.toobig);
                return false;
            }
            return _64.replace(_4, "<u style=\"color:red\">").replace(_5, "</u>");
        };
        return _5d();
    }

};

window.addEvent('domready',function(){
    if (!mnpg) {
		sendErrorObj.initialize();
		var pgc=$('pgcontent');
		if (pgc!=null && pgc.get('text').trim()!='')
			new Element('div',{'style':'color: #999999; font-size: 10px; padding: 0 10px 10px;','text':'Если заметили в тексте опечатку, выделите ее и нажмите Ctrl+Enter'}).inject(pgc);
	}
});
