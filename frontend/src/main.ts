import { createApp } from 'vue';
import vSelect from 'vue-select';
import { createPinia } from 'pinia';
import { createVuetify } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';
import router from './router';
import App from './App.vue';
import { logger } from './utils/logger';
import 'vue-select/dist/vue-select.css';
import 'vuetify/styles';
import '@mdi/font/css/materialdesignicons.css';
import { VFileUpload } from 'vuetify/labs/VFileUpload'

import './styles.css'

// Cargar tema desde localStorage antes de crear Vuetify
const getStoredTheme = (): 'light' | 'dark' => {
    try {
        const storedTheme = localStorage.getItem('app-theme');
        if (storedTheme === 'dark' || storedTheme === 'light') {
            return storedTheme;
        }
    } catch (error) {
        logger.warn('Error loading theme from localStorage', { error });
    }
    return 'light';
};

// Configurar Vuetify
const vuetify = createVuetify({
    components: {
        components,
        VFileUpload
    },
    directives,
    theme: {
        defaultTheme: getStoredTheme(),
        themes: {
            light: {
                colors: {
                    primary: '#1976D2',
                    secondary: '#424242',
                    accent: '#82B1FF',
                    error: '#FF5252',
                    info: '#2196F3',
                    success: '#4CAF50',
                    warning: '#FFC107',
                },
            },
            dark: {
                colors: {
                    primary: '#2196F3',
                    secondary: '#424242',
                    accent: '#FF4081',
                    error: '#FF5252',
                    info: '#2196F3',
                    success: '#4CAF50',
                    warning: '#FFC107',
                },
            },
        },
    },

});

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);
app.use(vuetify);

app.component('v-select', vSelect);

app.mount('#app');
