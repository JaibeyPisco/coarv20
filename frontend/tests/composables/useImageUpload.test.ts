import { describe, it, expect, beforeEach } from 'vitest';
import { useImageUpload } from '@/composables/useImageUpload';

describe('useImageUpload', () => {
    it('debería inicializar con preview vacío por defecto', () => {
        const { preview, file } = useImageUpload();

        expect(preview.value).toBe('');
        expect(file.value).toBeNull();
    });

    it('debería inicializar con preview inicial si se proporciona', () => {
        const initialPreview = '/images/default.jpg';
        const { preview } = useImageUpload(initialPreview);

        expect(preview.value).toBe(initialPreview);
    });

    it('debería resetear a valores iniciales', () => {
        const initialPreview = '/images/default.jpg';
        const { preview, file, reset, setPreview } = useImageUpload(initialPreview);

        setPreview('/images/new.jpg');
        file.value = new File([''], 'test.jpg', { type: 'image/jpeg' });

        reset();

        expect(preview.value).toBe(initialPreview);
        expect(file.value).toBeNull();
    });

    it('debería establecer preview manualmente', () => {
        const { preview, setPreview } = useImageUpload();

        setPreview('/images/test.jpg');

        expect(preview.value).toBe('/images/test.jpg');
    });
});

