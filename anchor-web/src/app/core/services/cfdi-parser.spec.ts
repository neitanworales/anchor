import { TestBed } from '@angular/core/testing';

import { CfdiParser } from './cfdi-parser';

describe('CfdiParser', () => {
  let service: CfdiParser;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(CfdiParser);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
