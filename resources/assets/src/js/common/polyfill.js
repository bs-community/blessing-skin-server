'use strict';

// polyfill of String.prototype.includes
if (!String.prototype.includes) {
    String.prototype.includes = function(search) {
        // Copied from core-js
        return !!~this.indexOf(search, arguments.length > 1 ? arguments[1] : undefined);
    };
}

// polyfill of String.prototype.endsWith
if (!String.prototype.endsWith) {
    String.prototype.endsWith = function (searchString, position) {
        var subjectString = this.toString();
        if (typeof position !== 'number' || !isFinite(position) || Math.floor(position) !== position || position > subjectString.length) {
            position = subjectString.length;
        }
        position -= searchString.length;
        var lastIndex = subjectString.lastIndexOf(searchString, position);
        return lastIndex !== -1 && lastIndex === position;
    };
}
