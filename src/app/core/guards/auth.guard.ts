import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';

function hasValidToken(): boolean {
  try {
    const raw = localStorage.getItem('currentUser');
    if (!raw) {
      return false;
    }
    const parsed = JSON.parse(raw);
    return !!parsed?.token;
  } catch {
    return false;
  }
}

export const authGuard: CanActivateFn = () => {
  const router = inject(Router);
  if (hasValidToken()) {
    return true;
  }
  return router.parseUrl('/login');
};
