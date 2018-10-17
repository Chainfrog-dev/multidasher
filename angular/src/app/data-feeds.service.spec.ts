import { TestBed } from '@angular/core/testing';

import { DataFeedsService } from './data-feeds.service';

describe('DataFeedsService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: DataFeedsService = TestBed.get(DataFeedsService);
    expect(service).toBeTruthy();
  });
});
