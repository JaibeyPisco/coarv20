<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(defineProps<{
    modelValue?: boolean;
    open?: boolean;
    title?: string;
    size?: 'sm' | 'md' | 'lg' | 'xl' | 'fullscreen';
    persistent?: boolean;
    scrollable?: boolean;
}>(), {
    modelValue: undefined,
    open: false,
    title: '',
    size: 'md',
    persistent: false,
    scrollable: true,
});

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'update:open', value: boolean): void;
    (e: 'update:modelValue', value: boolean): void;
}>();

// Soporte para v-model y prop open (compatibilidad)
const isOpen = computed(() => {
    if (props.modelValue !== undefined) {
        return props.modelValue;
    }
    return props.open ?? false;
});

const dialogModel = computed({
    get: () => isOpen.value,
    set: (value: boolean) => {
        if (props.modelValue !== undefined) {
            emit('update:modelValue', value);
        } else {
            emit('update:open', value);
        }
        if (!value) {
            emit('close');
        }
    },
});

const maxWidth = computed(() => {
    switch (props.size) {
        case 'sm':
            return '500';
        case 'md':
            return '600';
        case 'lg':
            return '800';
        case 'xl':
            return '1200';
        case 'fullscreen':
            return undefined;
        default:
            return '600';
    }
});

const fullscreen = computed(() => props.size === 'fullscreen');
</script>

<template>
    <v-dialog
        v-model="dialogModel"
        :max-width="maxWidth"
        :fullscreen="fullscreen"
        :persistent="persistent"
        :scrollable="scrollable"
        transition="dialog-transition"
    >
        <v-card rounded="lg" elevation="2">
            <!-- Header con título y botón cerrar -->
            <v-card-title
                v-if="title || $slots.header"
                class="d-flex align-center justify-space-between pa-4"
            >
                <slot name="header">
                    <span class="text-h6 font-weight-medium">{{ title }}</span>
                </slot>
                <v-btn
                    icon="mdi-close"
                    variant="text"
                    size="small"
                    @click="dialogModel = false"
                    aria-label="Cerrar"
                />
            </v-card-title>

            <v-divider v-if="title || $slots.header" />

            <!-- Body con padding consistente -->
            <v-card-text class="pa-6">
                <slot name="body" />
            </v-card-text>

            <!-- Footer con botones alineados a la derecha -->
            <template v-if="$slots.footer">
                <v-divider />
                <v-card-actions class="pa-4">
                    <div class="d-flex justify-end ga-3 w-100">
                        <slot name="footer" />
                    </div>
                </v-card-actions>
            </template>
        </v-card>
    </v-dialog>
</template>
