import { ref, type Ref } from 'vue';

/**
 * Interfaz de retorno del composable useImageUpload
 */
export interface UseImageUploadReturn {
    /** Referencia reactiva al archivo seleccionado */
    file: Ref<File | null>;
    /** Referencia reactiva a la URL de previsualización (data URL o URL de imagen) */
    preview: Ref<string>;
    /** Función para manejar el evento change del input file */
    handleChange: (event: Event) => void;
    /** Función para resetear el archivo y preview a sus valores iniciales */
    reset: () => void;
    /** Función para establecer manualmente la URL de previsualización */
    setPreview: (url: string) => void;
}

/**
 * Composable para manejar la carga y previsualización de imágenes
 * 
 * Este composable proporciona una interfaz reactiva para manejar la selección
 * de archivos de imagen, generar previsualizaciones usando FileReader, y
 * gestionar el estado del archivo y su preview.
 * 
 * @example
 * ```vue
 * <script setup>
 * import { useImageUpload } from '@/composables/useImageUpload';
 * 
 * const imageUpload = useImageUpload('/images/default.jpg');
 * 
 * // En el template
 * <input type="file" @change="imageUpload.handleChange" />
 * <img :src="imageUpload.preview" />
 * </script>
 * ```
 * 
 * @param initialPreview - URL inicial para la previsualización (opcional). 
 *                         Puede ser una URL de imagen existente o una data URL.
 * @returns Objeto con refs reactivas y funciones para manejar la imagen
 * 
 * @public
 */
export function useImageUpload(initialPreview: string = ''): UseImageUploadReturn {
    const file = ref<File | null>(null);
    const preview = ref<string>(initialPreview);

    const handleChange = (event: Event) => {
        const target = event.target as HTMLInputElement;
        const selectedFile = target.files?.[0];
        
        if (!selectedFile) {
            return;
        }

        file.value = selectedFile;

        const reader = new FileReader();
        reader.onload = (e) => {
            preview.value = e.target?.result as string;
        };
        reader.readAsDataURL(selectedFile);
    };

    const reset = () => {
        file.value = null;
        preview.value = initialPreview;
    };

    const setPreview = (url: string) => {
        preview.value = url;
    };

    return {
        file,
        preview,
        handleChange,
        reset,
        setPreview,
    };
}

