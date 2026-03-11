import { Injectable } from '@angular/core';

export interface ParsedCfdiFrontend {
    tipoDeComprobante: string;
    fecha: string;
    total: number | null;
    subTotal: number | null;
    moneda: string;
    emisorRfc: string;
    emisorNombre: string;
    receptorRfc: string;
    uuid: string;
    warnings: string[];
}

@Injectable({
    providedIn: 'root'
})
export class CfdiParserService {

    parseXmlText(xmlText: string): ParsedCfdiFrontend {
        const parser = new DOMParser();
        const xml = parser.parseFromString(xmlText, 'application/xml');

        const parserError = xml.getElementsByTagName('parsererror');
        if (parserError.length > 0) {
            throw new Error('XML inválido');
        }

        const comprobante =
            xml.getElementsByTagName('cfdi:Comprobante')[0] ||
            xml.getElementsByTagName('Comprobante')[0];

        if (!comprobante) {
            throw new Error('No se encontró el nodo Comprobante');
        }

        const emisor =
            xml.getElementsByTagName('cfdi:Emisor')[0] ||
            xml.getElementsByTagName('Emisor')[0];

        const receptor =
            xml.getElementsByTagName('cfdi:Receptor')[0] ||
            xml.getElementsByTagName('Receptor')[0];

        const timbre =
            xml.getElementsByTagName('tfd:TimbreFiscalDigital')[0] ||
            xml.getElementsByTagName('TimbreFiscalDigital')[0];

        const tipoDeComprobante = comprobante.getAttribute('TipoDeComprobante') || '';
        const fecha = comprobante.getAttribute('Fecha') || '';
        const total = comprobante.getAttribute('Total');
        const subTotal = comprobante.getAttribute('SubTotal');
        const moneda = comprobante.getAttribute('Moneda') || 'MXN';

        const emisorRfc = emisor?.getAttribute('Rfc') || '';
        const emisorNombre = emisor?.getAttribute('Nombre') || '';
        const receptorRfc = receptor?.getAttribute('Rfc') || '';
        const uuid = timbre?.getAttribute('UUID') || '';

        const warnings: string[] = [];

        if (tipoDeComprobante === 'P') {
            warnings.push('Se detectó CFDI tipo P (Pago).');
        }

        if (['E', 'T', 'N'].includes(tipoDeComprobante)) {
            warnings.push(`Se detectó CFDI tipo ${tipoDeComprobante}, no recomendado para flujo de gasto estándar.`);
        }

        return {
            tipoDeComprobante,
            fecha,
            total: total ? Number(total) : null,
            subTotal: subTotal ? Number(subTotal) : null,
            moneda,
            emisorRfc,
            emisorNombre,
            receptorRfc,
            uuid,
            warnings
        };
    }
}