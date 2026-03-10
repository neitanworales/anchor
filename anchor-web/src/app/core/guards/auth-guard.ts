import { CanActivateFn, Router } from '@angular/router';
import { inject } from '@angular/core';
import { AuthService } from '../services/auth.service';
import { StorageService } from '../services/storage.service';

export const authGuard: CanActivateFn = () => {
  const authService = inject(AuthService);
  const storage = inject(StorageService);
  const router = inject(Router);

  const isLoggedIn = authService.isAuthenticated();
  const companyId = storage.getCompanyId();

  if (!isLoggedIn) {
    return router.createUrlTree(['/login']);
  }

  // Permite entrar aunque no tenga empresa elegida;
  // algunas rutas decidirán si necesitan companyId o no
  return true;
};