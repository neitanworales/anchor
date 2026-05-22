import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router, RouterLink } from '@angular/router';
import { LoginDao } from '../../core/api/dao/LoginDao';
import { Utils } from '../../core/api/Utils';

@Component({
  selector: 'app-header',
  imports: [CommonModule, RouterLink],
  templateUrl: './header.html',
  styleUrl: './header.css',
  providers: [LoginDao, Utils]
})
export class Header {
  constructor(
    private loginDao: LoginDao,
    private router: Router
  ) {}

  get isLoggedIn(): boolean {
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

  logout(): void {
    const raw = localStorage.getItem('currentUser');
    if (!raw) {
      this.router.navigate(['/login']);
      return;
    }

    let token = '';
    try {
      const parsed = JSON.parse(raw);
      token = parsed?.token || '';
    } catch {
      token = '';
    }

    if (!token) {
      localStorage.removeItem('currentUser');
      this.router.navigate(['/login']);
      return;
    }

    this.loginDao.logout(token).subscribe({
      next: () => {
        localStorage.removeItem('currentUser');
        this.router.navigate(['/login']);
      },
      error: () => {
        localStorage.removeItem('currentUser');
        this.router.navigate(['/login']);
      }
    });
  }

}
