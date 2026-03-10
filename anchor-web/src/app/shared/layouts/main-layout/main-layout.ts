import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router, RouterLink, RouterOutlet } from '@angular/router';
import { AuthService } from '../../../core/services/auth.service';
import { StorageService } from '../../../core/services/storage.service';

@Component({
  selector: 'app-main-layout',
  standalone: true,
  imports: [CommonModule, RouterOutlet, RouterLink],
  templateUrl: './main-layout.html'
})
export class MainLayout {
  constructor(
    private authService: AuthService,
    private storage: StorageService,
    private router: Router
  ) { }

  logout(): void {
    this.authService.logout().subscribe({
      next: () => {
        this.storage.clear();
        this.router.navigate(['/login']);
      },
      error: () => {
        this.storage.clear();
        this.router.navigate(['/login']);
      }
    });
  }

  changeCompany(): void {
    this.storage.setCompanyId(0);
    localStorage.removeItem('companyId');
    this.router.navigate(['/companies/select']);
  }
}