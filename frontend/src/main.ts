import { createApp } from 'vue';
import vSelect from 'vue-select';
import { createPinia } from 'pinia';
import { createVuetify } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';
import router from './router';
import App from './App.vue';
import './style.css';
import { notificacion } from './utils/notificacion';
import 'vue-select/dist/vue-select.css';
import 'vuetify/styles';
import '@mdi/font/css/materialdesignicons.css';

// Configurar Vuetify
const vuetify = createVuetify({
    components,
    directives,
    theme: {
        defaultTheme: 'light',
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

// Exponer notificacion globalmente
if (typeof window !== 'undefined') {
    (window as any).notificacion = notificacion;
}
app.component('v-select', vSelect);

app.mount('#app');
