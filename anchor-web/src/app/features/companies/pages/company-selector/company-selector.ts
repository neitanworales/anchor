import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { CompanyService } from '../../../../core/services/company.service';
import { StorageService } from '../../../../core/services/storage.service';
import { Company } from '../../../../core/models/company.model';

@Component({
  selector: 'app-company-selector',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './company-selector.html'
})
export class CompanySelector implements OnInit {
  companies: Company[] = [];
  loading = true;
  error = '';

  constructor(
    private companyService: CompanyService,
    private storage: StorageService,
    private router: Router
  ) { }

  ngOnInit(): void {
    this.companyService.getMyCompanies().subscribe({
      next: (companies) => {
        this.companies = companies;
        this.loading = false;
      },
      error: () => {
        this.error = 'No se pudieron cargar las empresas';
        this.loading = false;
      }
    });
  }

  selectCompany(company: Company): void {
    this.storage.setCompanyId(company.id);
    this.router.navigate(['/dashboard']);
  }
}
