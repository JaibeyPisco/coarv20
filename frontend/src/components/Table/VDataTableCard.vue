<script setup lang="ts">
const props = defineProps<{
    loading?: boolean;
    columnMenu?: Array<{ title: string; key: string; visible: boolean }>;
    searchValue?: string;
    searchPlaceholder?: string;
}>();

const emit = defineEmits<{
    (e: 'print'): void;
    (e: 'export'): void;
    (e: 'toggle-column', key: string): void;
    (e: 'update:search', value: string): void;
}>();

function handleSearchInput(value: string) {
    emit('update:search', value);
}

function handleToggleColumn(key: string) {
    emit('toggle-column', key);
}
</script>

<template>
    <v-card class="table-card" elevation="1">
        <v-card-title class="py-2 px-3">
            <div class="d-flex flex-wrap align-center justify-space-between w-100 ga-2">
                <div class="d-flex flex-wrap align-center ga-2">
                    <slot name="actions">
                        <v-btn
                            variant="outlined"
                            size="small"
                            prepend-icon="mdi-printer"
                            @click="emit('print')"
                        >
                            Imprimir
                        </v-btn>
                        <v-btn
                            variant="outlined"
                            size="small"
                            prepend-icon="mdi-file-excel"
                            @click="emit('export')"
                        >
                            Excel
                        </v-btn>
                        <v-menu v-if="props.columnMenu && props.columnMenu.length > 0">
                            <template #activator="{ props: menuProps }">
                                <v-btn
                                    v-bind="menuProps"
                                    variant="outlined"
                                    size="small"
                                    prepend-icon="mdi-eye"
                                >
                                    Mostrar columnas
                                </v-btn>
                            </template>
                            <v-list>
                                <v-list-item
                                    v-for="column in props.columnMenu"
                                    :key="column.key"
                                    :prepend-icon="column.visible ? 'mdi-eye' : 'mdi-eye-off'"
                                    :title="column.title"
                                    @click="handleToggleColumn(column.key)"
                                />
                            </v-list>
                        </v-menu>
                    </slot>
                </div>
                <div class="d-flex align-center ms-lg-auto">
                    <slot name="search">
                        <v-text-field
                            :model-value="props.searchValue ?? ''"
                            :placeholder="props.searchPlaceholder ?? 'Buscar...'"
                            prepend-inner-icon="mdi-magnify"
                            variant="outlined"
                            density="compact"
                            hide-details
                            single-line
                            @update:model-value="handleSearchInput"
                            style="width: 350px; max-width: 350px"  
                        />
                    </slot>
                </div>
            </div>
        </v-card-title>

        <v-divider />

        <v-card-text class="pt-3 pb-2 px-3">
            <slot name="flash" />
            <div class="position-relative table-card__container">
                <slot />
                <v-overlay
                    v-if="props.loading"
                    :model-value="props.loading"
                    contained
                    class="align-center justify-center"
                    scrim="rgba(255, 255, 255, 0.7)"
                >
                    <v-progress-circular
                        indeterminate
                        color="primary"
                        size="64"
                    />
                </v-overlay>
            </div>
        </v-card-text>

        <v-divider />

        <v-card-actions class="py-2 px-3">
            <div class="d-flex flex-wrap justify-space-between w-100">
                <div class="table-card__footer-left">
                    <slot name="footer-left" />
                </div>
                <div class="table-card__footer-right ms-auto">
                    <slot name="footer-right" />
                </div>
            </div>
        </v-card-actions>
    </v-card>
</template>

<style scoped>
.table-card__container {
    min-height: 240px;
}
</style>
