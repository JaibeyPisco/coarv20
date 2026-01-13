import { onBeforeUnmount, onMounted } from 'vue';

const DEFAULT_LINKS = [
    { rel: 'stylesheet', href: '/tabler/css/tabler.min.css', key: 'tabler-core' },
    { rel: 'stylesheet', href: '/tabler/css/tabler-vendors.min.css', key: 'tabler-vendors' },
    { rel: 'stylesheet', href: '/tabler/css/tabler-marketing.min.css', key: 'tabler-marketing' },
    { rel: 'stylesheet', href: '/tabler/css/tabler-themes.min.css', key: 'tabler-themes' },
    { rel: 'stylesheet', href: '/tabler/css/tabler-flags.min.css', key: 'tabler-flags' },
    { rel: 'stylesheet', href: '/tabler/css/tabler-socials.min.css', key: 'tabler-socials' },
    { rel: 'stylesheet', href: '/tabler/css/tabler-payments.min.css', key: 'tabler-payments' },
    { rel: 'stylesheet', href: 'https://rsms.me/inter/inter.css', key: 'tabler-font-inter' },
];

const DEFAULT_SCRIPTS = [
    { src: '/tabler/js/tabler-theme.min.js', defer: true, key: 'tabler-theme' },
    { src: '/tabler/js/tabler.min.js', defer: true, key: 'tabler-js' },
];

interface LinkDescriptor {
    rel?: string;
    href: string;
    key: string;
    [key: string]: any;
}

interface ScriptDescriptor {
    src: string;
    defer?: boolean;
    key: string;
    [key: string]: any;
}

interface UseTablerAssetsOptions {
    links?: LinkDescriptor[];
    scripts?: ScriptDescriptor[];
    cleanup?: boolean;
}

/**
 * Ensures Tabler assets are available for the current view.
 */
export function useTablerAssets(options: UseTablerAssetsOptions = {}) {
    const {
        links = DEFAULT_LINKS,
        scripts = DEFAULT_SCRIPTS,
        cleanup = true,
    } = options;

    const injectedScripts: HTMLScriptElement[] = [];

    onMounted(() => {
        scripts.forEach(({ key, ...attrs }) => {
            // Verificar si el script ya existe en el documento
            const existingScript = document.querySelector(`script[data-tabler="${key}"], script[src="${attrs.src}"]`);
            if (existingScript) {
                return;
            }

            const script = document.createElement('script');
            script.dataset.tabler = key;
            Object.assign(script, attrs);
            
            // Si el script tiene defer, asegurarse de que se ejecute
            if (attrs.defer) {
                script.defer = true;
            }
            
            document.body.appendChild(script);
            injectedScripts.push(script);
        });
    });

    onBeforeUnmount(() => {
        if (!cleanup) {
            return;
        }

        injectedScripts.forEach((script) => {
            if (script.parentNode) {
                script.parentNode.removeChild(script);
            }
        });
        injectedScripts.length = 0;
    });

    return {
        headLinks: links,
    };
}


