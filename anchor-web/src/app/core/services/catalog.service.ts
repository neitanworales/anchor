import { Injectable } from '@angular/core';
import { Observable, of } from 'rxjs';

export interface CategoryOption {
    id: number;
    name: string;
    requires_cfdi?: boolean;
}

@Injectable({
    providedIn: 'root'
})
export class CatalogService {
    getCategories(): Observable<CategoryOption[]> {
        return of([
            { id: 1, name: 'Comidas', requires_cfdi: false },
            { id: 2, name: 'Gasolina', requires_cfdi: false },
            { id: 3, name: 'Hotel', requires_cfdi: true },
            { id: 4, name: 'Vuelo', requires_cfdi: true }
        ]);
    }

    getPaymentMethods(): Observable<string[]> {
        return of(['CASH', 'CARD', 'COMPANY_CARD']);
    }
}