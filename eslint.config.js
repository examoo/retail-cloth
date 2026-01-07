import js from '@eslint/js';
import pluginVue from 'eslint-plugin-vue';
import eslintConfigPrettier from 'eslint-config-prettier';
import pluginPrettier from 'eslint-plugin-prettier';
import globals from 'globals';

export default [
    js.configs.recommended,
    ...pluginVue.configs['flat/recommended'],
    {
        plugins: {
            prettier: pluginPrettier,
        },
        rules: {
            'prettier/prettier': 'error',
            'vue/require-default-prop': 'off',
            'vue/multi-word-component-names': 'off',
        },
        languageOptions: {
            globals: {
                ...globals.browser,
                axios: 'readonly',
            },
        },
    },
    eslintConfigPrettier,
];
