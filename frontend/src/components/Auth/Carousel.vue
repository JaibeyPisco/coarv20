<template>
  <div class="carousel">
    <slot :currentSlide="currentSlide" /> 

    <div class="autoplay-control">
        <v-btn
            :icon="autoPlayEnabled ? 'mdi-pause' : 'mdi-play'"
            variant="flat"
            class="control-btn"
            @click="toggleAutoPlay"
        ></v-btn>
        </div>


    <div class="navigate">
      <div class="toggle-page">
        <v-btn 
          icon="mdi-chevron-left" 
          variant="flat" 
          class="nav-btn"
          @click="prevSlide"
        ></v-btn>
      </div>
      
      <div class="toggle-page">
        <v-btn 
          icon="mdi-chevron-right" 
          variant="flat" 
          class="nav-btn"
          @click="nextSlide"
        ></v-btn>
      </div>
    </div>
   <div class="pagination">
        <v-icon
            v-for="(slide, index) in totalSlides"
            :key="index"
            :icon="currentSlide === index + 1 ? 'mdi-circle' : 'mdi-circle-outline'"
            :color="currentSlide === index + 1 ? 'white' : 'rgba(255, 255, 255, 0.5)'"
            size="x-small"
            class="mx-1 dot-icon"
            @click="currentSlide = index + 1" 
        ></v-icon>
</div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';

const currentSlide = ref(1);

const totalSlides = ref(null);

const autoPlayEnabled = ref(true);
const timeOutDuration = ref(5000);
let intervalId = null;


const nextSlide = () => {
  if (currentSlide.value === totalSlides.value) {
    currentSlide.value = 1;
      return;
  } 
    currentSlide.value+=1;
   
};

const prevSlide = () => {
  if (currentSlide.value === 1) {
   
    currentSlide.value = totalSlides.value;
    return;
  }

    currentSlide.value -= 1;
};


const toggleAutoPlay = () => {
  autoPlayEnabled.value = !autoPlayEnabled.value;
  
  if (autoPlayEnabled.value) {
    startAutoPlay();
  } else {
    stopAutoPlay();
  }
};
// Auto play

// Función para iniciar el movimiento
const startAutoPlay = () => {
  stopAutoPlay(); // Limpiamos cualquier intervalo previo por seguridad
  intervalId = setInterval(() => {
    nextSlide();
  }, timeOutDuration.value);
};

// Función para detener el movimiento
const stopAutoPlay = () => {
  if (intervalId) {
    clearInterval(intervalId);
    intervalId = null;
  }
};


if(autoPlayEnabled.value){
    startAutoPlay();
}


onMounted ( () => {
    totalSlides.value = document.querySelectorAll('.slide').length;
 
});


</script>

<style scoped>
.carousel {
  position: relative;
  width: 100%;
  height: 100%;
}

.navigate {
  position: absolute;
  top: 0;
  left: 0;
  display: flex;
  /* Esta es la clave: separa los elementos al máximo */
  justify-content: space-between; 
  align-items: center;
  height: 100%;
  width: 100%;
  padding: 0 16px;
  z-index: 10;
  /* Evita que el contenedor bloquee clics en el fondo si no hay botones */
  pointer-events: none; 
}

.toggle-page {
  /* Permite que solo los botones reciban clics */
  pointer-events: auto; 
}

/* Estilo para el fondo y el radio */
.nav-btn {
  /* Control total del tamaño */
  width: 40px !important; 
  height: 40px !important;
  min-width: 40px !important;
  
  /* Fondo sutil y bordes redondeados */
  background-color: rgba(22, 22, 22, 0.3) !important; 
  color: white !important;
  border-radius: 8px !important; /* Radio moderno, no tan circular */
  
  /* Ajuste del icono interno */
  font-size: 10px !important; 
}

.nav-btn:hover {
  background-color: rgba(0, 0, 0, 0.6) !important;
}

/* En el <style scoped> de Carousel.vue */
.pagination {
    position: absolute;
    bottom: 30px; 
    left: 0;
    right: 0; /* Asegura que el contenedor abarque todo el ancho para centrar */
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px; /* Espaciado uniforme entre puntos */
    z-index: 20;
    pointer-events: auto; /* Permite clics aunque el padre tenga none */
}
.dot-icon {
    cursor: pointer;
    transition: transform 0.2s ease, color 0.2s ease;
}

.dot-icon:hover {
    transform: scale(1.2); /* Efecto visual al pasar el mouse */
}

.autoplay-control {
  position: absolute;
  top: 16px;
  right: 16px;
  z-index: 30; /* Por encima de todo */
}

.autoplay-control {
  position: absolute;
  top: 15px;
  right: 15px; /* Lo anclamos a la derecha */
  z-index: 100; /* Aseguramos que esté por encima de la imagen */
}

.play-pause-btn {
  width: 35px !important;
  height: 35px !important;
  min-width: 35px !important;
  background-color: rgba(0, 0, 0, 0.4) !important; /* Fondo oscuro sutil */
  color: white !important;
  border-radius: 50% !important; /* Estilo circular */
}

.play-pause-btn:hover {
  background-color: rgba(0, 0, 0, 0.7) !important;
  transform: scale(1.1);
}
</style>