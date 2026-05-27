import { Injectable } from "@angular/core";
import { HttpClient } from "@angular/common/http";
import { Observable } from "rxjs";
import { Utils } from "../Utils";

@Injectable()
export class FacturaDao {
    constructor(
        private http: HttpClient,
        private utils: Utils
    ) {}

    public createFactura(payload: any): Observable<any> {
        return this.http.post<any>(this.utils.v1('/facturas'), payload, { headers: this.utils.getHeaders(true) });
    }

    public getDashboardSummary(days = 30, limit = 5, status = 'all', tipoComprobante = 'all'): Observable<any> {
        const params: Record<string, string> = {
            days: String(days),
            limit: String(limit)
        };

        if (status === 'approved') {
            params['aprobado'] = '1';
        } else if (status === 'pending') {
            params['aprobado'] = '0';
        }

        if (tipoComprobante !== 'all') {
            params['tipoComprobante'] = tipoComprobante;
        }

        const query = new URLSearchParams(params).toString();
        return this.http.get<any>(`${this.utils.v1('/dashboard')}?${query}`, { headers: this.utils.getHeaders(true) });
    }
}
