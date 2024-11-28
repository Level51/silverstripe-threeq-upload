const settings = require('./client/config/webpack.resolve').forEsLint;

module.exports = {
  root: true,
  parserOptions: {
    parser: 'babel-eslint',
  },
  extends: [
    'airbnb-base',
    'plugin:vue/strongly-recommended',
  ],
  rules: {
    'no-new': 0,
    radix: 0,
    'no-prototype-builtins': 0,
    'no-restricted-globals': 0,
    'no-underscore-dangle': 0,
    'vue/html-closing-bracket-newline': 0,
    'no-param-reassign': 0,
    'import/prefer-default-export': 0,
  },
  globals: {
    payload: false, // Define the payload as valid global variable, with "false" to prevent override
  },
  plugins: [
    'vue',
  ],
  settings,
};
