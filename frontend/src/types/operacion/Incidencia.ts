export interface Root {
    message: string;
    code: number;
    data: Data;
}

export interface Data {
    secuencia: string;
    tipoIncidencias: TipoIncidencia[];
    lugares: Lugar[];
    areas: Area[];
}

export interface TipoIncidencia {
    id: number;
    text: string;
}

export interface Lugar {
    id: number;
    text: string;
}

export interface Area {
    id: number;
    text: string;
}
