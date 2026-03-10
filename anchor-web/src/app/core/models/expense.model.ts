export interface Expense {
    id: number;
    company_id: number;
    category_id: number;
    expense_date: string;
    vendor?: string;
    currency: string;
    amount: number;
    tax_iva?: number;
    payment_method?: string;
    receipt_type: 'TICKET' | 'CFDI';
    cfdi_uuid?: string;
    cfdi_emitter_rfc?: string;
    cfdi_emitter_name?: string;
    cfdi_issue_datetime?: string;
    status: 'DRAFT' | 'IN_REPORT' | 'LOCKED';
}