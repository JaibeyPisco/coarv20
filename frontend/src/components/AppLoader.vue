<template>
    <Transition name="fade">
        <div v-if="loading" class="app-loader">
            <div class="app-loader__content">
                <div class="app-loader__logo">
                    <div class="logo-placeholder">
                        <div class="logo-content">
                            <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" class="logo-icon">
                                <defs>
                                    <linearGradient id="logoGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" style="stop-color:rgba(255,255,255,0.8);stop-opacity:1" />
                                        <stop offset="100%" style="stop-color:rgba(255,255,255,0.4);stop-opacity:1" />
                                    </linearGradient>
                                </defs>
                                <rect x="15" y="15" width="70" height="70" rx="12" fill="url(#logoGradient)" />
                                <path
                                    d="M35 35 L50 25 L65 35 L65 50 L50 60 L35 50 Z"
                                    fill="white"
                                    opacity="0.9"
                                />
                                <circle cx="50" cy="42" r="6" fill="white" opacity="0.8" />
                                <text x="50" y="75" text-anchor="middle" fill="white" font-size="12" font-weight="bold" opacity="0.7">COAR</text>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="app-loader__spinner">
                    <div class="spinner"></div>
                </div>
                <div class="app-loader__text">
                    <p class="text-muted mb-0">Cargando sistema...</p>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue';

const loading = ref(true);
let minDisplayTimer: ReturnType<typeof setTimeout> | null = null;
let loadTimer: ReturnType<typeof setTimeout> | null = null;

const hideLoader = () => {
    if (minDisplayTimer) {
        clearTimeout(minDisplayTimer);
    }
    if (loadTimer) {
        clearTimeout(loadTimer);
    }
    loading.value = false;
};

onMounted(() => {
    // Mostrar loader por al menos 800ms para una mejor UX
    minDisplayTimer = setTimeout(() => {
        if (document.readyState === 'complete') {
            hideLoader();
        }
    }, 800);

    // También escuchar cuando la página esté completamente cargada
    if (document.readyState === 'complete') {
        if (minDisplayTimer) {
            clearTimeout(minDisplayTimer);
        }
        loadTimer = setTimeout(() => {
            hideLoader();
        }, 300);
    } else {
        const handleLoad = () => {
            if (minDisplayTimer) {
                clearTimeout(minDisplayTimer);
            }
            loadTimer = setTimeout(() => {
                hideLoader();
            }, 300);
        };

        window.addEventListener('load', handleLoad);

        // Cleanup
        onBeforeUnmount(() => {
            window.removeEventListener('load', handleLoad);
        });
    }
});

onBeforeUnmount(() => {
    if (minDisplayTimer) {
        clearTimeout(minDisplayTimer);
    }
    if (loadTimer) {
        clearTimeout(loadTimer);
    }
});
</script>

<style scoped>
.app-loader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    overflow: hidden;
}

.app-loader__content {
    text-align: center;
    color: white;
}

.app-loader__logo {
    margin-bottom: 2rem;
    animation: logoFloat 3s ease-in-out infinite;
}

.logo-placeholder {
    width: 140px;
    height: 140px;
    margin: 0 auto;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(15px);
    border: 2px solid rgba(255, 255, 255, 0.3);
    box-shadow: 
        0 8px 32px rgba(0, 0, 0, 0.2),
        inset 0 1px 0 rgba(255, 255, 255, 0.3);
    position: relative;
    overflow: hidden;
}

.logo-placeholder::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(
        45deg,
        transparent,
        rgba(255, 255, 255, 0.1),
        transparent
    );
    animation: shine 3s infinite;
}

.logo-content {
    position: relative;
    z-index: 1;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.logo-icon {
    width: 90px;
    height: 90px;
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
}

.app-loader__spinner {
    margin-bottom: 1.5rem;
}

.spinner {
    width: 50px;
    height: 50px;
    margin: 0 auto;
    border: 4px solid rgba(255, 255, 255, 0.2);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

.app-loader__text {
    font-size: 1rem;
    font-weight: 500;
}

.app-loader__text p {
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.95rem;
}

/* Animaciones */
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

@keyframes logoFloat {
    0%,
    100% {
        transform: translateY(0) scale(1);
    }
    50% {
        transform: translateY(-10px) scale(1.05);
    }
}

@keyframes shine {
    0% {
        transform: translateX(-100%) translateY(-100%) rotate(45deg);
    }
    100% {
        transform: translateX(100%) translateY(100%) rotate(45deg);
    }
}

/* Transición de salida */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Responsive */
@media (max-width: 768px) {
    .logo-placeholder {
        width: 120px;
        height: 120px;
        border-radius: 20px;
    }

    .logo-icon {
        width: 75px;
        height: 75px;
    }

    .app-loader__text p {
        font-size: 0.85rem;
    }
}
</style>

