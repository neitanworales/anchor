import { Component, EventEmitter, Input, OnChanges, Output, SimpleChanges } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Expense } from '../../../../core/models/expense.model';
import { ExpenseService } from '../../../../core/services/expense.service';
import { ReportService } from '../../../../core/services/report.service';

@Component({
  selector: 'app-report-expense-picker',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './report-expense-picker.html'
})
export class ReportExpensePicker implements OnChanges {
  @Input() reportId!: number;
  @Input() visible = false;
  @Output() closePicker = new EventEmitter<void>();
  @Output() expenseAdded = new EventEmitter<void>();

  expenses: Expense[] = [];
  loading = false;
  error = '';
  addingExpenseId: number | null = null;

  constructor(
    private expenseService: ExpenseService,
    private reportService: ReportService
  ) {}

  ngOnChanges(changes: SimpleChanges): void {
    if (changes['visible']?.currentValue === true && this.reportId) {
      this.loadDraftExpenses();
    }
  }

  loadDraftExpenses(): void {
    this.loading = true;
    this.error = '';

    this.expenseService.getExpenses('DRAFT', 1).subscribe({
      next: (response) => {
        this.expenses = response.data;
        this.loading = false;
      },
      error: (err) => {
        this.error = err?.error?.message || 'No se pudieron cargar los gastos disponibles';
        this.loading = false;
      }
    });
  }

  addExpense(expenseId: number): void {
    this.addingExpenseId = expenseId;

    this.reportService.addExpense(this.reportId, expenseId).subscribe({
      next: () => {
        this.addingExpenseId = null;
        this.expenseAdded.emit();
        this.loadDraftExpenses();
      },
      error: (err) => {
        this.error = err?.error?.message || 'No se pudo agregar el gasto al reporte';
        this.addingExpenseId = null;
      }
    });
  }

  close(): void {
    this.closePicker.emit();
  }
}