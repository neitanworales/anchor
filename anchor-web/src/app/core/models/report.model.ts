import { Expense } from './expense.model';

export interface Report {
    id: number;
    company_id: number;
    title: string;
    period_start?: string;
    period_end?: string;
    status: 'DRAFT' | 'SUBMITTED' | 'APPROVED' | 'REJECTED' | 'PAID';
    total: number;
    expenses?: Expense[];
}