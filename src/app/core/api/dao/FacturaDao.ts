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
}
