import { Component, EventEmitter, OnInit, Output } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { ExpenseService, CreateExpensePayload } from '../../../../core/services/expense.service';
import { CatalogService, CategoryOption } from '../../../../core/services/catalog.service';
import { CfdiParserService, ParsedCfdiFrontend } from '../../../../core/services/cfdi-parser.service';

@Component({
  selector: 'app-expense-form',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './expense-form.html'
})
export class ExpenseForm implements OnInit {
  @Output() expenseCreated = new EventEmitter<void>();

  categories: CategoryOption[] = [];
  paymentMethods: string[] = [];

  loading = false;
  success = '';
  error = '';

  selectedFile: File | null = null;
  selectedFileType: 'IMG' | 'PDF' | 'XML' | '' = '';

  parsedCfdi: ParsedCfdiFrontend | null = null;

  form: FormGroup;

  constructor(
    private fb: FormBuilder,
    private expenseService: ExpenseService,
    private catalogService: CatalogService,
    private cfdiParser: CfdiParserService
  ) {
    this.form = this.fb.group({
      category_id: [null as number | null, Validators.required],
      expense_date: ['', Validators.required],
      vendor: [''],
      currency: ['MXN', [Validators.required, Validators.minLength(3), Validators.maxLength(3)]],
      amount: [null as number | null, [Validators.required, Validators.min(0.01)]],
      tax_iva: [null as number | null],
      payment_method: ['CASH'],
      receipt_type: ['TICKET' as 'TICKET' | 'CFDI', Validators.required]
    });
  }

  ngOnInit(): void {
    this.catalogService.getCategories().subscribe(categories => {
      this.categories = categories;
    });

    this.catalogService.getPaymentMethods().subscribe(methods => {
      this.paymentMethods = methods;
    });

    this.form.get('category_id')?.valueChanges.subscribe(categoryId => {
      const category = this.categories.find(c => c.id === Number(categoryId));
      if (category?.requires_cfdi) {
        this.form.patchValue({ receipt_type: 'CFDI' }, { emitEvent: false });
      }
    });
  }

  onFileSelected(event: Event): void {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;

    this.selectedFile = file;
    this.parsedCfdi = null;
    this.error = '';
    this.success = '';

    if (!file) {
      this.selectedFileType = '';
      return;
    }

    const fileName = file.name.toLowerCase();

    if (file.type.startsWith('image/')) {
      this.selectedFileType = 'IMG';
    } else if (file.type === 'application/pdf' || fileName.endsWith('.pdf')) {
      this.selectedFileType = 'PDF';
    } else if (
      file.type === 'text/xml' ||
      file.type === 'application/xml' ||
      fileName.endsWith('.xml')
    ) {
      this.selectedFileType = 'XML';
      this.readXmlAndPatchForm(file);
    } else {
      this.selectedFileType = '';
      this.error = 'Tipo de archivo no soportado';
    }
  }

  private readXmlAndPatchForm(file: File): void {
    const reader = new FileReader();

    reader.onload = () => {
      try {
        const xmlText = reader.result as string;
        const parsed = this.cfdiParser.parseXmlText(xmlText);

        this.parsedCfdi = parsed;

        this.form.patchValue({
          vendor: parsed.emisorNombre || '',
          amount: parsed.total ?? null,
          currency: parsed.moneda || 'MXN',
          expense_date: parsed.fecha ? parsed.fecha.substring(0, 10) : '',
          receipt_type: 'CFDI'
        });

        if (parsed.warnings.length > 0) {
          this.error = parsed.warnings.join(' ');
        }
      } catch (e: any) {
        this.error = e?.message || 'No se pudo leer el XML';
      }
    };

    reader.onerror = () => {
      this.error = 'No se pudo leer el archivo XML';
    };

    reader.readAsText(file);
  }

  submit(): void {
    this.success = '';
    this.error = '';

    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    const raw = this.form.getRawValue();

    const payload: CreateExpensePayload = {
      category_id: Number(raw.category_id),
      expense_date: raw.expense_date!,
      vendor: raw.vendor || undefined,
      currency: raw.currency || 'MXN',
      amount: Number(raw.amount),
      tax_iva: raw.tax_iva != null ? Number(raw.tax_iva) : null,
      payment_method: raw.payment_method || undefined,
      receipt_type: raw.receipt_type!
    };

    this.loading = true;

    this.expenseService.createExpense(payload).subscribe({
      next: (expense) => {
        if (this.selectedFile && this.selectedFileType) {
          this.uploadSelectedFile(expense.id);
          return;
        }

        this.success = 'Gasto creado correctamente';
        this.loading = false;
        this.resetForm();
        this.expenseCreated.emit();
      },
      error: (err) => {
        this.error = err?.error?.message || 'No se pudo crear el gasto';
        this.loading = false;
      }
    });
  }

  private uploadSelectedFile(expenseId: number): void {
    if (!this.selectedFile || !this.selectedFileType) {
      this.loading = false;
      return;
    }

    this.expenseService.uploadExpenseFile(expenseId, this.selectedFileType, this.selectedFile).subscribe({
      next: () => {
        if (this.selectedFileType === 'XML') {
          this.parseXmlAfterUpload(expenseId);
          return;
        }

        this.success = 'Gasto creado y archivo cargado correctamente';
        this.loading = false;
        this.resetForm();
        this.expenseCreated.emit();
      },
      error: (err) => {
        this.error = err?.error?.message || 'El gasto se creó, pero no se pudo subir el archivo';
        this.loading = false;
      }
    });
  }

  private parseXmlAfterUpload(expenseId: number): void {
    this.expenseService.parseCfdi(expenseId).subscribe({
      next: (response) => {
        const warnings = response?.warnings || [];

        this.success = warnings.length > 0
          ? `Gasto creado y XML procesado. Advertencias: ${warnings.join(' ')}`
          : 'Gasto creado, XML cargado y CFDI procesado correctamente';

        this.loading = false;
        this.resetForm();
        this.expenseCreated.emit();
      },
      error: (err) => {
        this.error = err?.error?.message || 'El XML se subió, pero no se pudo procesar';
        this.loading = false;
      }
    });
  }

  private resetForm(): void {
    this.form.reset({
      category_id: null,
      expense_date: '',
      vendor: '',
      currency: 'MXN',
      amount: null,
      tax_iva: null,
      payment_method: 'CASH',
      receipt_type: 'TICKET'
    });

    this.selectedFile = null;
    this.selectedFileType = '';
    this.parsedCfdi = null;
  }
}