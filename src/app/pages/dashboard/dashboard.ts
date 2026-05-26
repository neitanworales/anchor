import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { FacturaDao } from '../../core/api/dao/FacturaDao';
import { Utils } from '../../core/api/Utils';
import { HttpErrorResponse } from '@angular/common/http';

@Component({
  selector: 'app-dashboard',
  imports: [CommonModule, RouterLink],
  templateUrl: './dashboard.html',
  styleUrl: './dashboard.css',
  providers: [FacturaDao, Utils]
})
export class Dashboard implements OnInit {
  isLoading = false;
  errorMessage = '';
  summary = {
    capturadasUltimos30: 0,
    enRevision: 0,
    aprobadas: 0,
  };
  activity: Array<any> = [];

  constructor(private facturaDao: FacturaDao) {}

  ngOnInit(): void {
    this.loadDashboard();
  }

  loadDashboard(): void {
    if (this.isLoading) {
      return;
    }

    this.isLoading = true;
    this.errorMessage = '';

    this.facturaDao.getDashboardSummary().subscribe({
      next: (response) => {
        this.isLoading = false;
        if (!response || response.success === false) {
          this.errorMessage = response?.message || 'No se pudo cargar el dashboard.';
          return;
        }

        this.summary = response.data?.summary || this.summary;
        this.activity = response.data?.activity || [];
      },
      error: (err: HttpErrorResponse) => {
        this.isLoading = false;
        this.errorMessage = err.error?.message || 'No se pudo cargar el dashboard.';
      }
    });
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
