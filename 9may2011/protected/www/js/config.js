$.fn.commentForm = function (obj) {
    var obj = obj || {};
    comment_form.build();

    comment_form.submit(obj.submit || function () {});

    comment_form.setIframeUrl(obj.urlFrame || "");

    var defaultSmiles = [];
    var smiles = defaultSmiles.concat (obj.smiles || []);
    comment_form.smiles(smiles);



    var defaultCommands = [];
    var commands = defaultCommands.concat(obj.commands || []);
    comment_form.commands(commands);
}