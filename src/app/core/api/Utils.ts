import { HttpHeaders } from "@angular/common/http";
import { Injectable } from "@angular/core";
import { Router } from "@angular/router";
import { environment } from "../../../environments/environment";

@Injectable()
export class Utils {

    constructor(
        private router: Router
    ) { }

    public getHeaders(withToken: boolean): HttpHeaders {
        const headersConfig: any = {
            'Content-Type': 'application/json'
        };
        if (withToken) {
            const user = JSON.parse(localStorage.getItem('currentUser')!);
            headersConfig['Authorization'] = `Bearer ${user.token}`;
        }
        return new HttpHeaders(headersConfig);
    }

    /*public getSessionFromStorage(): Session | undefined {
        console.log(localStorage.getItem('session'));
        if (localStorage.getItem('session') == null) {
            console.log("redireccionará");
            this.router.navigate(["/login"]);
            return undefined;
        } else {
            return JSON.parse(localStorage.getItem('session')!);
        }
    }*/

    public v1(path: string): string {
        const base = (environment.apiUrl || '').replace(/\/+$/, '');
        return `${base}${path}`;
    }
}