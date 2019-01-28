(function () {
    'use strict';

    /**
     * Source: https://github.com/janl/mustache.js/blob/master/mustache.js
     */
    var entityMap = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;',
        '/': '&#x2F;',
        '`': '&#x60;',
        '=': '&#x3D;'
    };
    window.escapeHtml = function (string) {
        return String(string).replace(/[&<>"'`=\/]/g, function fromEntityMap (s) {
            return entityMap[s];
        });
    };

})();
