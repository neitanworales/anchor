import { Routes } from '@angular/router';
import { Login } from './features/auth/pages/login/login';
import { authGuard } from './core/guards/auth-guard';
import { companyGuard } from './core/guards/company.guard';
import { MainLayout } from './shared/layouts/main-layout/main-layout';

export const routes: Routes = [
  { path: 'login', component: Login },

  {
    path: 'companies/select',
    canActivate: [authGuard],
    loadComponent: () =>
      import('./features/companies/pages/company-selector/company-selector')
        .then(m => m.CompanySelector)
  },

  {
    path: '',
    component: MainLayout,
    canActivate: [authGuard],
    children: [
      {
        path: 'dashboard',
        canActivate: [companyGuard],
        loadComponent: () =>
          import('./features/dashboard/pages/dashboard/dashboard')
            .then(m => m.Dashboard)
      },
      {
        path: 'expenses',
        canActivate: [companyGuard],
        loadComponent: () =>
          import('./features/expenses/pages/expense-list/expense-list')
            .then(m => m.ExpenseList)
      },
      {
        path: 'reports',
        canActivate: [companyGuard],
        loadComponent: () =>
          import('./features/reports/pages/report-list/report-list')
            .then(m => m.ReportList)
      },
      {
        path: 'approvals',
        canActivate: [companyGuard],
        loadComponent: () =>
          import('./features/approvals/pages/approval-list/approval-list')
            .then(m => m.ApprovalList)
      }
    ]
  },

  { path: '', redirectTo: 'login', pathMatch: 'full' },
  { path: '**', redirectTo: 'login' }
];