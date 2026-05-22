import { Injectable } from "@angular/core";
import { Observable, map } from 'rxjs';
import { Utils } from "../Utils";
import { LoginResponse } from "../../models/login/LoginResponse";
import { HttpClient } from "@angular/common/http";

@Injectable()
export class LoginDao {

    constructor(
        private http: HttpClient,
        private utils: Utils
    ) { }

    public login(username: String, password: String): Observable<LoginResponse> {
        const body = {
            email: username.toString(),
            password: password.toString()
        };

        return this.http.post<LoginResponse>(this.utils.v1('/auth/login'), body, { headers: this.utils.getHeaders(false) }).pipe();
    }

    public logout(token: string): Observable<LoginResponse> {
        const body = { token };
        return this.http.post<LoginResponse>(this.utils.v1('/auth/logout'), body, { headers: this.utils.getHeaders(false) }).pipe();
    }

}