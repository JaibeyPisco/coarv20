import * as XLSX from 'xlsx';
import { notificacion } from './notificacion';

export default function<T = any>(file: File, headers: string[],  range: number = 0): Promise<T[]> {
    
    return new Promise((resolve, reject) => {
    
        const reader = new FileReader();

        reader.onload = (e) => {
            try {
                const data = new Uint8Array(e.target?.result as ArrayBuffer);
                const workbook = XLSX.read(data, { type: 'array' });

                const sheetNames = workbook.SheetNames;

                if (sheetNames.length === 0) {
                    notificacion('El archivo Excel no contiene hojas.', {
                        type: 'danger',
                        title: 'Formato incorrecto',
                    });
                    return reject(new Error('El archivo Excel no contiene hojas.'));
                }

                const firstSheetName = sheetNames[0] as string; // ya sabemos que existe
                const worksheet = workbook.Sheets[firstSheetName] as XLSX.WorkSheet;

                if (!worksheet) {
                    notificacion('La hoja inicial del Excel no se pudo leer.', {
                        type: 'danger',
                        title: 'Formato incorrecto',
                    });
                    return reject(new Error('No se encontró la hoja inicial del Excel.'));
                }

                const json = XLSX.utils.sheet_to_json<T>(worksheet, {
                    header: headers,
                    range,
                    defval: '', // valores vacíos → cadena vacía,
                });

                resolve(json);
            } catch (error) {
                reject(error);
            }
        };

        reader.onerror = () => reject(reader.error);
        reader.readAsArrayBuffer(file);
    });
}
