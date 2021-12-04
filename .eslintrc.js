module.exports = {
  env: {
    browser: true,
    es2021: true,
  },
  extends: [
    'airbnb-base',
    'plugin:vue/recommended',
  ],
  parserOptions: {
    ecmaVersion: 12,
    sourceType: 'module',
  },
  plugins: [
    'vue',
  ],
  rules: {
    'no-param-reassign': [2, {props: false}],
    'max-len': 'off',
    'vue/no-v-html': 0,
    'vue/max-len': [
      'error',
      {
        code: 120,
        template: 160,
        ignoreTemplateLiterals: true,
        ignoreUrls: true,
        ignoreStrings: true,
      },
    ],
  },
  globals: {
    Nova: true,
  },
};
