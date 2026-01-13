<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, watch } from 'vue';

const props = defineProps({
    open: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: '',
    },
    size: {
        type: String,
        default: 'md', // sm, md, lg, xl
    },
    closeOnBackdrop: {
        type: Boolean,
        default: true,
    },
    id: {
        type: String,
        default: () => `modal-${Math.random().toString(36).slice(2, 9)}`,
    },
});

const emit = defineEmits(['close']);

const dialogSizeClass = computed(() => {
    switch (props.size) {
        case 'sm':
            return 'modal-container-sm';
        case 'lg':
            return 'modal-container-lg';
        case 'xl':
            return 'modal-container-xl';
        case 'full':
            return 'modal-container-full';
        default:
            return 'modal-container-md';
    }
});

function handleBackdropClick() {
    if (!props.closeOnBackdrop) return;
    emit('close');
}

function handleKeydown(event: KeyboardEvent) {
    if (event.key === 'Escape') {
        emit('close');
    }
}

watch(
    () => props.open,
    (isOpen) => {
        if (typeof document === 'undefined') return;
        document.body.style.overflow = isOpen ? 'hidden' : '';
    },
    { immediate: true }
);

onMounted(() => {
    document.addEventListener('keydown', handleKeydown);
});

onBeforeUnmount(() => {
    document.removeEventListener('keydown', handleKeydown);
    document.body.style.overflow = '';
});
</script>

<template>
    <Teleport to="body">
        <Transition name="app-modal">
            <div
                v-if="open"
                class="app-modal-backdrop"
                :class="{ fullscreen: size === 'full' }"
                role="dialog"
                :aria-labelledby="`${id}-title`"
                aria-modal="true"
            >
                <div class="app-modal-blanket" @click="handleBackdropClick"></div>
                <div class="app-modal-wrapper">
                    <div class="app-modal-container" :class="dialogSizeClass" @click.stop>
                        <header
                            v-if="title || $slots.header"
                            class="app-modal-header border-bottom"
                        >
                            <slot name="header">
                                <h2 class="h4 mb-0" :id="`${id}-title`">{{ title }}</h2>
                            </slot>
                            <button
                                type="button"
                                class="btn-close"
                                aria-label="Cerrar"
                                @click="emit('close')"
                            ></button>
                        </header>

                        <section class="app-modal-body">
                            <slot name="body" />
                        </section>

                        <footer v-if="$slots.footer" class="app-modal-footer border-top">
                            <slot name="footer" />
                        </footer>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
:global(:root) {
    --app-modal-surface: var(--tblr-card-bg, #fff);
    --app-modal-border: rgba(15, 23, 42, 0.12);
    --app-modal-text: inherit;
    --app-modal-elevation: 0 25px 50px -12px rgba(15, 23, 42, 0.35);
    --app-modal-header-bg: var(--app-modal-surface);
    --app-modal-body-bg: var(--app-modal-surface);
}

:global(:root[data-bs-theme='dark']),
:global(body[data-bs-theme='dark']) {
    --app-modal-surface: #111c2f;
    --app-modal-border: rgba(148, 163, 184, 0.25);
    --app-modal-text: #e2e8f0;
    --app-modal-elevation: 0 30px 60px rgba(2, 6, 23, 0.65);
    --app-modal-header-bg: rgba(15, 23, 42, 0.9);
    --app-modal-body-bg: rgba(13, 22, 37, 0.95);
}

.app-modal-backdrop {
    position: fixed;
    inset: 0;
    z-index: 1050;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
}

.app-modal-backdrop.fullscreen {
    padding: 0;
    align-items: stretch;
    justify-content: stretch;
}

.app-modal-blanket {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.55);
    backdrop-filter: blur(2px);
}

.app-modal-wrapper {
    position: relative;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.app-modal-backdrop.fullscreen .app-modal-wrapper {
    width: 100%;
    height: 100%;
    align-items: stretch;
    justify-content: stretch;
}

.app-modal-container {
    background: var(--app-modal-surface);
    color: var(--app-modal-text);
    border-radius: 0.75rem;
    box-shadow: var(--app-modal-elevation);
    border: 1px solid var(--app-modal-border);
    max-height: calc(100vh - 3rem);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.modal-container-full {
    width: 100vw !important;
    height: 100vh !important;
    max-width: 100vw !important;
    max-height: 100vh !important;
    margin: 0 !important;
    border-radius: 0 !important;
    border: none !important;
    box-shadow: none !important;
}

.modal-container-sm {
    width: min(420px, 100%);
}

.modal-container-md {
    width: min(540px, 100%);
}

.modal-container-lg {
    width: min(720px, 100%);
}

.modal-container-xl {
    width: min(960px, 100%);
}

/* Cuando el modal es full-screen, el body debe tener scroll interno */
.modal-container-full .app-modal-body {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    min-height: 0;
}

.app-modal-header,
.app-modal-footer {
    padding: 1rem 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    background: var(--app-modal-header-bg);
    color: var(--app-modal-text);
    border-color: var(--app-modal-border);
}

.app-modal-body {
    padding: 1.25rem 1.5rem;
    overflow-y: auto;
    background: var(--app-modal-body-bg);
    color: var(--app-modal-text);
}

.app-modal-footer {
    justify-content: flex-end;
    gap: 0.75rem;
}

.app-modal-footer :deep(.btn) {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}

.app-modal-body :deep(.form-control),
.app-modal-body :deep(textarea) {
    background: rgba(255, 255, 255, 0.08);
    color: var(--app-modal-text);
    border-color: var(--app-modal-border);
}

.app-modal-body :deep(.form-control::placeholder),
.app-modal-body :deep(textarea::placeholder) {
    color: color-mix(in srgb, var(--app-modal-text) 65%, transparent);
}

.app-modal-footer :deep(.btn-default) {
    color: var(--app-modal-text);
    border-color: var(--app-modal-border);
    background-color: color-mix(in srgb, var(--app-modal-text) 8%, transparent);
}

.app-modal-footer :deep(.btn-default:hover) {
    background-color: color-mix(in srgb, var(--app-modal-text) 15%, transparent);
}

.app-modal-enter-active,
.app-modal-leave-active {
    transition:
        opacity 0.18s ease,
        transform 0.2s ease;
}

.app-modal-enter-from,
.app-modal-leave-to {
    opacity: 0;
    transform: translateY(-12px);
}

:global(:root[data-bs-theme='dark']) .app-modal-blanket,
:global(body[data-bs-theme='dark']) .app-modal-blanket {
    background: rgba(2, 6, 23, 0.8);
}
</style>
