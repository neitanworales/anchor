export interface ExpenseFile {
    id: number;
    expense_id: number;
    type: 'IMG' | 'PDF' | 'XML';
    path: string;
    original_name?: string;
}

export interface ExpenseCategory {
    id: number;
    name: string;
    max_per_report?: number | null;
    requires_cfdi?: boolean;
}

export interface Expense {
    id: number;
    company_id: number;
    category_id: number;
    cost_center_id?: number | null;
    expense_date: string;
    vendor?: string;
    currency: string;
    amount: number;
    tax_iva?: number | null;
    payment_method?: string;
    receipt_type: 'TICKET' | 'CFDI';

    cfdi_type?: 'I' | 'E' | 'T' | 'N' | 'P';
    cfdi_uuid?: string;
    cfdi_emitter_rfc?: string;
    cfdi_emitter_name?: string;
    cfdi_receiver_rfc?: string;
    cfdi_currency?: string;
    cfdi_subtotal?: number;
    cfdi_total?: number;
    cfdi_issue_datetime?: string;

    status: 'DRAFT' | 'IN_REPORT' | 'LOCKED';
    category?: ExpenseCategory;
    files?: ExpenseFile[];
}