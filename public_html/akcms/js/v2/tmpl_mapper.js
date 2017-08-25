function occurrences(string, subString, allowOverlapping) {

    string += ""; subString += "";
    if (subString.length <= 0) return (string.length + 1);

    var n = 0, pos = 0, step = allowOverlapping ? 1 : subString.length;

    while (true) {
        pos = string.indexOf(subString, pos);
        if (pos >= 0) {
            ++n;
            pos += step;
        } else break;
    }
    return n;
}

function makeXPath (node, currentPath) {
    /* this should suffice in HTML documents for selectable nodes, XML with namespaces needs more code */
    currentPath = currentPath || '';
    switch (node.nodeType) {
        case 3:
        case 4:
            return makeXPath(node.parentNode, 'text()[' + (document.evaluate('preceding-sibling::text()', node, null, XPathResult.ORDERED_NODE_SNAPSHOT_TYPE, null).snapshotLength + 1) + ']');
        case 1:
            return makeXPath(node.parentNode, node.nodeName + '[' + (document.evaluate('preceding-sibling::' + node.nodeName, node, null, XPathResult.ORDERED_NODE_SNAPSHOT_TYPE, null).snapshotLength + 1) + ']' + (currentPath ? '/' + currentPath : ''));
        case 9:
            return '/' + currentPath;
        default:
            return '';
    }
}

var _getSelected = function () {
//			try
    {
        //https://stackoverflow.com/questions/5379120/get-the-highlighted-selected-text
        var w = window,d = w.document,_txt = null,_sel = null,expanded=false;
        if (w.getSelection) {
            _sel = w.getSelection();
        } else if (d.getSelection) {
            _sel = d.getSelection();
        }
        if (_sel != null) {
            var _txt = null;
            if (_sel.getRangeAt) {
                var r = _sel.getRangeAt(0);
                //locate = r.commonAncestorContainer.innerHTML;
                //r.setStartBefore(r.startContainer);
                //r.setEndAfter(r.endContainer);
                var findStart = r.startContainer,findEnd = r.endContainer;
                //console.log(r.commonAncestorContainer,r.startContainer,r.endContainer);
                if (r.commonAncestorContainer===r.startContainer || r.commonAncestorContainer===r.startContainer.parentNode) {} else {
                    while(findStart.parentNode!==r.commonAncestorContainer && findStart!==r.commonAncestorContainer) findStart=findStart.parentNode;
                    r.setStartBefore(findStart);
                    expanded = true;
                }
                if (r.commonAncestorContainer===r.endContainer || r.commonAncestorContainer===r.endContainer.parentNode) {} else {
                    while(findEnd.parentNode!==r.commonAncestorContainer && findEnd!==r.commonAncestorContainer) findEnd=findEnd.parentNode;
                    r.setEndAfter(findEnd);
                    expanded = true;
                }
                _txt = r.toString();
                var div = document.createElement('div');
                div.appendChild( r.cloneContents().cloneNode(true) ); //
                var locateEl = r.commonAncestorContainer.outerHTML!==undefined?r.commonAncestorContainer:r.commonAncestorContainer.parentNode;
                _txt = {
                    //'text':_txt,
                    'html':div.innerHTML,
                    //'ancestor':r.commonAncestorContainer,
                    //'pathAncestor':makeXPath(r.commonAncestorContainer),
                    //'pathStart':makeXPath(findStart),
                    //'pathEnd':makeXPath(findEnd),
                    'locate':locateEl.outerHTML,
                    'locate_tag':locateEl.tagName,
                    'whole':div.innerHTML.trim()===locateEl.innerHTML.trim(),
                    'expanded':expanded,
                    'html_count':occurrences(locateEl.outerHTML,div.innerHTML,false),
                    'locate_count':occurrences(document.head.outerHTML+document.body.outerHTML,locateEl.outerHTML,false)
                };
                //

            }
            return _txt;
        } else {
            return null;
        }
    }
//			catch (e) {
//				return null;
//			}
};

$(window).keyup(function(e){
    if (e.shiftKey && e.which==32) {
        e.preventDefault();
        e.stopPropagation();

        if (confirm("Отменить последнюю вставку?"))
        {
            $.ajax({
                type: 'POST',
                url: '/ajx/_tmpl/'+_akcms.template+'/' + '_cr_undo',
                data: {confirm:1},
                success: function (sres) {
                    if (sres==='t') {
                        alert("Предыдущее состояние восстановлено.");
                        window.location.reload();
                    }
                    else if (sres==='nobackup') alert("НЕТ ДАННЫХ восстановления!");
                    else if (sres==='i') alert("Файлы ИДЕНТИЧНЫ!");
                    else if (sres==='f') alert("НЕ УДАЛОСЬ восстановить!");
                    else alert("Неизвестная ошибка!");
                },
                dataType: 'json'
            });
        }
    }

    if (e.ctrlKey && !e.altKey && e.which==32) {
        e.preventDefault();
        e.stopPropagation();

        console.clear();
        var selected = _getSelected();
        console.log('html: '+selected.html);
        console.log('locate: '+selected.locate);

        var text = 'Заменить фрагмент?\nВведите идентификатор'+"\n\n";
        text += "Родитель: "+selected.locate_tag+"\n";
        text += "Длина фрагмента: "+selected.html.length+"\n\n";
        if (selected.whole) text += "Весь родитель выделен"+"\n";
        if (selected.expanded) {
            text += "Фрагмент расширен"+"\n";
        }
        if (selected.html_count===0 || selected.html_count>1) text += "Замен текста: "+selected.html_count+"\n";
        if (selected.locate_count===0 || selected.locate_count>1) text += "Замен родителя: "+selected.locate_count+"\n";

        text += "\n! - замена включая родителя"+"\n";
        text += "* - не закрывать тег замены"+"\n";
        text += "\nПримеры:"+"\n";
        text += "ep:content:m - основной контент"+"\n";
        text += "ep:namefull:l - заголовок страницы"+"\n";
        var answer = prompt(text+' ','!');
        if(answer!==null) {
            if (answer.length>4) {
                selected.label = answer;
                $.ajax({
                    type: 'POST',
                    url: '/ajx/_tmpl/'+_akcms.template+'/' + '_cr',
                    data: selected,
                    success: function (sres) {
                        if (sres.error!==undefined) alert(sres.error);
                        else if (sres.status!==undefined) {
                            alert(sres.status);
                            window.location.reload();
                        }
                        console.log(sres);
                    },
                    dataType: 'json'
                });
            } else alert('Слишком короткий идентификатор');
        }
        /*
*/
        //document.getSelection().selectAllChildren($p)
    }
});

console.log('loaded');