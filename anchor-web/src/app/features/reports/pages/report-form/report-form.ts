import { Component, EventEmitter, Output } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { ReportService, CreateReportPayload } from '../../../../core/services/report.service';

@Component({
  selector: 'app-report-form',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './report-form.html'
})
export class ReportForm {
  @Output() reportCreated = new EventEmitter<void>();

  loading = false;
  success = '';
  error = '';

  form: FormGroup;

  constructor(
    private fb: FormBuilder,
    private reportService: ReportService
  ) {
    this.form = this.fb.group({
      title: ['', [Validators.required, Validators.maxLength(255)]],
      period_start: [''],
      period_end: ['']
    });
  }

  submit(): void {
    this.success = '';
    this.error = '';

    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    const raw = this.form.getRawValue();

    const payload: CreateReportPayload = {
      title: raw.title!,
      period_start: raw.period_start || null,
      period_end: raw.period_end || null
    };

    this.loading = true;

    this.reportService.createReport(payload).subscribe({
      next: () => {
        this.success = 'Reporte creado correctamente';
        this.loading = false;
        this.form.reset({
          title: '',
          period_start: '',
          period_end: ''
        });
        this.reportCreated.emit();
      },
      error: (err) => {
        this.error = err?.error?.message || 'No se pudo crear el reporte';
        this.loading = false;
      }
    });
  }
}