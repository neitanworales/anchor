import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { Expense } from '../models/expense.model';
import { PaginatedResponse } from '../models/paginated-response.model';

export interface CreateExpensePayload {
    category_id: number;
    cost_center_id?: number | null;
    expense_date: string;
    vendor?: string;
    currency?: string;
    amount: number;
    tax_iva?: number | null;
    payment_method?: string;
    receipt_type: 'TICKET' | 'CFDI';
}

@Injectable({
    providedIn: 'root'
})
export class ExpenseService {
    private apiUrl = environment.apiUrl;

    constructor(private http: HttpClient) { }

    getExpenses(status?: string, page = 1): Observable<PaginatedResponse<Expense>> {
        let params = new HttpParams().set('page', page);

        if (status) {
            params = params.set('status', status);
        }

        return this.http.get<PaginatedResponse<Expense>>(`${this.apiUrl}/expenses`, { params });
    }

    createExpense(payload: CreateExpensePayload): Observable<Expense> {
        return this.http.post<Expense>(`${this.apiUrl}/expenses`, payload);
    }

    uploadExpenseFile(expenseId: number, type: 'IMG' | 'PDF' | 'XML', file: File): Observable<any> {
        const formData = new FormData();
        formData.append('type', type);
        formData.append('file', file);

        return this.http.post(`${this.apiUrl}/expenses/${expenseId}/files`, formData);
    }

    parseCfdi(expenseId: number): Observable<any> {
        return this.http.post(`${this.apiUrl}/expenses/${expenseId}/parse-cfdi`, {});
    }
}