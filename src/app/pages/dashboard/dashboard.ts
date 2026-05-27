import { ChangeDetectorRef, Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { FacturaDao } from '../../core/api/dao/FacturaDao';
import { Utils } from '../../core/api/Utils';
import { HttpErrorResponse } from '@angular/common/http';
import { finalize } from 'rxjs';

@Component({
  selector: 'app-dashboard',
  imports: [CommonModule, FormsModule, RouterLink],
  templateUrl: './dashboard.html',
  styleUrl: './dashboard.css',
  providers: [FacturaDao, Utils]
})
export class Dashboard implements OnInit {
  isLoading = false;
  errorMessage = '';
  daysFilter = 30;
  statusFilter = 'all';
  tipoComprobanteFilter = 'all';
  activityLimit = 5;
  summary = {
    capturadasUltimos30: 0,
    enRevision: 0,
    aprobadas: 0,
  };
  activity: Array<any> = [];

  constructor(
    private facturaDao: FacturaDao,
    private cdr: ChangeDetectorRef
  ) {}

  ngOnInit(): void {
    this.loadDashboard();
  }

  applyFilters(): void {
    this.loadDashboard();
  }

  loadDashboard(): void {
    if (this.isLoading) {
      return;
    }

    this.isLoading = true;
    this.errorMessage = '';

    if (!this.hasValidToken()) {
      this.isLoading = false;
      this.errorMessage = 'Inicia sesion para ver el dashboard.';
      return;
    }

    this.facturaDao
      .getDashboardSummary(
        this.daysFilter,
        this.activityLimit,
        this.statusFilter,
        this.tipoComprobanteFilter
      )
      .pipe(finalize(() => {
        this.isLoading = false;
      }))
      .subscribe({
      next: (response) => {
        if (!response || response.success === false) {
          this.errorMessage = response?.message || 'No se pudo cargar el dashboard.';
          return;
        }

        this.summary = response.data?.summary || this.summary;
        this.activity = response.data?.activity || [];
        this.cdr.detectChanges();
      },
      error: (err: HttpErrorResponse) => {
        this.errorMessage = err.error?.message || 'No se pudo cargar el dashboard.';
      }
    });
  }

  private hasValidToken(): boolean {
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

  getActivityStatus(item: any): string {
    if (Number(item?.aprobado) === 1) {
      return 'Aprobado';
    }

    const estatus = (item?.estatusSat || '').toUpperCase();
    if (estatus && estatus !== 'NO_CONSULTADO') {
      return 'En validacion SAT';
    }

    return 'En revision';
  }

  getActivityTitle(item: any): string {
    const tipo = item?.tipoComprobante ? `CFDI ${item.tipoComprobante}` : 'CFDI';
    const emisor = item?.nombreEmisor || 'Emisor sin nombre';
    return `${tipo} - ${emisor}`;
  }
}
