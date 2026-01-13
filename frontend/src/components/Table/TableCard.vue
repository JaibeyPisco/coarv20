<script setup lang="ts">
const props = defineProps<{
    loading?: boolean;
    columnMenu?: Array<{ title: string; field: string; visible: boolean }>;
    searchValue?: string;
    searchPlaceholder?: string;
}>();

const emit = defineEmits<{
    (e: 'print'): void;
    (e: 'export'): void;
    (e: 'toggle-column', field: string): void;
    (e: 'update:search', value: string): void;
}>();

function handleSearchInput(event: Event) {
    const target = event.target as HTMLInputElement;
    emit('update:search', target.value);
}

function handleToggleColumn(field: string) {
    emit('toggle-column', field);
}
</script>

<template>
    <div class="card table-card">
        <div class="table-card__header card-header py-2 px-3">
            <div class="table-card__toolbar d-flex flex-wrap align-items-center gap-2 w-100">
                <div class="table-card__actions d-flex flex-wrap align-items-center gap-2">
                    <slot name="actions">
                        <div class="btn-toolbar gap-2 flex-wrap">
                            <button
                                type="button"
                                class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 px-3"
                                @click="emit('print')"
                                aria-label="Imprimir tabla"
                            >
                                <i class="ti ti-printer fs-5" aria-hidden="true"></i>
                                Imprimir
                            </button>
                            <button
                                type="button"
                                class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 px-3"
                                @click="emit('export')"
                                aria-label="Exportar tabla a Excel"
                            >
                                <i class="ti ti-file-spreadsheet fs-5" aria-hidden="true"></i>
                                Excel
                            </button>
                            <div v-if="props.columnMenu?.length" class="dropdown">
                                <button
                                    class="btn btn-outline-secondary btn-sm dropdown-toggle px-3"
                                    data-bs-toggle="dropdown"
                                    aria-label="Mostrar u ocultar columnas"
                                    aria-haspopup="true"
                                    aria-expanded="false"
                                >
                                    Mostrar columnas
                                </button>
                                <div class="dropdown-menu shadow-sm py-2" role="menu">
                                    <template v-for="column in props.columnMenu" :key="column.field">
                                        <button
                                            class="dropdown-item d-flex justify-content-between align-items-center py-1"
                                            type="button"
                                            role="menuitemcheckbox"
                                            :aria-checked="column.visible"
                                            @click="handleToggleColumn(column.field)"
                                        >
                                            <span>{{ column.title }}</span>
                                            <i 
                                                :class="['ti', column.visible ? 'ti-eye' : 'ti-eye-off']"
                                                aria-hidden="true"
                                            ></i>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </slot>
                </div>
                <div class="table-card__search d-flex align-items-center ms-lg-auto">
                    <slot name="search">
                        <div class="input-icon">
                            <span class="input-icon-addon" aria-hidden="true">
                                <i class="ti ti-search" aria-hidden="true"></i>
                            </span>
                            <input
                                :value="props.searchValue ?? ''"
                                type="search"
                                class="form-control form-control-sm"
                                :placeholder="props.searchPlaceholder ?? 'Buscar...'"
                                aria-label="Buscar en la tabla"
                                @input="handleSearchInput"
                            />
                        </div>
                    </slot>
                </div>
            </div>
        </div>

        <div class="table-card__body card-body pt-3 pb-2 px-3">
            <slot name="flash" />
            <div class="position-relative table-card__container">
                <slot />
                <div
                    v-if="props.loading"
                    class="table-card__overlay d-flex align-items-center justify-content-center"
                    role="status"
                    aria-live="polite"
                    aria-label="Cargando datos"
                >
                    <div class="spinner-border text-primary" role="status" aria-hidden="true" />
                    <span class="visually-hidden">Cargando datos de la tabla...</span>
                </div>
            </div>
        </div>

        <div class="table-card__footer card-footer py-2 px-3 small">
            <div class="table-card__footer-content d-flex flex-wrap justify-content-between w-100">
                <div class="table-card__footer-left">
                    <slot name="footer-left" />
                </div>
                <div class="table-card__footer-right ms-auto">
                    <slot name="footer-right" />
                </div>
            </div>
        </div>
    </div>
</template>


