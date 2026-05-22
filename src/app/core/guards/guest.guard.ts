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

export const guestGuard: CanActivateFn = () => {
  const router = inject(Router);
  if (hasValidToken()) {
    return router.parseUrl('/dashboard');
  }
  return true;
};
