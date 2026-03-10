import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable, tap } from 'rxjs';
import { environment } from '../../../environments/environment.development';
import { StorageService } from './storage.service';

@Injectable({
    providedIn: 'root'
})
export class AuthService {
    private apiUrl = environment.apiUrl;

    constructor(
        private http: HttpClient,
        private storage: StorageService
    ) { }

    login(email: string, password: string, device_name = 'angular-web'): Observable<any> {
        return this.http.post<any>(`${this.apiUrl}/login`, {
            email,
            password,
            device_name
        }).pipe(
            tap(response => {
                this.storage.setToken(response.token);
            })
        );
    }

    logout(): Observable<any> {
        return this.http.post(`${this.apiUrl}/logout`, {}).pipe(
            tap(() => {
                this.storage.clear();
            })
        );
    }

    isAuthenticated(): boolean {
        return !!this.storage.getToken();
    }
}