import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { Report } from '../models/report.model';
import { PaginatedResponse } from '../models/paginated-response.model';

export interface CreateReportPayload {
    title: string;
    cost_center_id?: number | null;
    period_start?: string | null;
    period_end?: string | null;
}

@Injectable({
    providedIn: 'root'
})
export class ReportService {
    private apiUrl = environment.apiUrl;

    constructor(private http: HttpClient) { }

    getReports(status?: string, page = 1): Observable<PaginatedResponse<Report>> {
        let params = new HttpParams().set('page', page);

        if (status) {
            params = params.set('status', status);
        }

        return this.http.get<PaginatedResponse<Report>>(`${this.apiUrl}/reports`, { params });
    }

    createReport(payload: CreateReportPayload): Observable<Report> {
        return this.http.post<Report>(`${this.apiUrl}/reports`, payload);
    }

    addExpense(reportId: number, expenseId: number): Observable<Report> {
        return this.http.post<Report>(`${this.apiUrl}/reports/${reportId}/add-expense`, {
            expense_id: expenseId
        });
    }

    submitReport(reportId: number): Observable<Report> {
        return this.http.post<Report>(`${this.apiUrl}/reports/${reportId}/submit`, {});
    }
}