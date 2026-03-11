import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Report } from '../../../../core/models/report.model';
import { ReportService } from '../../../../core/services/report.service';
import { ReportForm } from '../report-form/report-form';
import { ReportExpensePicker } from '../report-expense-picker/report-expense-picker';

@Component({
  selector: 'app-report-list',
  standalone: true,
  imports: [CommonModule, FormsModule, ReportForm, ReportExpensePicker],
  templateUrl: './report-list.html'
})
export class ReportList implements OnInit {
  reports: Report[] = [];
  loading = false;
  error = '';

  currentPage = 1;
  lastPage = 1;
  total = 0;
  selectedStatus = '';

  pickerVisible = false;
  selectedReportId: number | null = null;
  submittingReportId: number | null = null;

  constructor(private reportService: ReportService) {}

  ngOnInit(): void {
    this.loadReports();
  }

  loadReports(page = 1): void {
    this.loading = true;
    this.error = '';

    this.reportService.getReports(this.selectedStatus || undefined, page).subscribe({
      next: (response) => {
        this.reports = response.data;
        this.currentPage = response.current_page;
        this.lastPage = response.last_page;
        this.total = response.total;
        this.loading = false;
      },
      error: (err) => {
        this.error = err?.error?.message || 'No se pudieron cargar los reportes';
        this.loading = false;
      }
    });
  }

  onReportCreated(): void {
    this.loadReports(1);
  }

  onStatusChange(): void {
    this.loadReports(1);
  }

  openPicker(reportId: number): void {
    this.selectedReportId = reportId;
    this.pickerVisible = true;
  }

  closePicker(): void {
    this.pickerVisible = false;
    this.selectedReportId = null;
  }

  onExpenseAdded(): void {
    this.loadReports(this.currentPage);
  }

  submitReport(reportId: number): void {
    this.submittingReportId = reportId;

    this.reportService.submitReport(reportId).subscribe({
      next: () => {
        this.submittingReportId = null;
        this.loadReports(this.currentPage);
      },
      error: (err) => {
        this.error = err?.error?.message || 'No se pudo enviar el reporte';
        this.submittingReportId = null;
      }
    });
  }

  nextPage(): void {
    if (this.currentPage < this.lastPage) {
      this.loadReports(this.currentPage + 1);
    }
  }

  prevPage(): void {
    if (this.currentPage > 1) {
      this.loadReports(this.currentPage - 1);
    }
  }
}