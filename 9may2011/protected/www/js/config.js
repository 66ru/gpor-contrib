$.fn.commentForm = function (obj) {
    var obj = obj || {};
    comment_form.build();

    comment_form.submit(obj.submit || function () {});

    comment_form.setIframeUrl(obj.urlFrame || "");

    var defaultSmiles = [
        {
            url: "/img/smiles/4eb59b8a.gif",
            content: "[girl2]"
        },
        {
            url: "/img/smiles/7f3ec806.gif",
            content: "[med]"
        },
        {
            url: "/img/smiles/7f10c150.gif",
            content: "[cheese]"
        },
            {
            url: "/img/smiles/4eb59b8a.gif",
            content: "[girl2]"
        },
        {
            url: "/img/smiles/7f3ec806.gif",
            content: "[med]"
        },
        {
            url: "/img/smiles/7f10c150.gif",
            content: "[cheese]"
        },
        {
            url: "/img/smiles/4eb59b8a.gif",
            content: "[girl2]"
        },
        {
            url: "/img/smiles/7f3ec806.gif",
            content: "[med]"
        }
    ];
    var smiles = defaultSmiles.concat (obj.smiles || []);
    comment_form.smiles(smiles);



    var defaultCommands = [
        // стилей текста
        {
            containerIndex: 0,
            type: "text",
            textvalue: "b",
            src: "/img/b.gif",
            title: "Полужирный"
        },
        {
            containerIndex: 0,
            type: "text",
            textvalue: "i",
            src: "/img/i.gif",
            title: "Курсив"
        },
        {
            containerIndex: 0,
            type: "text",
            textvalue: "s",
            src: "/img/s.gif",
            title: "Зачёркнутый"
        },

        // блок команд отвечающих за расположение текста
        {
            containerIndex: 1,
            type: "text",
            textvalue: "left",
            src: "/img/left.gif",
            title: "Выравнивание по левому краю"
        },
        {
            containerIndex: 1,
            type: "text",
            textvalue: "center",
            src: "/img/center.gif",
            title: "Выравнивание по центру"
        },
        {
            containerIndex: 1,
            type: "text",
            textvalue: "right",
            src: "/img/right.gif",
            title: "Выравнивание по правому краю"
        },

        // блок мультимедиа
        {
            containerIndex: 2,
            type: "popup",
            textvalue: "url",
            src: "/img/link.gif",
            title: "Вставить ссылку"
        },
        {
            containerIndex: 2,
            type: "popup",
            textvalue: "photo",
            src: "/img/pic.gif",
            title: "Вставить изображение"
        },
        {
            containerIndex: 2,
            type: "popup",
            textvalue: "video",
            src: "/img/video.gif",
            title: "Вставить видео"
        },


        // смайлики и цитата
        {
            containerIndex: 3,
            type: "text",
            textvalue: "quote",
            src: "/img/quote.gif",
            title: "Цитировать"
        },
        {
            containerIndex: 3,
            type: "popup",
            textvalue: "smile",
            src: "/img/smile.gif",
            title: "Смайлики"
        },
    ];
    var commands = defaultCommands.concat(obj.commands || []);
    comment_form.commands(commands);
}