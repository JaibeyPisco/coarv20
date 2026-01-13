<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    open: {
        type: Boolean;
        default: false;
    };
    title: {
        type: String;
        default: '';
    };
    size: {
        type: String;
        default: 'md'; // sm, md, lg, xl, fullscreen
    };
    persistent: {
        type: Boolean;
        default: false; // Si es true, no se cierra al hacer click fuera
    };
    scrollable: {
        type: Boolean;
        default: true;
    };
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'update:open', value: boolean): void;
}>();

const dialogModel = computed({
    get: () => props.open,
    set: (value: boolean) => {
        emit('update:open', value);
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
