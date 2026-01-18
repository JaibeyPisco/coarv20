<template>
  <Transition name="fade">
    <div v-if="loading" class="app-loader" aria-busy="true" aria-live="polite">
      <!-- Decor -->
      <div class="bg-orbs" aria-hidden="true">
        <span class="orb o1"></span>
        <span class="orb o2"></span>
        <span class="orb o3"></span>
      </div>

      <div class="app-loader__content">
        <!-- Logo -->
        <div class="app-loader__logo">
          <div class="logo-shell">
            <div class="logo-ring"></div>

            <svg
              viewBox="0 0 120 120"
              xmlns="http://www.w3.org/2000/svg"
              class="logo-icon"
              role="img"
              aria-label="COAR"
            >
              <defs>
                <linearGradient id="cardGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                  <stop offset="0%" stop-color="rgba(255,255,255,0.95)" />
                  <stop offset="60%" stop-color="rgba(255,255,255,0.55)" />
                  <stop offset="100%" stop-color="rgba(255,255,255,0.25)" />
                </linearGradient>

                <linearGradient id="markGradient" x1="10%" y1="0%" x2="90%" y2="100%">
                  <stop offset="0%" stop-color="rgba(255,255,255,0.95)" />
                  <stop offset="100%" stop-color="rgba(255,255,255,0.65)" />
                </linearGradient>

                <filter id="softShadow" x="-40%" y="-40%" width="180%" height="180%">
                  <feDropShadow dx="0" dy="6" stdDeviation="6" flood-color="rgba(0,0,0,0.25)" />
                </filter>
              </defs>

              <!-- Base card -->
              <rect x="20" y="20" width="80" height="80" rx="18" fill="url(#cardGradient)" filter="url(#softShadow)" />

              <!-- Mark (más “brand”) -->
              <path
                d="M38 52 L60 38 L82 52 L82 70 L60 82 L38 70 Z"
                fill="url(#markGradient)"
                opacity="0.95"
              />

              <circle cx="60" cy="58" r="6.8" fill="white" opacity="0.85" />

              <!-- Brand text -->
              <text
                x="60"
                y="92"
                text-anchor="middle"
                fill="rgba(255,255,255,0.85)"
                font-size="12"
                font-weight="800"
                letter-spacing="2"
              >
                COAR
              </text>
            </svg>

            <div class="logo-gloss" aria-hidden="true"></div>
          </div>
        </div>

        <!-- Progress -->
        <div class="loader-progress" role="status" aria-label="Cargando">
          <div class="progress-track">
            <div class="progress-bar"></div>
          </div>
          <div class="progress-meta">
            <span class="dots" aria-hidden="true">
              <i></i><i></i><i></i>
            </span>
            <p class="label">Cargando sistema</p>
          </div>
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
  if (minDisplayTimer) clearTimeout(minDisplayTimer);
  if (loadTimer) clearTimeout(loadTimer);
  loading.value = false;
};

onMounted(() => {
  // mínimo visible
  minDisplayTimer = setTimeout(() => {
    if (document.readyState === 'complete') hideLoader();
  }, 850);

  const finish = () => {
    if (minDisplayTimer) clearTimeout(minDisplayTimer);
    loadTimer = setTimeout(() => hideLoader(), 250);
  };

  if (document.readyState === 'complete') {
    finish();
  } else {
    window.addEventListener('load', finish);
    onBeforeUnmount(() => window.removeEventListener('load', finish));
  }
});

onBeforeUnmount(() => {
  if (minDisplayTimer) clearTimeout(minDisplayTimer);
  if (loadTimer) clearTimeout(loadTimer);
});
</script>

