// rule reference: http://eslint.org/docs/rules
// individual rule reference: http://eslint.org/docs/rules/NAME-OF-RULE
module.exports = {
  extends: "airbnb",
  globals: {
    Drupal: true,
    jQuery: true,
    _: true,
    domready: true
  },
  rules: {
    'no-comma-dangle': [0],
    'strict': [0],
    'no-param-reassign': [0],
    'react/require-extension': [0],
    'no-var': [0],
    'func-names': [0],
    'object-shorthand': [0],
    'comma-dangle': [0],
    'radix': [0],
    'prefer-arrow-callback': [0],
    'no-plusplus': [0],
    'no-cond-assign': [0],
  }
};

