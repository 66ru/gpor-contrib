function surroundText (text1, text2) {
    var textarea = document.getElementById('comments_textarea');
    if (typeof(textarea.caretPos) != "undefined" && textarea.createTextRange) {
        var caretPos = textarea.caretPos, temp_length = caretPos.text.length;
        caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? text1 + caretPos.text + text2 + ' ' : text1 + caretPos.text + text2;
        if (temp_length == 0) {
            caretPos.moveStart("character", -text2.length);
            caretPos.moveEnd("character", -text2.length);
            caretPos.select();
        }
        else {
            textarea.focus(caretPos);
        }
    } else if (typeof(textarea.selectionStart) != "undefined") {
        var begin = textarea.value.substr(0, textarea.selectionStart);
        var selection = textarea.value.substr(textarea.selectionStart, textarea.selectionEnd - textarea.selectionStart);
        var end = textarea.value.substr(textarea.selectionEnd);
        var newCursorPos = textarea.selectionStart;
        var scrollPos = textarea.scrollTop;

        textarea.value = begin + text1 + selection + text2 + end;

        if (textarea.setSelectionRange) {
            if (selection.length == 0) {
                textarea.setSelectionRange(newCursorPos + text1.length, newCursorPos + text1.length);
            } else {
                textarea.setSelectionRange(newCursorPos, newCursorPos + text1.length + selection.length + text2.length);
            }
            textarea.focus();
        }
        textarea.scrollTop = scrollPos;
    }
    else {
        textarea.value += text1 + text2;
        textarea.focus(textarea.value.length - 1);
    }
}