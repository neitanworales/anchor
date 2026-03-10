import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ApprovalList } from './approval-list';

describe('ApprovalList', () => {
  let component: ApprovalList;
  let fixture: ComponentFixture<ApprovalList>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ApprovalList]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ApprovalList);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
