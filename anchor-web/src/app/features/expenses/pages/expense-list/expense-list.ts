import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { ExpenseService } from '../../../../core/services/expense.service';
import { Expense } from '../../../../core/models/expense.model';
import { ExpenseForm } from '../expense-form/expense-form';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-expense-list',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink],
  templateUrl: './expense-list.html'
})
export class ExpenseList implements OnInit {
  expenses: Expense[] = [];
  loading = false;
  error = '';

  currentPage = 1;
  lastPage = 1;
  total = 0;

  selectedStatus = '';

  constructor(private expenseService: ExpenseService) { }

  ngOnInit(): void {
    this.loadExpenses();
  }

  loadExpenses(page = 1): void {
    this.loading = true;
    this.error = '';

    this.expenseService.getExpenses(this.selectedStatus || undefined, page).subscribe({
      next: (response) => {
        this.expenses = response.data;
        this.currentPage = response.current_page;
        this.lastPage = response.last_page;
        this.total = response.total;
        this.loading = false;
      },
      error: (err) => {
        this.error = err?.error?.message || 'No se pudieron cargar los gastos';
        this.loading = false;
      }
    });
  }

  onExpenseCreated(): void {
    this.loadExpenses(1);
  }

  onStatusChange(): void {
    this.loadExpenses(1);
  }

  nextPage(): void {
    if (this.currentPage < this.lastPage) {
      this.loadExpenses(this.currentPage + 1);
    }
  }

  prevPage(): void {
    if (this.currentPage > 1) {
      this.loadExpenses(this.currentPage - 1);
    }
  }
}