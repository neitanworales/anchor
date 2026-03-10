import { Injectable } from '@angular/core';
import { Observable, of } from 'rxjs';
import { Company } from '../models/company.model';

@Injectable({
    providedIn: 'root'
})
export class CompanyService {

    // MVP temporal:
    // después esto lo cambias por GET /me/companies desde Laravel
    getMyCompanies(): Observable<Company[]> {
        return of([
            { id: 1, name: 'Empresa Demo A', slug: 'empresa-demo-a', role: 'EMPLOYEE' },
            { id: 2, name: 'Empresa Demo B', slug: 'empresa-demo-b', role: 'ADMIN' }
        ]);
    }
}