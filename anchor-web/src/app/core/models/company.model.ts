export interface Company {
    id: number;
    name: string;
    slug: string;
    role?: 'EMPLOYEE' | 'APPROVER' | 'ADMIN';
}