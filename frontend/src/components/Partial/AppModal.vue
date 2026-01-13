<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(defineProps<{
    modelValue?: boolean;
    open?: boolean;

const props = withDefaults(defineProps<{
    modelValue?: boolean;
    open?: boolean;
    title: {
        type: String;
        default: '';
    };
    size: {
        type: String;
        default: 'md'; // sm, md, lg, xl, fullscreen
    };
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
            return '420';
        case 'md':
            return '540';
        case 'lg':
            return '720';
        case 'xl':
            return '960';
        case 'fullscreen':
            return '100%';
        default:
            return '540';
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
        <v-card>
            <v-card-title v-if="title || $slots.header" class="d-flex align-center justify-space-between">
                <slot name="header">
                    <span class="text-h6">{{ title }}</span>
                </slot>
                <v-btn
                    icon="mdi-close"
                    variant="text"
                    size="small"
                    @click="dialogModel = false"
                />
            </v-card-title>

            <v-divider v-if="title || $slots.header" />

            <v-card-text>
                <slot name="body" />
            </v-card-text>

            <v-card-actions v-if="$slots.footer">
                <v-divider />
                <div class="d-flex justify-space-between w-100 pa-2">
                    <slot name="footer" />
                </div>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>
