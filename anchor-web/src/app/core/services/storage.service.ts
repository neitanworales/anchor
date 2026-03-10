import { Injectable } from '@angular/core';

@Injectable({
    providedIn: 'root'
})
export class StorageService {
    setToken(token: string): void {
        localStorage.setItem('token', token);
    }

    getToken(): string | null {
        return localStorage.getItem('token');
    }

    setCompanyId(companyId: number): void {
        localStorage.setItem('companyId', String(companyId));
    }

    getCompanyId(): string | null {
        return localStorage.getItem('companyId');
    }

    clear(): void {
        localStorage.removeItem('token');
        localStorage.removeItem('companyId');
    }
}