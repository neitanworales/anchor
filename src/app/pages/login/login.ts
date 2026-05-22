import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { LoginDao } from '../../core/api/dao/LoginDao';
import { Utils } from '../../core/api/Utils';
import { LoginResponse } from '../../core/models/login/LoginResponse';
import { HttpErrorResponse } from '@angular/common/http';

@Component({
  selector: 'app-login',
  imports: [CommonModule, FormsModule],
  templateUrl: './login.html',
  styleUrl: './login.css',
  providers: [LoginDao, Utils]
})
export class Login {
  email = '';
  password = '';
  isLoading = false;
  errorMessage = '';

  constructor(
    private loginDao: LoginDao,
    private router: Router
  ) {}

  onSubmit(): void {
    if (this.isLoading) {
      return;
    }

    this.errorMessage = '';
    if (!this.email || !this.password) {
      this.errorMessage = 'Email y password son requeridos.';
      return;
    }

    this.isLoading = true;
    this.loginDao.login(this.email, this.password).subscribe({
      next: (response: LoginResponse) => {
        this.isLoading = false;

        if (!response || response.success === false) {
          this.errorMessage = response?.message || 'Credenciales invalidas.';
          return;
        }

        const token = response.data?.token || response.token;
        if (token) {
          localStorage.setItem('currentUser', JSON.stringify({
            token,
            user: response.data?.user,
            roles: response.data?.roles,
            expires_at: response.data?.expires_at
          }));
        }

        this.router.navigate(['/dashboard']);
      },
      error: (err: HttpErrorResponse) => {
        this.isLoading = false;
        this.errorMessage = err.error?.message || 'No se pudo iniciar sesion.';
      }
    });
  }

}
