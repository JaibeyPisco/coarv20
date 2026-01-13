import { onMounted, onBeforeUnmount, watch, type Ref } from 'vue';
import { applyTabulatorTheme, getCurrentTheme } from '@/utils/tabulatorTheme';

/**
 * Composable para aplicar tema dark automáticamente a tablas Tabulator
 * @param table - Referencia reactiva a la instancia de Tabulator
 */
export function useTabulatorDark(table: Ref<any>) {
    let themeObserver: MutationObserver | null = null;

    const setupThemeObserver = () => {
        if (typeof document === 'undefined') return;

        // Aplicar tema inicial
        if (table.value) {
            applyTabulatorTheme(table.value);
        }

        // Observar cambios en el atributo data-bs-theme
        themeObserver = new MutationObserver(() => {
            if (table.value) {
                applyTabulatorTheme(table.value);
            }
        });

        themeObserver.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['data-bs-theme'],
        });
    };

    const cleanup = () => {
        if (themeObserver) {
            themeObserver.disconnect();
            themeObserver = null;
        }
    };

    // Aplicar tema cuando la tabla se construya
    const onTableBuilt = () => {
        if (table.value) {
            applyTabulatorTheme(table.value);
        }
    };

    // Observar cambios en la referencia de la tabla
    watch(
        () => table.value,
        (newTable) => {
            if (newTable) {
                applyTabulatorTheme(newTable);
            }
        },
        { immediate: true }
    );

    onMounted(() => {
        setupThemeObserver();
    });

    onBeforeUnmount(() => {
        cleanup();
    });

    return {
        onTableBuilt,
        applyTheme: () => applyTabulatorTheme(table.value),
    };
}


