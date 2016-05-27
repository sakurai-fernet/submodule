$(function() {
    var Clipboard = require('clipboard');

    // ブランチ選択時
    $('#branch-selectpicker').on('change', function() {
        var url = $(this).find("option:selected").val();
        window.location.href = url;
    });

    // SSHのパステキストフォーカス時
    $('#ssh-path').click(function() {
        this.select();
    });

    var clipboard = new Clipboard('#ssh-path-clipboard');
});
