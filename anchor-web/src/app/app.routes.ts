import { Routes } from '@angular/router';
import { Login } from './features/auth/pages/login/login';

export const routes: Routes = [
    { path: 'login', component: Login },
    {
        path: 'companies/select',
        loadComponent: () => import('./features/companies/pages/company-selector/company-selector')
            .then(m => m.CompanySelector)
    },
    {
        path: 'dashboard',
        loadComponent: () => import('./features/dashboard/pages/dashboard/dashboard')
            .then(m => m.Dashboard)
    },
    { path: '', redirectTo: 'login', pathMatch: 'full' },
    { path: '**', redirectTo: 'login' }
];