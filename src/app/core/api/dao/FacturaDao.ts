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

    public getDashboardSummary(days = 30, limit = 5): Observable<any> {
        const params = new URLSearchParams({
            days: String(days),
            limit: String(limit)
        }).toString();
        return this.http.get<any>(`${this.utils.v1('/dashboard')}?${params}`, { headers: this.utils.getHeaders(true) });
    }
}
