module.exports = {
    "extends": ["eslint:recommended", "plugin:vue/essential"],
    "rules": {
        "linebreak-style": ["error", "unix"],
        "quotes": ["warn", "single"],
        "semi": ["error", "always"],
        "object-curly-spacing": ["error", "always"],
        "no-unused-vars": "warn",
        "no-console": "off",
        "comma-style": ["warn", "last"],
        "prefer-const": "warn",
        "no-var": "error",
        "eqeqeq": "error",
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
        "TexturePreview": false,
        "handleDataTablesAjaxError": false
    },
    "parser": "vue-eslint-parser",
    "parserOptions": {
        "ecmaVersion": 2018,
        "sourceType": "module",
        "parser": "babel-eslint"
    },
    "root": true,
    "env":{
        "node": true,
        "es6": true,
        "browser": true,
        "jest": true,
        "jquery": true
    }
};