<style scoped>
/* Base */
.app-loader {
  position: fixed;
  inset: 0;
  display: grid;
  place-items: center;
  z-index: 9999;
  overflow: hidden;

  background:
    radial-gradient(1200px 600px at 10% 10%, rgba(255,255,255,0.14), transparent 60%),
    radial-gradient(900px 600px at 90% 40%, rgba(255,255,255,0.10), transparent 55%),
    linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.app-loader__content {
  text-align: center;
  color: rgba(255,255,255,0.95);
  position: relative;
  z-index: 2;
  padding: 24px 20px;
}

/* Background orbs */
.bg-orbs {
  position: absolute;
  inset: 0;
  z-index: 1;
  filter: blur(2px);
  opacity: 0.9;
}
.orb {
  position: absolute;
  width: 420px;
  height: 420px;
  border-radius: 50%;
  background: radial-gradient(circle at 30% 30%, rgba(255,255,255,0.18), rgba(255,255,255,0.03) 55%, transparent 70%);
  animation: orbFloat 9s ease-in-out infinite;
}
.o1 { left: -120px; top: -140px; animation-duration: 10s; }
.o2 { right: -160px; top: 20%; animation-duration: 12s; }
.o3 { left: 25%; bottom: -200px; animation-duration: 11s; }

@keyframes orbFloat {
  0%, 100% { transform: translate3d(0,0,0) scale(1); }
  50% { transform: translate3d(30px, -18px, 0) scale(1.06); }
}

/* Logo */
.app-loader__logo {
  margin-bottom: 20px;
  animation: popIn 700ms cubic-bezier(.2,.8,.2,1) both;
}
@keyframes popIn {
  from { transform: translateY(10px) scale(0.96); opacity: 0; }
  to { transform: translateY(0) scale(1); opacity: 1; }
}

.logo-shell {
  width: 148px;
  height: 148px;
  margin: 0 auto;
  border-radius: 28px;
  position: relative;
  display: grid;
  place-items: center;

  background: rgba(255, 255, 255, 0.14);
  border: 1px solid rgba(255, 255, 255, 0.28);
  backdrop-filter: blur(18px);
  box-shadow:
    0 18px 55px rgba(0,0,0,0.28),
    inset 0 1px 0 rgba(255,255,255,0.25);
  overflow: hidden;
}

.logo-ring {
  position: absolute;
  inset: -40%;
  background: conic-gradient(
    from 180deg,
    rgba(255,255,255,0.0),
    rgba(255,255,255,0.35),
    rgba(255,255,255,0.0),
    rgba(255,255,255,0.25),
    rgba(255,255,255,0.0)
  );
  animation: ringSpin 2.1s linear infinite;
  opacity: 0.9;
}
@keyframes ringSpin {
  to { transform: rotate(360deg); }
}

.logo-gloss {
  position: absolute;
  inset: 0;
  background: linear-gradient(
    135deg,
    rgba(255,255,255,0.22),
    transparent 45%,
    rgba(255,255,255,0.06) 70%,
    transparent
  );
  transform: translateX(-35%);
  animation: gloss 2.8s ease-in-out infinite;
}
@keyframes gloss {
  0%, 100% { transform: translateX(-40%); opacity: 0.8; }
  50% { transform: translateX(40%); opacity: 1; }
}

.logo-icon {
  width: 96px;
  height: 96px;
  position: relative;
  z-index: 1;
  filter: drop-shadow(0 8px 18px rgba(0,0,0,0.22));
  animation: logoBreath 2.6s ease-in-out infinite;
}
@keyframes logoBreath {
  0%, 100% { transform: translateY(0) scale(1); }
  50% { transform: translateY(-6px) scale(1.03); }
}

/* Progress */
.loader-progress {
  width: min(360px, 80vw);
  margin: 0 auto;
}

.progress-track {
  height: 10px;
  border-radius: 999px;
  background: rgba(255,255,255,0.18);
  border: 1px solid rgba(255,255,255,0.22);
  overflow: hidden;
  box-shadow: inset 0 1px 0 rgba(255,255,255,0.18);
}

.progress-bar {
  height: 100%;
  width: 35%;
  border-radius: 999px;
  background: rgba(255,255,255,0.85);
  animation: indeterminate 1.15s ease-in-out infinite;
  filter: drop-shadow(0 6px 12px rgba(0,0,0,0.15));
}

@keyframes indeterminate {
  0%   { transform: translateX(-120%); width: 30%; opacity: 0.75; }
  50%  { transform: translateX(80%);   width: 50%; opacity: 1; }
  100% { transform: translateX(260%);  width: 30%; opacity: 0.75; }
}

.progress-meta {
  margin-top: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
}

.label {
  margin: 0;
  font-weight: 600;
  letter-spacing: 0.2px;
  font-size: 0.95rem;
  color: rgba(255,255,255,0.92);
}

.dots {
  display: inline-flex;
  gap: 4px;
  transform: translateY(1px);
}
.dots i {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: rgba(255,255,255,0.85);
  animation: dot 900ms ease-in-out infinite;
}
.dots i:nth-child(2) { animation-delay: 120ms; }
.dots i:nth-child(3) { animation-delay: 240ms; }
@keyframes dot {
  0%, 100% { transform: translateY(0); opacity: 0.55; }
  50% { transform: translateY(-5px); opacity: 1; }
}

/* Transition */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.45s ease, transform 0.45s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: scale(1.01);
}

/* Responsive */
@media (max-width: 768px) {
  .logo-shell { width: 132px; height: 132px; border-radius: 24px; }
  .logo-icon { width: 86px; height: 86px; }
  .label { font-size: 0.9rem; }
}

/* Accesibilidad: reduce motion */
@media (prefers-reduced-motion: reduce) {
  .orb,
  .logo-ring,
  .logo-gloss,
  .logo-icon,
  .progress-bar,
  .dots i,
  .app-loader__logo {
    animation: none !important;
  }
}
</style>
