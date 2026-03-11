import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ReportExpensePicker } from './report-expense-picker';

describe('ReportExpensePicker', () => {
  let component: ReportExpensePicker;
  let fixture: ComponentFixture<ReportExpensePicker>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ReportExpensePicker]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ReportExpensePicker);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
