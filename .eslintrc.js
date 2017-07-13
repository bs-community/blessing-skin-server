module.exports = {
    "extends": "eslint:recommended",
    "rules": {
        "linebreak-style": ["error", "unix"],
        "quotes": ["warn", "single"],
        "semi": ["error", "always"],
        "object-curly-spacing": ["error", "always"],
        "no-unused-vars": "warn",
        "no-console": "off",
        "comma-style": ["warn", "last"]
    },
    "globals": {
        "url": false,
        "swal": false,
        "fetch": false,
        "trans": false,
        "logout": false,
        "toastr": false,
        "isEmpty": false,
        "showMsg": false,
        "blessing": true,
        "debounce": false,
        "showModal": false,
        "showAjaxError": false,
        "getQueryString": false,
        "TexturePreview": false
    },
    "env":{
        "es6": true,
        "browser": true,
        "jquery": true
    }
};
