var comment_form = {

    build: function () {
        this._buildContainer();
        this._buildCommentsEditor();
        this._buildInsertedLink();
        this._buildSubmitButton();
    },

    _buildContainer: function () {
        var textarea = $("#comments_textarea");
        var container = $("<div class='code_frame' />");
        textarea.after(container);
        container.append(textarea.clone());
        textarea.remove();
        this.container = $(".code_frame");
    },

    _buildCommentsEditor: function () {
        var textarea = this.container.children("textarea");
        var form =
        "<form action='/comments/post/' class='js_comment_form ie_layout' method='post' id='newComment-form'>" +
            "<div class='js_comment_form-pad'>" +
                "<table class='comments_editor'><tr>" +
                    "<td class='comments_editor__editor-label'><label for=''>Текст:</label></a></td><td class='commands_container'></td><td class='comments_editor__editor-rules'></td>" +
                "</tr></table>" +
            "</div>" +
        "</form>";
        
        this.container.append(form);
        var table_out = this.container.find(".js_comment_form-pad");
        table_out.append(textarea)
                //.append("<a class='increase-height-link' href='#' onClick='increaseHeight(); return false;'><i class='increase-height-link__l'></i><i class='increase-height-link__r'></i></a>");

    },

    _buildInsertedLink: function () {
        var inserted_link = $("<div id='inserted_link'></div>");
        inserted_link.html(
            "<table class='inserted_link_table'>" +
                "<tr class='top'>" +
                    "<td class='left'>" +
                        "<img src='/img/confirm_ico.gif' />" +
                    "</td>" +
                    "<td class='right'>" +
                        "<div>" +
                            "<input class='inserted_link_content' type='text' value='http://' />" +
                        "</div>" +
                    "</td>" +
                "</tr>" +
                "<tr class='bottom'>" +
                    "<td colspan='2'>" +
                        "<div>" +
                            "<input class='inserted_link_submit' type='image' title='Да' alt='Да' src='/img/button_yes.gif' />" +
                            "&nbsp;" +
                            "<img class='inserted_link_cancel' title='Отмена' alt='Отмена' src='/img/cancel.gif' />" +
                        "</div>" +
                    "</td>" +
                "</tr>" +
            "</table>"
        );
        this.container.append(inserted_link);
    },

    _buildSubmitButton: function () {
        var submitButton = 
        "<div class='js_comment_form-bottom context'>" +
            "<div class='js_comment_form-bottom_but'>" +
                "<i class='button buttons_wrap rc3'>" +
                    "<b class='opera_inline-block-wrap'>" +
                        "<input type='submit' value='Отправить' id='submit-comment' name='submitComment' class='forms_submit forms_submit js_comments_forms_submit'>" +
                    "</b>" +
                "</i>" +
            "</div>" +
        "</div>";
        $(".js_comment_form").append(submitButton);
    },

    // событие на отправку формы
    submit: function (f) {
        $(".code_frame").find(".js_comment_form").bind("submit", function () {
            f();
        });
    },

    setIframeUrl: function (url) {
        this.iFrameUrl = url;
    },

    // добавление смайлов
    smiles: function (smilesCollection) {
        var table = $("<table id='smiles_table'></table>");
        var lensRow = parseInt(smilesCollection.length / 8) + 1;
        for (var i = 0; i < lensRow; ++i) {
            var tr = $("<tr>");
            for (var j = 0; j < 8; ++j) {
                var td = $("<td>");
                var smile = smilesCollection[i * 8 + j];
                if (smile) {
                    td.html("<a href='#' alt='" + smile.content + "' title='"+ smile.content  +"' class='commentSmilesForm-smile'>"+
                                "<img src='" + smile.url + "' />" +
                            "</a>");
                }
                tr.append(td);
            }
            table.append(tr);
        }
        this.container.append(table);
    },

    commands: function (collectionCommand) {
        this.commandsContainer = this.container.find(".commands_container");
        for (var i = 0, len = collectionCommand.length; i < len; ++i) {
            var command = collectionCommand[i];

            // нет контейнера - создаем
            if (!this._isHasContainer(command.containerIndex)) {
                var countHtmlCommandContainer = this.commandsContainer.children().length;
                // если нет 3-го контейнера, то создаем с последнего существующего до 3-го
                for (var j = countHtmlCommandContainer; j <= command.containerIndex; ++j) {
                    this.commandsContainer.append($("<div />"));
                }
            }
            var htmlContainer = this.commandsContainer.children().eq(command.containerIndex);
            this._createHtmlCommand(command, htmlContainer);
        }
        actionCommands();
    },

    /**
     * создаем html-представление команды и добавляем в форму
     * @param objCommand
     * @param htmlContainer
     */
    _createHtmlCommand: function (objCommand, htmlContainer) {
        var img = $("<img />", {
            src: objCommand.src,
            textvalue: objCommand.textvalue,
            title: objCommand.title,
            class: objCommand.type + " " + objCommand.textvalue
        });
        htmlContainer.append(img);
    },

    /**
     * проверяет есть ли порядковый div-блок для команды (у нас группы команд разделяются дефисом)
     * @param index
     */
    _isHasContainer: function (index) {
        if (this.commandsContainer.children().eq(index).html()) {
            return true;
        }
    }

};