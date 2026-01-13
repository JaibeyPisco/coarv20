<script setup lang="ts">
import { computed } from 'vue';
import { useNotificationsStore } from '@/stores/notifications';

const notificationsStore = useNotificationsStore();

const currentNotification = computed(() => {
    return notificationsStore.notifications.length > 0
        ? notificationsStore.notifications[0]
        : null;
});

const show = computed(() => currentNotification.value !== null);

const color = computed(() => {
    if (!currentNotification.value) return 'info';
    
    switch (currentNotification.value.type) {
        case 'success':
            return 'success';
        case 'error':
            return 'error';
        case 'warning':
            return 'warning';
        default:
            return 'info';
    }
});

const icon = computed(() => {
    if (!currentNotification.value) return 'mdi-information';
    
    switch (currentNotification.value.type) {
        case 'success':
            return 'mdi-check-circle';
        case 'error':
            return 'mdi-alert-circle';
        case 'warning':
            return 'mdi-alert';
        default:
            return 'mdi-information';
    }
});

function handleClose() {
    if (currentNotification.value) {
        notificationsStore.removeNotification(currentNotification.value.id);
    }
}
</script>

<template>
    <v-snackbar
        v-model="show"
        :color="color"
        :timeout="currentNotification?.timeout ?? 3000"
        location="top right"
        variant="elevated"
        @update:model-value="handleClose"
    >
        <div class="d-flex align-center" style="gap: 8px;">
            <v-icon :icon="icon" />
            <div>
                <div v-if="currentNotification?.title" class="font-weight-bold">
                    {{ currentNotification.title }}
                </div>
                <div>{{ currentNotification?.message }}</div>
            </div>
        </div>

        <template #actions>
            <v-btn
                icon="mdi-close"
                variant="text"
                size="small"
                @click="handleClose"
            />
        </template>
    </v-snackbar>
</template>
