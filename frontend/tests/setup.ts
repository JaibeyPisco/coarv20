import { expect, afterEach, vi } from 'vitest';

// Limpiar localStorage después de cada test
afterEach(() => {
    localStorage.clear();
});

// Mock de localStorage
const localStorageMock = (() => {
    let store: Record<string, string> = {};

    return {
        getItem: (key: string) => store[key] || null,
        setItem: (key: string, value: string) => {
            store[key] = value.toString();
        },
        removeItem: (key: string) => {
            delete store[key];
        },
        clear: () => {
            store = {};
        },
    };
})();

Object.defineProperty(window, 'localStorage', {
    value: localStorageMock,
});

// Mock de notificacion global
global.notificacion = vi.fn();

