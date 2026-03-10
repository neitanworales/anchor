import { CanActivateFn, Router } from '@angular/router';
import { inject } from '@angular/core';
import { StorageService } from '../services/storage.service';

export const companyGuard: CanActivateFn = () => {
    const storage = inject(StorageService);
    const router = inject(Router);

    const companyId = storage.getCompanyId();

    if (!companyId || companyId === '0') {
        return router.createUrlTree(['/companies/select']);
    }

    return true;
};